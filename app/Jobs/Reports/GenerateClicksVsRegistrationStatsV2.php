<?php

namespace App\Jobs\Reports;

use App\Affiliate;
use App\AffiliateRevenueTracker;
use App\Jobs\Job;
use App\LeadUser;
use Carbon\Carbon;
use Curl\Curl;
use DB;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Log;

class GenerateClicksVsRegistrationStatsV2 extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $date_from;

    protected $date_to;

    protected $affiliates = [];

    protected $startLog;

    protected $endLog;

    protected $revTrackers;

    protected $availableRevTrackers;

    protected $subIDBreakdown = [];

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($date)
    {
        $this->startLog = Carbon::now();
        $date = Carbon::parse($date);
        $this->date_from = $date->toDateString();
        $this->date_to = $date->addDay()->toDateString();
        $this->affiliates = Affiliate::pluck('id')->toArray();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->attempts() > 1) {
            return;
        }

        // Get Revenue Trackers Details
        $revenue_trackers = AffiliateRevenueTracker::select('affiliate_id', 'campaign_id', 'revenue_tracker_id', 'offer_id', 'report_subid_breakdown_status', 'rsib_s1', 'rsib_s2', 'rsib_s3', 'rsib_s4')
            ->groupBy('affiliate_id', 'revenue_tracker_id')->get();
        $this->revTrackers = $revenue_trackers;
        $this->getAvaliableRevTrackers();

        Log::info('Clicks Vs Registrations Job Queue Starting...');
        Log::info($this->date_from.' - '.$this->date_to);
        $registrations = [];
        $registration_report = LeadUser::whereRAW("created_at BETWEEN '$this->date_from 00:00:00' AND '$this->date_from 23:59:59'")
            ->selectRAW('affiliate_id,revenue_tracker_id, s1, s2, s3, s4, s5, COUNT(*) as registrations')
            ->groupBy(['affiliate_id', 'revenue_tracker_id', 's1', 's2', 's3', 's4', 's5'])
            ->get();
        // Log::info($registration_report);
        // exit;
        foreach ($registration_report as $report) {
            $allowBreakdown = isset($this->subIDBreakdown[$report->revenue_tracker_id]['status']) ? $this->subIDBreakdown[$report->revenue_tracker_id]['status'] : 0;
            $s1Breakdown = isset($this->subIDBreakdown[$report->revenue_tracker_id]['s1']) ? $this->subIDBreakdown[$report->revenue_tracker_id]['s1'] : 0;
            $s2Breakdown = isset($this->subIDBreakdown[$report->revenue_tracker_id]['s2']) ? $this->subIDBreakdown[$report->revenue_tracker_id]['s2'] : 0;
            $s3Breakdown = isset($this->subIDBreakdown[$report->revenue_tracker_id]['s3']) ? $this->subIDBreakdown[$report->revenue_tracker_id]['s3'] : 0;
            $s4Breakdown = isset($this->subIDBreakdown[$report->revenue_tracker_id]['s4']) ? $this->subIDBreakdown[$report->revenue_tracker_id]['s4'] : 0;
            $s5Breakdown = 0;

            $s1 = $allowBreakdown && $s1Breakdown ? $report->s1 : '';
            $s2 = $allowBreakdown && $s2Breakdown ? $report->s2 : '';
            $s3 = $allowBreakdown && $s3Breakdown ? $report->s3 : '';
            $s4 = $allowBreakdown && $s4Breakdown ? $report->s4 : '';
            $s5 = '';

            $registrations[$report->affiliate_id][$report->revenue_tracker_id][$s1][$s2][$s3][$s4][$s5] = $report->registrations;
        }

        $cakeBaseClicksURL = config('constants.CAKE_CLICKS_REPORTS_API_V12');
        $stats = [];
        // foreach($revenue_trackers as $rt)
        foreach ($this->availableRevTrackers as $rt) {
            $rt_data = [];
            $affiliate_id = $rt['affiliate_id'];
            $campaign_id = $rt['campaign_id'];
            $offer_id = $rt['offer_id'];
            $revenue_tracker_id = $rt['revenue_tracker_id'];
            $allowBreakdown = isset($this->subIDBreakdown[$revenue_tracker_id]['status']) ? $this->subIDBreakdown[$revenue_tracker_id]['status'] : 0;
            $s1Breakdown = isset($this->subIDBreakdown[$revenue_tracker_id]['s1']) ? $this->subIDBreakdown[$revenue_tracker_id]['s1'] : 0;
            $s2Breakdown = isset($this->subIDBreakdown[$revenue_tracker_id]['s2']) ? $this->subIDBreakdown[$revenue_tracker_id]['s2'] : 0;
            $s3Breakdown = isset($this->subIDBreakdown[$revenue_tracker_id]['s3']) ? $this->subIDBreakdown[$revenue_tracker_id]['s3'] : 0;
            $s4Breakdown = isset($this->subIDBreakdown[$revenue_tracker_id]['s4']) ? $this->subIDBreakdown[$revenue_tracker_id]['s4'] : 0;
            $s5Breakdown = 0;
            $rowStart = 1;
            $rowLimit = 2500;
            $totalRows = 0;

            // Log::info("A: $affiliate_id R: $revenue_tracker_id C: $campaign_id O: $offer_id");

            while (true) {
                // Log::info("START ROW: $rowStart");
                $cakeurl = $cakeBaseClicksURL;
                $cakeurl = str_replace('offer_id=0', "offer_id=$offer_id", $cakeurl);
                $cakeurl = str_replace('campaign_id=0', "campaign_id=$campaign_id", $cakeurl);
                $cakeurl = str_replace('start_at_row=0', 'start_at_row='.$rowStart, $cakeurl);
                $cakeurl = str_replace('row_limit=0', "row_limit=$rowLimit", $cakeurl);

                $response = $this->getCakeXML($cakeurl, $affiliate_id, $campaign_id, $revenue_tracker_id);
                $clicks = [];

                $totalRows = $response['row_count'];

                if ($totalRows == 0) {
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
                        $s1 = ! is_array($click['sub_id_1']) && $allowBreakdown && $s1Breakdown ? $click['sub_id_1'] : '';
                        $s2 = ! is_array($click['sub_id_2']) && $allowBreakdown && $s2Breakdown ? $click['sub_id_2'] : '';
                        $s3 = ! is_array($click['sub_id_3']) && $allowBreakdown && $s3Breakdown ? $click['sub_id_3'] : '';
                        $s4 = ! is_array($click['sub_id_4']) && $allowBreakdown && $s4Breakdown ? $click['sub_id_4'] : '';
                        // $s5 = !is_array($click['sub_id_5']) && $allowBreakdown ? $click['sub_id_5'] : '';
                        $s5 = '';

                        if (! isset($rt_data[$s1][$s2][$s3][$s4][$s5])) {
                            $rt_data[$s1][$s2][$s3][$s4][$s5] = 0;
                        }

                        $rt_data[$s1][$s2][$s3][$s4][$s5] += 1;
                    }
                }

                $rowStart += $rowLimit;
                if ($rowStart > $totalRows) {
                    break;
                }
            }

            foreach ($rt_data as $s1 => $s1d) {
                foreach ($s1d as $s2 => $s2d) {
                    foreach ($s2d as $s3 => $s3d) {
                        foreach ($s3d as $s4 => $s4d) {
                            foreach ($s4d as $s5 => $clicks) {

                                $registration_count = 0;
                                if (isset($registrations[$affiliate_id][$revenue_tracker_id][$s1][$s2][$s3][$s4][$s5])) {
                                    $registration_count = $registrations[$affiliate_id][$revenue_tracker_id][$s1][$s2][$s3][$s4][$s5];
                                    unset($registrations[$affiliate_id][$revenue_tracker_id][$s1][$s2][$s3][$s4][$s5]);
                                }

                                $percentage = 0;
                                if ($registration_count > 0 && $clicks > 0) {
                                    $percentage = floatval($registration_count / $clicks);
                                }

                                if (($registration_count > 0 || $clicks > 0) && in_array($affiliate_id, $this->affiliates)) {
                                    $stats[] = [
                                        'affiliate_id' => $affiliate_id,
                                        'revenue_tracker_id' => $revenue_tracker_id,
                                        'created_at' => $this->date_from,
                                        's1' => $s1,
                                        's2' => $s2,
                                        's3' => $s3,
                                        's4' => $s4,
                                        's5' => $s5,
                                        'clicks' => $clicks,
                                        'registration_count' => $registration_count,
                                        'percentage' => $percentage,
                                    ];
                                }
                            }
                        }
                    }
                }
            }
        }

        foreach ($registrations as $affiliate_id => $regData) {
            foreach ($regData as $revenue_tracker_id => $rtData) {
                foreach ($rtData as $s1 => $s1Data) {
                    foreach ($s1Data as $s2 => $s2Data) {
                        foreach ($s2Data as $s3 => $s3Data) {
                            foreach ($s3Data as $s4 => $s4Data) {
                                foreach ($s4Data as $s5 => $registration_count) {
                                    if (in_array($affiliate_id, $this->affiliates)) {
                                        $stats[] = [
                                            'affiliate_id' => $affiliate_id,
                                            'revenue_tracker_id' => $revenue_tracker_id,
                                            'created_at' => $this->date_from,
                                            's1' => $s1,
                                            's2' => $s2,
                                            's3' => $s3,
                                            's4' => $s4,
                                            's5' => $s5,
                                            'clicks' => 0,
                                            'registration_count' => $registration_count,
                                            'percentage' => 0,
                                        ];
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        DB::table('clicks_vs_registration_statistics')->where('created_at', $this->date_from)->delete();

        $chunks = array_chunk($stats, 1000);
        foreach ($chunks as $chunk) {
            DB::table('clicks_vs_registration_statistics')->insert($chunk);
        }

        $this->endLog = Carbon::now();

        $this->sendEmail();
    }

    public function getAvaliableRevTrackers()
    {
        $start = Carbon::now();
        $cakeurl = config('constants.CAKE_API_CAMPAIGN_SUMMARY_ALL_CAMPAIGNS_BASE_URL_V5');
        $cakeurl .= "&start_date=$this->date_from&end_date=$this->date_to";

        $revTrackerCampaign = [];
        foreach ($this->revTrackers as $rt) {
            $revTrackerCampaign[$rt->affiliate_id][$rt->campaign_id] = [
                'rt' => $rt->revenue_tracker_id,
                'oid' => $rt->offer_id,
            ];

            $this->subIDBreakdown[$rt->revenue_tracker_id] = [
                'status' => $rt->report_subid_breakdown_status,
                's1' => $rt->rsib_s1,
                's2' => $rt->rsib_s2,
                's3' => $rt->rsib_s3,
                's4' => $rt->rsib_s4,
                's5' => 0,
            ];
        }

        $response = $this->getCurlResponse($cakeurl);

        if ($response['row_count'] <= 1) {
            $summary[] = $response['campaigns']['campaign_summary'];
        } else {
            $summary = $response['campaigns']['campaign_summary'];
        }

        foreach ($summary as $campaign) {
            $aff_id = $campaign['source_affiliate']['source_affiliate_id'];
            $cam_id = $campaign['campaign']['campaign_id'];

            if (isset($revTrackerCampaign[$aff_id][$cam_id])) {
                $rt_id = $revTrackerCampaign[$aff_id][$cam_id]['rt'];
                $off_id = $revTrackerCampaign[$aff_id][$cam_id]['oid'];
                $this->availableRevTrackers[] = [
                    'type' => $campaign['price_format'],
                    'affiliate_id' => $aff_id,
                    'campaign_id' => $cam_id,
                    'revenue_tracker_id' => $rt_id,
                    'offer_id' => $off_id,
                ];
            }
        }
    }

    public function getCakeXML($cakeBaseClicksURL, $affiliateID, $campaignID, $revenueTracker)
    {
        $cakeClickURL = $cakeBaseClicksURL."&affiliate_id=$affiliateID&campaign_id=$campaignID&start_date=$this->date_from&end_date=$this->date_to&include_duplicates=true";
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
                        $curl->close();
                        break;
                    }

                    continue;
                }
            }

            //stop the process when budget breached
            if ($callCounter == 10) {
                Log::info('getCakeClicks API call limit reached!');
                $curl->close();
                break;
            }

        } while ($curl->error);

        $curl->close();

        return $response;
    }

    public function sendEmail()
    {

        $diffInHours = $this->startLog->diffInMinutes($this->endLog).' minute/s';
        $emailNotificationRecipient = env('REPORTS_EMAIL_NOTIFICATION_RECIPIENT', 'marwilburton@hotmail.com');

        //send email to Burt to notify that Affiliate Report Queue was successfully finished
        Mail::send('emails.clicks_vs_registrations',
            ['startDate' => $this->date_from, 'endDate' => $this->date_to, 'executionDuration' => $diffInHours],
            function ($m) use ($emailNotificationRecipient) {
                $m->from('ariel@engageiq.com', 'Ariel Magbanua');
                $m->to($emailNotificationRecipient, 'Marwil Burton')->subject('Clicks Vs Registrations V2 Job Queue Successfully Executed!');
            });

        Log::info('Clicks Vs Registrations Job Queue Successfully Executed!');
    }
}
