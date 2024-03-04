<?php

namespace App\Jobs\Reports;

use App\Campaign;
use App\ExternalPathAffiliateReport;
use App\Jobs\Job;
use App\Lead;
use App\LeadUser;
use Carbon\Carbon;
use Curl\Curl;
use DB;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Log;

class ExternalPathAffiliateReportJob extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $dateFromStr;

    protected $dateToStr;

    protected $affiliates;

    protected $campaigns;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($date)
    {
        $this->dateFromStr = $date;
        $this->dateToStr = $date;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->attempts() > 1) {
            return;
        }

        $startLog = Carbon::now();
        // Log::info(config('app.type'));
        Log::info('Generating External Path Affiliate Reports...');
        Log::info('Date: '.$this->dateFromStr.' - '.$this->dateToStr);
        //2 = internal type
        $params['type'] = 2;

        $dateFromStr = $this->dateFromStr;
        $dateToStr = $this->dateToStr;

        //get the leads revenue per affiliate
        \DB::enableQueryLog();
        \DB::connection('secondary')->enableQueryLog();
        $handpRevenues = Lead::select('leads.affiliate_id', 'leads.campaign_id', 's1', 's2', 's3', 's4', 's5', DB::raw('COUNT(leads.id) as lead_count'), DB::raw('SUM(leads.received) as total_received'), DB::raw('SUM(leads.payout) as total_payout'), DB::raw('DATE(leads.created_at) as created_at'))
            ->join('affiliates', 'leads.affiliate_id', '=', 'affiliates.id')
            ->where('leads.lead_status', '=', 1)
            ->where('leads.s5', '=', 'EXTPATH')
            ->where('affiliates.type', '=', 2)
            ->where(function ($query) use ($dateFromStr, $dateToStr) {
                $query->whereRaw('DATE(leads.created_at) >= DATE(?) and DATE(leads.created_at) <= DATE(?)',
                    [
                        $dateFromStr,
                        $dateToStr,
                    ]);
            })
            ->groupBy('leads.affiliate_id', 'leads.campaign_id', 'leads.s1', 'leads.s2', 'leads.s3', 'leads.s3', 'leads.s4', 'leads.s5', DB::raw('DATE(leads.created_at)'))->get();

        //Get Affiliate IDs that run the external path
        $this->affiliates = LeadUser::where('affiliate_id', '>', 0)->where('s5', 'EXTPATH')->whereBetween('created_at', [$this->dateFromStr.' 00:00:00', $this->dateToStr.' 23:59:59'])->groupBy('affiliate_id')->pluck('affiliate_id')->toArray();
        $this->campaigns = Campaign::where('linkout_offer_id', '>', 0)->pluck('id', 'linkout_offer_id')->toArray();

        Log::info('Main');
        Log::info(DB::getQueryLog());
        Log::info('Second');
        Log::info(DB::connection('secondary')->getQueryLog());

        // Log::info($handpRevenues);
        foreach ($handpRevenues as $handpRevenue) {
            // Log::info($handpRevenue);
            $report = ExternalPathAffiliateReport::firstOrNew([
                'affiliate_id' => $handpRevenue->affiliate_id,
                'campaign_id' => $handpRevenue->campaign_id,
                's1' => $handpRevenue->s1,
                's2' => $handpRevenue->s2,
                's3' => $handpRevenue->s3,
                's4' => $handpRevenue->s4,
                's5' => $handpRevenue->s5,
                'created_at' => Carbon::parse($handpRevenue->created_at)->toDateString(),
            ]);

            $report->lead_count = $handpRevenue->lead_count;
            $report->received = $handpRevenue->total_received;
            $report->payout = $handpRevenue->total_payout;
            $report->save();

        }

        $this->getLinkOutRevenue();

        $endLog = Carbon::now();
        $diffInHours = $startLog->diffInHours($endLog).' hours';

        $emailNotificationRecipient = env('REPORTS_EMAIL_NOTIFICATION_RECIPIENT', 'marwilburton@hotmail.com');

        //send email to Burt to notify that Internal Iframe Affiliate Report Queue was successfully finished
        Mail::send('emails.affiliate_report_execution_email',
            ['startDate' => $this->dateFromStr, 'endDate' => $this->dateToStr, 'executionDuration' => $diffInHours],
            function ($m) use ($emailNotificationRecipient) {
                $m->from('ariel@engageiq.com', 'Ariel Magbanua');
                $m->to($emailNotificationRecipient, 'Marwil Burton')->subject('External Path Affiliate Reports Job Queue Successfully Executed!');
            });
        Log::info('Generating External Path Affiliate Reports DONE');
    }

    public function getLinkOutRevenue()
    {
        // $this->affiliates = [18732];
        foreach ($this->affiliates as $affiliate_id) {
            $cakeBaseClicksURL = config('constants.CAKE_CLICKS_REPORTS_API_V12_UNIQUE');
            $cakeBaseConversionsURL = config('constants.CAKE_CONVERSIONS_API_V15');
            $cr_data = [];
            $rowStart = 1;
            $rowLimit = 2500;
            $totalRows = 0;
            //Clicks
            while (true) {
                // if($rowStart == $rowLimit) break; //forchecking only

                $cakeurl = $cakeBaseClicksURL;
                $cakeurl = str_replace('start_at_row=0', 'start_at_row='.$rowStart, $cakeurl);
                $cakeurl = str_replace('row_limit=0', "row_limit=$rowLimit", $cakeurl);

                $response = $this->getCakeXML($cakeurl, $affiliate_id);

                $totalRows = $response['row_count'];
                if ($totalRows == 0) {
                    Log::info('No Click Data');
                    break;
                } else {
                    $x = $rowStart - 1;
                    $currentRows = $totalRows - $x;
                    if ($currentRows <= 1) {
                        $clicks[] = $response['clicks']['click'];
                    } else {
                        $clicks = $response['clicks']['click'];
                    }

                    foreach ($clicks as $click) {
                        $s1 = is_array($click['sub_id_1']) ? '' : $click['sub_id_1'];
                        $s2 = is_array($click['sub_id_2']) ? '' : $click['sub_id_2'];
                        $s3 = is_array($click['sub_id_3']) ? '' : $click['sub_id_3'];
                        $s4 = is_array($click['sub_id_4']) ? '' : $click['sub_id_4'];
                        $s5 = is_array($click['sub_id_5']) ? '' : $click['sub_id_5'];

                        $offer_id = $click['site_offer']['site_offer_id'];
                        if (! isset($cr_data[$s1][$s2][$s3][$s4][$s5][$offer_id])) {
                            $cr_data[$s1][$s2][$s3][$s4][$s5][$offer_id] = [
                                'r' => 0,
                                'p' => 0,
                            ];
                        }
                        $cr_data[$s1][$s2][$s3][$s4][$s5][$offer_id]['r'] += isset($click['received']['amount']) ? $click['received']['amount'] : 0;
                        $cr_data[$s1][$s2][$s3][$s4][$s5][$offer_id]['p'] += isset($click['paid']['amount']) ? $click['paid']['amount'] : 0;
                    }
                }

                $rowStart += $rowLimit;

                if ($rowStart > $totalRows) {
                    break;
                }
            }

            //Conversions
            $rowStart = 1;
            $rowLimit = 2500;
            $totalRows = 0;
            $nextDay = Carbon::parse($this->dateFromStr)->addDay()->toDateString();

            while (true) {
                $cakeurl = $cakeBaseConversionsURL;
                $cakeurl = str_replace('start_at_row=0', 'start_at_row='.$rowStart, $cakeurl);
                $cakeurl = str_replace('row_limit=0', "row_limit=$rowLimit", $cakeurl);
                $cakeurl .= "&source_affiliate_id=$affiliate_id&start_date=$this->dateFromStr&end_date=$nextDay";

                $response = $this->getCurlResponse($cakeurl);
                $totalRows = $response['row_count'];
                if ($totalRows == 0) {
                    Log::info('No Conversion Data');
                    break;
                } else {
                    if ($totalRows <= 1) {
                        $conversions[] = $response['event_conversions']['event_conversion'];
                    } else {
                        $conversions = $response['event_conversions']['event_conversion'];
                    }
                    foreach ($conversions as $conv) {
                        $s1 = is_array($conv['sub_id_1']) ? '' : $conv['sub_id_1'];
                        $s2 = is_array($conv['sub_id_2']) ? '' : $conv['sub_id_2'];
                        $s3 = is_array($conv['sub_id_3']) ? '' : $conv['sub_id_3'];
                        $s4 = is_array($conv['sub_id_4']) ? '' : $conv['sub_id_4'];
                        $s5 = is_array($conv['sub_id_5']) ? '' : $conv['sub_id_5'];

                        $offer_id = $conv['site_offer']['site_offer_id'];
                        if (! isset($cr_data[$s1][$s2][$s3][$s4][$s5][$offer_id])) {
                            $cr_data[$s1][$s2][$s3][$s4][$s5][$offer_id] = [
                                'r' => 0,
                                'p' => 0,
                            ];
                        }
                        $cr_data[$s1][$s2][$s3][$s4][$s5][$offer_id]['r'] += isset($conv['received']['amount']) ? $conv['received']['amount'] : 0;
                        $cr_data[$s1][$s2][$s3][$s4][$s5][$offer_id]['p'] += isset($conv['paid']['amount']) ? $conv['paid']['amount'] : 0;
                    }
                }

                $rowStart += $rowLimit;

                if ($rowStart > $totalRows) {
                    break;
                }
            }

            $rows = [];

            foreach ($cr_data as $s1 => $s1d) {
                foreach ($s1d as $s2 => $s2d) {
                    foreach ($s2d as $s3 => $s3d) {
                        foreach ($s3d as $s4 => $s4d) {
                            foreach ($s4d as $s5 => $s5d) {
                                //Cake Revenue
                                foreach ($s5d as $offer_id => $data) {

                                    $report = ExternalPathAffiliateReport::firstOrNew([
                                        'affiliate_id' => $affiliate_id,
                                        'campaign_id' => isset($this->campaigns[$offer_id]) ? $this->campaigns[$offer_id] : 0,
                                        's1' => $s1,
                                        's2' => $s2,
                                        's3' => $s3,
                                        's4' => $s4,
                                        's5' => $s5,
                                        'created_at' => $this->dateFromStr,
                                    ]);

                                    $report->lead_count = 0;
                                    $report->received = $data['r'];
                                    $report->payout = $data['p'];
                                    $report->save();
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    /*Helpers*/
    public function getCakeXML($cakeBaseClicksURL, $revenueTracker)
    {
        $nextDay = Carbon::parse($this->dateFromStr)->addDay()->toDateString();
        $cakeClickURL = $cakeBaseClicksURL."&affiliate_id=$revenueTracker&campaign_id=0&start_date=$this->dateFromStr&end_date=$nextDay";
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
                        $this->errors[] = [
                            'Area' => 'CURL API call limit reached!',
                            'Info' => $cakeClickURL,
                            'MSG' => $e->getMessage(),
                        ];
                        $curl->close();
                        break;
                    }

                    continue;
                }
            }

            //stop the process when budget breached
            if ($callCounter == 10) {
                Log::info('getCakeClicks API call limit reached!');
                $this->errors[] = [
                    'Area' => 'CURL API call limit reached!',
                    'Info' => $cakeClickURL,
                    'MSG' => $curl->error(),
                ];
                $curl->close();
                break;
            }

        } while ($curl->error);
        $curl->close();

        return $response;
    }

    public function getCurlResponse($url, $response_type = 'xml')
    {
        Log::info("curl: $url");

        $callCounter = 0;
        $response = null;

        do {
            $curl = new Curl();
            $curl->get($url);

            if ($curl->error) {
                Log::info('getCakeClicks API Error!');
                Log::info($curl->error_message);
                $callCounter++;
            } else {
                try {

                    if ($response_type == 'json') {
                        $json = $curl->response;
                    } elseif ($response_type == 'LIBXML_NOWARNING') {
                        $xml = simplexml_load_string($curl->response, 'SimpleXMLElement', LIBXML_NOWARNING);
                        $json = json_encode($xml);
                    } else {
                        $xml = simplexml_load_string($curl->response);
                        $json = json_encode($xml);
                    }
                    $response = json_decode($json, true);
                } catch (Exception $e) {
                    Log::info($e->getCode());
                    Log::info($e->getMessage());
                    $callCounter++;

                    //stop the process when budget breached
                    if ($callCounter == 10) {
                        Log::info('getCakeClicks API call limit reached!');
                        $this->errors[] = [
                            'Area' => 'CURL API call limit reached!',
                            'Info' => $url,
                            'MSG' => $e->getMessage(),
                        ];
                        $curl->close();
                        break;
                    }

                    continue;
                }
            }

            //stop the process when budget breached
            if ($callCounter == 10) {
                Log::info('getCakeClicks API call limit reached!');
                $this->errors[] = [
                    'Area' => 'CURL API call limit reached!',
                    'Info' => $url,
                    'MSG' => '',
                ];
                $curl->close();
                break;
            }

        } while ($curl->error);

        $curl->close();

        return $response;
    }
}
