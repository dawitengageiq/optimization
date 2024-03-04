<?php

namespace App\Jobs\Reports;

use App\AffiliateReport;
use App\AffiliateRevenueTracker;
use App\Jobs\Job;
use App\PrepopStatistic;
use App\RevenueTrackerCakeStatistic;
use Curl\Curl;
use DB;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;

class GetUniqueClicksReport extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $dateFromStr;

    protected $dateToStr;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($dateFromStr, $dateToStr)
    {
        $this->dateFromStr = $dateFromStr;
        $this->dateToStr = $dateToStr;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->attempts() > 1) {
            return;
        }

        Log::info('Generating Unique Click Reports...');

        $cakeBaseClicksURL = config('constants.CAKE_CLICKS_REPORTS_API_V12_UNIQUE');
        $requiredParameters = config('constants.PREPOP_PARAMETERS');

        $revenue_trackers = AffiliateRevenueTracker::where('offer_id', '!=', 0)
            ->orWhere(function ($q) {
                $q->where('campaign_id', 1)
                    ->where('offer_id', 1)
                    ->where('revenue_tracker_id', 1);
            })
            ->select('affiliate_id', 'campaign_id', 'revenue_tracker_id', 'offer_id')
            ->groupBy('affiliate_id', 'revenue_tracker_id')->get();

        // $revenue_trackers = AffiliateRevenueTracker::where('revenue_tracker_id',7612)->get();

        foreach ($revenue_trackers as $rt) {
            $rt_data = [];
            $affiliate_id = $rt->affiliate_id;
            $campaign_id = $rt->campaign_id;
            $offer_id = $rt->offer_id;
            $revenue_tracker_id = $rt->revenue_tracker_id;
            $rowStart = 0;
            $rowLimit = 2500;

            while (true) {
                // if($rowStart == $rowLimit) break; //forchecking only

                Log::info("START ROW: $rowStart");
                $cakeurl = $cakeBaseClicksURL;
                $cakeurl = str_replace('offer_id=0', "offer_id=$offer_id", $cakeurl);
                $cakeurl = str_replace('start_at_row=0', 'start_at_row='.$rowStart, $cakeurl);
                $cakeurl = str_replace('row_limit=0', "row_limit=$rowLimit", $cakeurl);

                $response = $this->getCakeXML($cakeurl, $affiliate_id, $campaign_id, $revenue_tracker_id);

                if (! isset($response['clicks']['click'])) {
                    break;
                } else {
                    $clicks = $response['clicks']['click'];
                    foreach ($clicks as $click) {
                        $s1 = ! is_array($click['sub_id_1']) ? $click['sub_id_1'] : '';
                        $s2 = ! is_array($click['sub_id_2']) ? $click['sub_id_2'] : '';
                        $s3 = ! is_array($click['sub_id_3']) ? $click['sub_id_3'] : '';
                        $s4 = ! is_array($click['sub_id_4']) ? $click['sub_id_4'] : '';
                        $s5 = ! is_array($click['sub_id_5']) ? $click['sub_id_5'] : '';

                        if (! isset($rt_data[$s1][$s2][$s3][$s4][$s5])) {
                            $rt_data[$s1][$s2][$s3][$s4][$s5] = [
                                'click' => 0,
                                'payout' => 0,
                                'no_prepop_count' => 0,
                                'prepop_count' => 0,
                                'prepop_werrors_count' => 0,
                            ];
                        }

                        //Count Click, Revenue & Payout
                        $rt_data[$s1][$s2][$s3][$s4][$s5]['click'] += 1;
                        $rt_data[$s1][$s2][$s3][$s4][$s5]['payout'] += isset($click['paid']['amount']) ? $click['paid']['amount'] : 0;

                        $requestURL = $click['request_url'];

                        $parts = parse_url($requestURL, PHP_URL_QUERY);
                        $queryParams = [];
                        parse_str($parts, $queryParams);

                        if (count($queryParams) == 0) {
                            $rt_data[$s1][$s2][$s3][$s4][$s5]['no_prepop_count'] += 1;

                            continue;
                        }

                        $missingParamCount = 0;

                        foreach ($requiredParameters as $param => $value) {
                            if (! isset($queryParams[$param])) {
                                $missingParamCount++;
                            }
                        }

                        if ($missingParamCount == count($requiredParameters)) {
                            //this means all required parameter are not present
                            $rt_data[$s1][$s2][$s3][$s4][$s5]['no_prepop_count'] += 1;

                            continue;
                        }

                        $noMissingParam = true;

                        //determine if all required prepop parameters and present
                        foreach ($requiredParameters as $param => $value) {
                            if (isset($queryParams[$param]) && empty($queryParams[$param])) {
                                $noMissingParam = false;
                                $rt_data[$s1][$s2][$s3][$s4][$s5]['prepop_werrors_count'] += 1;
                                break;
                            }
                        }

                        if ($noMissingParam) {
                            $rt_data[$s1][$s2][$s3][$s4][$s5]['prepop_count'] += 1;
                        }
                    }
                }

                $rowStart += $rowLimit;
            }

            foreach ($rt_data as $s1 => $s1d) {
                foreach ($s1d as $s2 => $s2d) {
                    foreach ($s2d as $s3 => $s3d) {
                        foreach ($s3d as $s4 => $s4d) {
                            foreach ($s4d as $s5 => $data) {
                                //SAVE REV CAKE Statistics
                                $cake_stats = RevenueTrackerCakeStatistic::firstOrNew([
                                    'affiliate_id' => $affiliate_id,
                                    'revenue_tracker_id' => $revenue_tracker_id,
                                    'cake_campaign_id' => $campaign_id,
                                    'type' => 1,
                                    'created_at' => $this->dateFromStr,
                                    's1' => $s1,
                                    's2' => $s2,
                                    's3' => $s3,
                                    's4' => $s4,
                                    's5' => $s5,
                                ]);
                                $cake_stats->clicks = $data['click'];
                                $cake_stats->payout = $data['payout'];
                                $cake_stats->save();

                                // Get Revenue from Affiliate Report
                                $aff_report = AffiliateReport::where('affiliate_id', $affiliate_id)
                                    ->where('revenue_tracker_id', $revenue_tracker_id)
                                    ->where('s1', $s1)
                                    ->where('s2', $s2)
                                    ->where('s3', $s3)
                                    ->where('s4', $s4)
                                    ->where('s5', $s5)
                                    ->where('created_at', $this->dateFromStr)
                                    ->select(DB::RAW('SUM(revenue) as revenue'))
                                    ->first();

                                //PrePop Statistics
                                $prepop = PrepopStatistic::firstOrNew([
                                    'affiliate_id' => $affiliate_id,
                                    'revenue_tracker_id' => $revenue_tracker_id,
                                    'created_at' => $this->dateFromStr,
                                    's1' => $s1,
                                    's2' => $s2,
                                    's3' => $s3,
                                    's4' => $s4,
                                    's5' => $s5,
                                ]);

                                $prepop->total_clicks = $data['click'];
                                $prepop->prepop_count = $data['prepop_count'];
                                $prepop->no_prepop_count = $data['no_prepop_count'];
                                $prepop->prepop_with_errors_count = $data['prepop_werrors_count'];
                                $prepop->no_prepop_percentage = (floatval($data['no_prepop_count']) / floatval($data['click']));
                                $prepop->prepop_with_errors_percentage = (floatval($data['prepop_werrors_count']) / floatval($data['click']));

                                $margin = 0;
                                $revenue = isset($aff_report->revenue) ? $aff_report->revenue : 0;
                                $numerator = floatval($revenue) - floatval($data['payout']);
                                if ($numerator > 0 && $revenue > 0) {
                                    $margin = ($numerator / floatval($revenue));
                                }
                                $prepop->profit_margin = $margin;
                                $prepop->save();
                            }
                        }
                    }
                }
            }
        }
    }

    public function getCakeXML($cakeBaseClicksURL, $affiliateID, $campaignID, $revenueTracker)
    {
        $cakeClickURL = $cakeBaseClicksURL."&affiliate_id=$affiliateID&campaign_id=$campaignID&start_date=$this->dateFromStr&end_date=$this->dateToStr";
        Log::info('Get Unique Clicks Report');
        Log::info("affiliate_id: $affiliateID");
        Log::info("revenue_tracker_id: $revenueTracker");
        Log::info("campaign_id: $campaignID");
        Log::info("cake_api: $cakeClickURL");

        $callCounter = 0;
        $response = null;

        do {
            $curl = new Curl();
            $curl->get($cakeClickURL);

            if ($curl->error) {
                Log::info('getCakeClicks API Error!');
                Log::info($curl->error_message);
                $callCounter++;
            } else {
                try {

                    $xml = simplexml_load_string($curl->response);
                    $json = json_encode($xml);
                    $response = json_decode($json, true);
                } catch (Exception $e) {
                    Log::info($e->getCode());
                    Log::info($e->getMessage());
                    $callCounter++;

                    //stop the process when budget breached
                    if ($callCounter == 10) {
                        Log::info('getCakeClicks API call limit reached!');
                        break;
                    }

                    continue;
                }
            }

            //stop the process when budget breached
            if ($callCounter == 10) {
                Log::info('getCakeClicks API call limit reached!');
                break;
            }

        } while ($curl->error);

        return $response;
    }
}
