<?php

namespace App\Jobs\Reports;

use App\AffiliateReport;
use App\AffiliateRevenueTracker;
use App\Jobs\Job;
use App\PrepopStatistic;
use Carbon\Carbon;
use Curl\Curl;
use DB;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;
use Sabre\Xml\Reader;

class GeneratePrepopStatistics extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $skip;

    protected $take;

    protected $dateFromStr;

    protected $dateToStr;

    /**
     * Create a new job instance.
     */
    public function __construct($skip, $take, $dateFromStr, $dateToStr)
    {
        $this->skip = $skip;
        $this->take = $take;
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

        Log::info('Prepop Group Starting...');

        $cakeBaseClicksURL = config('constants.CAKE_CLICKS_REPORTS_API_V10');
        $prefix = config('constants.CAKE_SABRE_PREFIX_V10');
        $requiredParameters = config('constants.PREPOP_PARAMETERS');

        $revenueTrackers = AffiliateRevenueTracker::skip($this->skip)->take($this->take)->get();
        //$revenueTrackers = AffiliateRevenueTracker::skip(0)->take(50)->get();
        //$startDate = Carbon::now()->subDay()->format('m/d/Y');
        //$endDate = Carbon::now()->format('m/d/Y');

        foreach ($revenueTrackers as $revenueTracker) {
            $affiliateID = $revenueTracker->affiliate_id;
            $campaignID = $revenueTracker->campaign_id;
            //$cakeClickURL = $cakeBaseClicksURL."&affiliate_id=$affiliateID&campaign_id=$campaignID&start_date=$this->dateFromStr&end_date=$this->dateToStr";
            //Log::info("affiliate_id: $affiliateID");
            //Log::info("revenue_tracker_id: $revenueTracker->revenue_tracker_id");

            //$clicksCount = $response['value'][$prefix.'row_count'];
            $response = $this->getCakeClicks($cakeBaseClicksURL, $affiliateID, $campaignID, $revenueTracker, $prefix);
            $clicksCount = $response['value'][$prefix.'row_count'];

            $noPrepopCount = 0;
            $prepopCount = 0;
            $prepopWithErrorsCount = 0;

            Log::info("row_count: $clicksCount");

            //if there are no rows then skip it
            if ($clicksCount <= 0) {
                continue;
            }

            $prepopStatistic = PrepopStatistic::firstOrNew([
                'affiliate_id' => $affiliateID,
                'revenue_tracker_id' => $revenueTracker->revenue_tracker_id,
                'created_at' => Carbon::parse($this->dateFromStr)->toDateString(),
            ]);

            if (! isset($response['value'][$prefix.'clicks'])) {
                //no data then skip
                continue;
            }

            foreach ($response['value'][$prefix.'clicks'] as $click) {
                $requestURL = $click['value'][$prefix.'request_url'];

                $parts = parse_url($requestURL, PHP_URL_QUERY);
                $queryParams = [];
                parse_str($parts, $queryParams);

                if (count($queryParams) == 0) {
                    $noPrepopCount++;

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
                    $noPrepopCount++;

                    continue;
                }

                $noMissingParam = true;

                //determine if all required prepop parameters and present
                foreach ($requiredParameters as $param => $value) {
                    if (isset($queryParams[$param]) && empty($queryParams[$param])) {
                        $noMissingParam = false;
                        $prepopWithErrorsCount++;
                        break;
                    }
                }

                /*
                if($missingParamCount==0)
                {
                    //meaning so far the parameters are there
                    $noMissingParam = true;
                }
                */

                if ($noMissingParam) {
                    $prepopCount++;
                }
            }

            //$noPrepopPercentage = (doubleval($noPrepopCount) / doubleval($clicksCount)) * 100.00;
            //$prepopWithErrorsPercentage = (doubleval($prepopWithErrorsCount) / doubleval($clicksCount)) * 100.00;
            $noPrepopPercentage = (floatval($noPrepopCount) / floatval($clicksCount));
            $prepopWithErrorsPercentage = (floatval($prepopWithErrorsCount) / floatval($clicksCount));

            $dateFrom = $this->dateFromStr;
            //$dateTo = $this->dateToStr;
            $dateTo = $dateFrom;

            //get the profit margin from affiliate reports
            $affiliateReport = AffiliateReport::select(DB::raw('SUM(affiliate_reports.revenue) as revenue'),
                DB::raw("(SELECT SUM(payout) FROM revenue_tracker_cake_statistics WHERE affiliate_id=$affiliateID AND revenue_tracker_id=$revenueTracker->revenue_tracker_id AND created_at >= '$dateFrom' AND created_at <= '$dateTo') as payout"))
                ->where('affiliate_reports.affiliate_id', '=', $affiliateID)
                ->where('affiliate_reports.revenue_tracker_id', '=', $revenueTracker->revenue_tracker_id)
                ->where(function ($query) use ($dateFrom, $dateTo) {
                    $query->where('created_at', '>=', $dateFrom);
                    $query->where('created_at', '<=', $dateTo);
                })->first();

            //Log::info($affiliateReport);
            $profitMargin = 0.0;
            $numerator = floatval($affiliateReport->revenue) - floatval($affiliateReport->payout);

            if ($numerator > 0.0) {
                //$profitMargin = ($numerator / doubleval($affiliateReport->revenue)) * 100.00;
                $profitMargin = ($numerator / floatval($affiliateReport->revenue));
            }

            // sub ids
            //            $s1 = $click['value'][$prefix.'sub_id_1'];
            //            $s2 = $click['value'][$prefix.'sub_id_2'];
            //            $s3 = $click['value'][$prefix.'sub_id_3'];
            //            $s4 = $click['value'][$prefix.'sub_id_4'];
            //            $s5 = $click['value'][$prefix.'sub_id_5'];
            //
            //            $prepopStatistic->s1 = $s1;
            //            $prepopStatistic->s2 = $s2;
            //            $prepopStatistic->s3 = $s3;
            //            $prepopStatistic->s4 = $s4;
            //            $prepopStatistic->s5 = $s5;

            $prepopStatistic->total_clicks = $clicksCount;
            $prepopStatistic->prepop_count = $prepopCount;
            $prepopStatistic->no_prepop_count = $noPrepopCount;
            $prepopStatistic->prepop_with_errors_count = $prepopWithErrorsCount;
            $prepopStatistic->no_prepop_percentage = $noPrepopPercentage;
            $prepopStatistic->prepop_with_errors_percentage = $prepopWithErrorsPercentage;
            $prepopStatistic->profit_margin = $profitMargin;
            $prepopStatistic->save();
        }
    }

    public function getCakeClicks($cakeBaseClicksURL, $affiliateID, $campaignID, $revenueTracker, $prefix)
    {
        $cakeClickURL = $cakeBaseClicksURL."&affiliate_id=$affiliateID&campaign_id=$campaignID&start_date=$this->dateFromStr&end_date=$this->dateToStr";
        Log::info("affiliate_id: $affiliateID");
        Log::info("revenue_tracker_id: $revenueTracker->revenue_tracker_id");
        Log::info("campaign_id: $campaignID");
        Log::info("cake_api: $cakeClickURL");

        $reader = new Reader();

        $reader->elementMap = [
            $prefix.'click_report_response' => 'Sabre\Xml\Element\KeyValue',
            $prefix.'click' => 'Sabre\Xml\Element\KeyValue',
        ];

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

                    $reader->xml($curl->response);
                    $response = $reader->parse();

                    //$clicksCount = $response['value'][$prefix.'row_count'];
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
