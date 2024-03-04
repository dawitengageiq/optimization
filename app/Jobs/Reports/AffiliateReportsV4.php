<?php

namespace App\Jobs\Reports;

use App\AffiliateReport;
use App\AffiliateRevenueTracker;
use App\AffiliateWebsite;
use App\AffiliateWebsiteReport;
use App\CakeRevenue;
use App\CampaignView;
use App\ClickLogRevTrackers;
use App\Helpers\AffiliateReportExcelGeneratorHelper;
use App\Jobs\Job;
use App\Lead;
use App\LeadUser;
use App\LeadUserUniqueInfo;
use App\PrepopStatistic;
use App\RevenueTrackerCakeStatistic;
use App\Setting;
use Carbon\Carbon;
use Curl\Curl;
use DB;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\QueryException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Log;

class AffiliateReportsV4 extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $dateFromStr;

    protected $dateToStr;

    protected $affiliates;

    protected $errors;

    protected $restriction;

    protected $startLog;

    protected $endLog;

    protected $availableRevTrackers;

    protected $revTrackers;

    protected $registrations;

    protected $revTrackerRestrict;

    protected $affiliateRestrict;

    protected $timeLog;

    protected $subIDBreakdown = [];

    protected $revenues = [];

    protected $payouts = [];

    protected $excludedRevs = [];

    protected $clickLogRevTrackers = [];

    protected $clickLogs = [];

    protected $clickLogEmails = [];

    protected $clickLogAllAffiliates = false;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($dateFromStr, $dateToStr, $restriction, $affiliate_id, $rev_tracker_id)
    {
        $this->startLog = Carbon::now();
        $this->dateFromStr = $dateFromStr;
        $this->dateToStr = $dateToStr;
        $this->affiliates = AffiliateRevenueTracker::pluck('affiliate_id', 'revenue_tracker_id');
        $this->errors = [];
        $this->restriction = $restriction;
        $this->revTrackerRestrict = $rev_tracker_id;
        $this->affiliateRestrict = $affiliate_id;
        $this->registrations = [];
        $this->timeLog = [];
        $this->excludedRevs = [];

        $settings = Setting::where('code', 'excessive_affiliate_subids')->select('description')->first();
        if ($settings->description != '') {
            $this->excludedRevs = explode(',', str_replace(' ', '', $settings->description));
        }

        /* Click Log Tracking */
        $settings = Setting::where('code', 'clic_logs_apply_all_affiliates')->select('integer_value')->first();
        $this->clickLogAllAffiliates = $settings->integer_value == 1 ? true : false;
        if (! $this->clickLogAllAffiliates) {
            $this->clickLogRevTrackers = ClickLogRevTrackers::pluck('revenue_tracker_id')->toArray();
        }
        \Log::info('Click Log Settings');
        \Log::info($this->clickLogAllAffiliates ? 'All Affiliates' : 'Not all');
        \Log::info($this->clickLogRevTrackers);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->attempts() > 1) {
            return;
        }

        Log::info('Generating Internal Affiliate Reports Version 4...');

        if ($this->restriction != '') {
            $this->runRestrict();

            return;
        }

        // if($this->revTrackerRestrict != '' || $this->affiliateRestrict != '') {
        //     Log::info($this->revTrackerRestrict);
        //     Log::info($this->affiliateRestrict);
        //     $this->runAffiliateRestrict();
        //     return;
        // }

        Log::info('------------------------------');
        Log::info('Delete Current Data');
        $this->deleteCurrentRecord();

        //Get Revenue Trackers Details
        $revenue_trackers = AffiliateRevenueTracker::select('affiliate_id', 'campaign_id', 'revenue_tracker_id', 'offer_id', 'report_subid_breakdown_status', 'rsib_s1', 'rsib_s2', 'rsib_s3', 'rsib_s4')->groupBy('affiliate_id', 'revenue_tracker_id')->get();
        // $revenue_trackers = AffiliateRevenueTracker::where('revenue_tracker_id', 7612)->select('affiliate_id','campaign_id','revenue_tracker_id', 'offer_id')->groupBy('affiliate_id','revenue_tracker_id')->get();
        $this->revTrackers = $revenue_trackers;
        Log::info('------------------------------');
        Log::info('Get Available Revenue Trackers');
        $this->getAvaliableRevTrackers();
        // Log::info($this->availableRevTrackers);

        Log::info('----------------------------');
        Log::info('Get Lead Total Revenue');
        $this->getRevTrackerLeadRevenue();

        Log::info('------------------------------');
        Log::info('Get Revenue Tracker Click, Payout & Revenue');

        $start = Carbon::now();
        //Get Rev Tracker Payout and Revenue
        foreach ($this->availableRevTrackers as $rt) {
            $affiliate_id = $rt['affiliate_id'];
            $campaign_id = $rt['campaign_id'];
            $offer_id = $rt['offer_id'];
            $revenue_tracker_id = $rt['revenue_tracker_id'];
            $type = $rt['type'];

            Log::info('------------------------------');
            Log::info("Affiliate_id: $affiliate_id");
            Log::info("Revenue_tracker_id: $revenue_tracker_id");
            Log::info("campaign_id: $campaign_id");
            Log::info("type: $type");

            Log::info('------------------------------');
            Log::info('Get Revenue Tracker Revenue');
            $this->getRevTrackerCakeRevenue($affiliate_id, $revenue_tracker_id);

            Log::info('------------------------------');
            Log::info('Generate Revenue Tracker Clicks & Payout');
            if (! in_array($revenue_tracker_id, $this->excludedRevs)) {
                if ($type == 'CPA') {
                    $this->getRevenueTrackerCPAPayout($rt);
                } elseif ($type == 'CPM') {
                    $this->getRevenueTrackerCPMPayout($rt);
                } else {
                    $this->getRevenueTrackerCPCPayout($rt);
                }
            }
        }

        // //Click Log Tracing Process
        // $this->clickLogsTracingProcess();
        // exit;

        //Save Payout and Revenue for CD1
        $this->getCD1PayoutAndRevenue();

        //Get JS Path
        Log::info('------------------------------');
        Log::info('Get JS Paths Revenue');
        $this->getJsPathRevenue();

        $this->timeTracker('Rev Tracker Clicks, Payout, Revenue, Clicks vs Reg, PrePop & Cake Revenue', $start, Carbon::now());

        // Log::info("------------------------------");
        // Log::info("Get Affluent Revenue");
        // $this->getAffluentRevenue();

        Log::info('------------------------------');
        Log::info('Get SBG Revenue');
        $this->getSBGRevenue();

        Log::info('------------------------------');
        Log::info('Get EPOLL Revenue');
        $this->getEPollRevenue();

        // Log::info("------------------------------");
        // Log::info("Get Jobs2Shop Revenue");
        // $this->getJobs2ShopRevenue();

        Log::info('------------------------------');
        Log::info('Get Tiburon Revenue');
        $this->getTiburonRevenue();

        Log::info('------------------------------');
        Log::info('Get Ifficient Revenue');
        $this->getIfficientRevenue();

        Log::info('------------------------------');
        Log::info('Get Rexadz Revenue');
        $this->getRexadzRevenue();

        Log::info('------------------------------');
        Log::info('Get Permission Data Revenue');
        $this->getPermissionDataRevenue();

        Log::info('------------------------------');
        Log::info('Get PushCrew Revenue');
        $this->pushCrewRevenue();

        Log::info('------------------------------');
        Log::info('Get Campaign Revenue View Statistics');
        $this->getCampaignRevenueViewStatistics();

        Log::info('------------------------------');
        Log::info('Set Revenue for NO Revenue');
        $this->setRevenueForNoRevenue();

        Log::info('------------------------------');
        Log::info('Set Payout for NO Clicks');
        $this->setPayoutforNoClicks();

        Log::info('------------------------------');
        Log::info('Delete Zero Values');
        $this->deleteZeroValues();

        Log::info('------------------------------');
        Log::info('Generate Excel Report');
        $start = Carbon::now();
        $excel = new AffiliateReportExcelGeneratorHelper($this->dateFromStr, $this->dateFromStr, 1);
        $excel->deleteTempFile();
        $excel->generate();
        $this->timeTracker('Excel Report', $start, Carbon::now());

        $this->endLog = Carbon::now();

        Log::info('------------------------------');
        Log::info('Email');
        $this->mail();

        //Click Log Tracing Process
        $this->clickLogsTracingProcess();
    }

    public function deleteCurrentRecord($affiliate_id = null, $rev_tracker = null, $campaign_id = null)
    {
        $start = Carbon::now();
        $date = $this->dateFromStr;

        //DB::enableQueryLog();

        //Revenue = Affiliate Reports; Cake Revenue
        $affiliate_reports = DB::table('affiliate_reports')->where('created_at', $date);
        if ($rev_tracker != null) {
            $affiliate_reports->where('revenue_tracker_id', $rev_tracker);
        }
        if ($affiliate_id != null) {
            $affiliate_reports->where('affiliate_id', $affiliate_id);
        }
        if ($campaign_id != null) {
            $affiliate_reports->where('campaign_id', $campaign_id);
        }
        $affiliate_reports->delete();

        $cake_revenues = DB::table('cake_revenues')->where('created_at', $date);
        if ($rev_tracker != null) {
            $cake_revenues->where('revenue_tracker_id', $rev_tracker);
        }
        if ($affiliate_id != null) {
            $cake_revenues->where('affiliate_id', $affiliate_id);
        }
        $cake_revenues->delete();

        if ($campaign_id == null) {
            //Payout = Revenue Tracker Cake Statistics; PrepopStatistic
            $revenue_tracker_cake_statistics = DB::table('revenue_tracker_cake_statistics')->where('created_at', $date);
            if ($rev_tracker != null) {
                $revenue_tracker_cake_statistics->where('revenue_tracker_id', $rev_tracker);
            }
            if ($affiliate_id != null) {
                $revenue_tracker_cake_statistics->where('affiliate_id', $affiliate_id);
            }
            $revenue_tracker_cake_statistics->delete();

            $prepop_statistics = DB::table('prepop_statistics')->where('created_at', $date);
            if ($rev_tracker != null) {
                $prepop_statistics->where('revenue_tracker_id', $rev_tracker);
            }
            if ($affiliate_id != null) {
                $prepop_statistics->where('affiliate_id', $affiliate_id);
            }
            $prepop_statistics->delete();
        }

        $click_logs = DB::table('click_log_trace_infos')->whereBetween('click_date', [$date.' 00:00:00', $date.' 23:59:59']);
        if ($rev_tracker != null) {
            $click_logs->where('revenue_tracker_id', $rev_tracker);
        }
        if ($affiliate_id != null) {
            $click_logs->where('affiliate_id', $affiliate_id);
        }
        $click_logs->delete();

        //Log::info(DB::getQueryLog());

        $this->timeTracker('Deleting Existing Data', $start, Carbon::now());
    }

    public function clickLogsTracingProcess()
    {
        Log::info('------------------------------');
        Log::info('Process Click Logs Tracing Process');

        // \Log::info($this->clickLogs);

        if (count($this->clickLogEmails) > 0) {
            Log::info('Get DB prepop');
            // DB::enableQueryLog();
            $db_existing = LeadUserUniqueInfo::whereIn('email', $this->clickLogEmails)->pluck('email')->toArray();
            Log::info('Get Reg details');
            $users = LeadUser::whereIn('email', $this->clickLogEmails)
                ->select('id', 'email', 'revenue_tracker_id', 'created_at')
                ->get();
            // Log::info(DB::getQueryLog());
            Log::info('Existing DB Prepop');
            Log::info($db_existing);
            $lead_users = [];
            foreach ($users as $u) {
                $email = trim(strtolower($u->email));
                $lead_users[$email][] = $u;
            }

            $clicks = [];
            foreach ($this->clickLogs as $log) {
                $email = trim(strtolower($log['email']));
                $rev_leads = isset($lead_users[$email]) ? $lead_users[$email] : null;
                $first_lead = $rev_leads != null ? $rev_leads[0] : null;
                $last_lead = $rev_leads != null ? $rev_leads[count($rev_leads) - 1] : null;

                $log['is_dbprepoped'] = in_array($email, $db_existing) ? 1 : 0;
                $log['reg_count'] = $rev_leads != null ? count($rev_leads) : 0;
                $log['first_entry_rev_id'] = $first_lead != null ? $first_lead->revenue_tracker_id : null;
                $log['first_entry_timestamp'] = $first_lead != null ? $first_lead->created_at : null;
                $log['last_entry_rev_id'] = $last_lead != null ? $last_lead->revenue_tracker_id : null;
                $log['last_entry_timestamp'] = $last_lead != null ? $last_lead->created_at : null;
                $clicks[] = $log;
            }

            // \Log::info($clicks);
            Log::info('Saving');
            $chunks = array_chunk($clicks, 1000);
            foreach ($chunks as $chunk) {
                try {
                    DB::table('click_log_trace_infos')->insert($chunk);
                } catch (QueryException $e) {
                    Log::info('Click Log Tracing Saving Error!');
                    Log::info($e->getMessage());

                    $this->errors[] = [
                        'Area' => 'Click Log Tracing',
                        'Info' => 'DB Saving',
                        'MSG' => $e->getMessage(),
                    ];
                }
            }
            Log::info('Click Log Tracing Done');
        } else {
            Log::info('No Clicks');
        }

    }

    public function getCD1PayoutAndRevenue()
    {
        Log::info('------------------------------');
        Log::info('Generate Revenue Tracker Cake Statistics for CD1');
        $rtcs = RevenueTrackerCakeStatistic::firstOrNew([
            'affiliate_id' => 1,
            'revenue_tracker_id' => 1,
            'cake_campaign_id' => 1,
            'type' => 1,
            'created_at' => $this->dateFromStr,
        ]);
        $rtcs->clicks = 0;
        $rtcs->payout = 0;
        $rtcs->s1 = '';
        $rtcs->s2 = '';
        $rtcs->s3 = '';
        $rtcs->s4 = '';
        $rtcs->s5 = '';
        $rtcs->save();

        Log::info('------------------------------');
        Log::info('Get Cake Total Revenue for CD1');
        $this->getRevTrackerCakeRevenue(1, 1);
    }

    public function getAvaliableRevTrackers()
    {
        $start = Carbon::now();
        $cakeurl = config('constants.CAKE_API_CAMPAIGN_SUMMARY_ALL_CAMPAIGNS_BASE_URL_V5');
        $cakeurl .= "&start_date=$this->dateFromStr&end_date=$this->dateToStr";

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
                's5' => $rt->offer_id == 0 ? $rt->rsib_s5 : 0,
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
        $this->timeTracker('Campaign Summary', $start, Carbon::now());
    }

    public function getRevenueTrackerCPCPayout($revenue_tracker)
    {
        $cakeBaseClicksURL = config('constants.CAKE_CLICKS_REPORTS_API_V12');
        $requiredParameters = config('constants.PREPOP_PARAMETERS');
        $affiliate_id = $revenue_tracker['affiliate_id'];
        $campaign_id = $revenue_tracker['campaign_id'];
        $offer_id = $revenue_tracker['offer_id'];
        $revenue_tracker_id = $revenue_tracker['revenue_tracker_id'];
        $type = $revenue_tracker['type'];
        $allowBreakdown = isset($this->subIDBreakdown[$revenue_tracker_id]['status']) ? $this->subIDBreakdown[$revenue_tracker_id]['status'] : 0;
        $s1Breakdown = isset($this->subIDBreakdown[$revenue_tracker_id]['s1']) ? $this->subIDBreakdown[$revenue_tracker_id]['s1'] : 0;
        $s2Breakdown = isset($this->subIDBreakdown[$revenue_tracker_id]['s2']) ? $this->subIDBreakdown[$revenue_tracker_id]['s2'] : 0;
        $s3Breakdown = isset($this->subIDBreakdown[$revenue_tracker_id]['s3']) ? $this->subIDBreakdown[$revenue_tracker_id]['s3'] : 0;
        $s4Breakdown = isset($this->subIDBreakdown[$revenue_tracker_id]['s4']) ? $this->subIDBreakdown[$revenue_tracker_id]['s4'] : 0;
        // $s5Breakdown = $this->subIDBreakdown[$revenue_tracker_id]['s5'];

        $rt_data = [];

        $rowStart = 1;
        $rowLimit = 2500;
        $totalRows = 0;

        while (true) {
            $cakeurl = $cakeBaseClicksURL;
            $cakeurl = str_replace('offer_id=0', "offer_id=$offer_id", $cakeurl);
            $cakeurl = str_replace('start_at_row=0', 'start_at_row='.$rowStart, $cakeurl);
            $cakeurl = str_replace('row_limit=0', "row_limit=$rowLimit", $cakeurl);
            $cakeurl .= "&affiliate_id=$affiliate_id&campaign_id=$campaign_id&start_date=$this->dateFromStr&end_date=$this->dateToStr&include_duplicates=true";

            $response = $this->getCurlResponse($cakeurl);
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
                    // $s5 = !is_array($click['sub_id_5']) ? $click['sub_id_5'] : '';
                    $s5 = '';

                    if (! isset($rt_data[$s1][$s2][$s3][$s4][$s5])) {
                        $rt_data[$s1][$s2][$s3][$s4][$s5] = [
                            'click' => 0,
                            'uclick' => 0,
                            'payout' => 0,
                            'no_prepop_count' => 0,
                            'prepop_count' => 0,
                            'prepop_werrors_count' => 0,
                        ];
                    }
                    //Count Click, Revenue & Payout
                    $rt_data[$s1][$s2][$s3][$s4][$s5]['click'] += 1;
                    $rt_data[$s1][$s2][$s3][$s4][$s5]['payout'] += isset($click['paid']['amount']) ? $click['paid']['amount'] : 0;

                    //Click Log Tracing
                    if ($this->clickLogAllAffiliates || in_array($revenue_tracker_id, $this->clickLogRevTrackers)) {

                        $requestURL = $click['request_url'];
                        $parts = parse_url($requestURL, PHP_URL_QUERY);
                        $queryParams = [];
                        parse_str($parts, $queryParams);

                        $email = isset($queryParams['email']) ? urldecode($queryParams['email']) : '';
                        $email = trim(strtolower($email));

                        if ($email != '' && ! in_array($email, $this->clickLogEmails)) {
                            $this->clickLogEmails[] = $email;
                        }

                        $this->clickLogs[] = [
                            'click_date' => Carbon::parse($click['click_date'])->toDateTimeString(),
                            'click_id' => $click['click_id'],
                            'email' => $email,
                            'affiliate_id' => $affiliate_id,
                            'revenue_tracker_id' => $revenue_tracker_id,
                            'ip' => $click['ip_address'],
                        ];
                    }

                    //NO S5
                    if ($click['duplicate'] == 'false' || $click === false) {
                        if (! isset($no_s5_data[$s1][$s2][$s3][$s4])) {
                            $no_s5_data[$s1][$s2][$s3][$s4] = [
                                'click' => 0,
                                'payout' => 0,
                            ];
                        }

                        $no_s5_data[$s1][$s2][$s3][$s4]['click'] += 1;
                        $no_s5_data[$s1][$s2][$s3][$s4]['payout'] += isset($click['paid']['amount']) ? $click['paid']['amount'] : 0;
                    }

                    //Unique Checking
                    if ($click['duplicate'] == 'false' || $click === false) {
                        $rt_data[$s1][$s2][$s3][$s4][$s5]['uclick'] += 1;

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
            }

            $rowStart += $rowLimit;

            if ($rowStart > $totalRows) {
                break;
            }
        }

        $rows = [];
        $rows_ps = [];

        foreach ($rt_data as $s1 => $s1d) {
            foreach ($s1d as $s2 => $s2d) {
                foreach ($s2d as $s3 => $s3d) {
                    foreach ($s3d as $s4 => $s4d) {
                        foreach ($s4d as $s5 => $data) {

                            if ($data['uclick'] > 0) {
                                // Prep Rev Tracker Click and Payout
                                $rows[] = [
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
                                    'clicks' => $data['click'],
                                    'payout' => $data['payout'],
                                ];

                                //PrePop Statistics
                                $npp = $data['no_prepop_count'] == 0 && $data['uclick'] == 0 ? 0 : (floatval($data['no_prepop_count']) / floatval($data['uclick']));
                                $pwep = $data['prepop_werrors_count'] == 0 && $data['uclick'] == 0 ? 0 : (floatval($data['prepop_werrors_count']) / floatval($data['uclick']));

                                $rows_ps[] = [
                                    'affiliate_id' => $affiliate_id,
                                    'revenue_tracker_id' => $revenue_tracker_id,
                                    'created_at' => $this->dateFromStr,
                                    's1' => $s1,
                                    's2' => $s2,
                                    's3' => $s3,
                                    's4' => $s4,
                                    's5' => $s5,
                                    'total_clicks' => $data['uclick'],
                                    'prepop_count' => $data['prepop_count'],
                                    'no_prepop_count' => $data['no_prepop_count'],
                                    'prepop_with_errors_count' => $data['prepop_werrors_count'],
                                    'no_prepop_percentage' => $npp,
                                    'prepop_with_errors_percentage' => $pwep,
                                ];
                            }
                        }
                    }
                }
            }
        }

        $chunks = array_chunk($rows, 1000);
        foreach ($chunks as $chunk) {
            try {
                DB::table('revenue_tracker_cake_statistics')->insert($chunk);
                //Log::info('Rev Tracker Cake Stats Chunks total: ' . count($chunk));
            } catch (QueryException $e) {
                Log::info('Rev Tracker Cake Stats Saving Error!');
                Log::info($e->getMessage());

                $this->errors[] = [
                    'Area' => 'Rev Tracker Cake Stats',
                    'Info' => 'DB Saving',
                    'MSG' => $e->getMessage(),
                ];
            }
        }

        $chunks_ps = array_chunk($rows_ps, 1000);
        foreach ($chunks_ps as $chunk) {
            try {
                DB::table('prepop_statistics')->insert($chunk);
                //Log::info('Pre Pop Stats Chunks total: ' . count($chunk));
            } catch (QueryException $e) {
                Log::info('Pre Pop Stats Saving Error!');
                Log::info($e->getMessage());

                $this->errors[] = [
                    'Area' => 'Pre Pop Stats',
                    'Info' => 'DB Saving',
                    'MSG' => $e->getMessage(),
                ];
            }
        }
    }

    public function getRevenueTrackerCPAPayout($revenue_tracker)
    {
        $cakeBaseClicksURL = config('constants.CAKE_CLICKS_REPORTS_API_V12');
        $cakeBaseConversionsURL = config('constants.CAKE_CONVERSIONS_API_V15');
        $requiredParameters = config('constants.PREPOP_PARAMETERS');
        $affiliate_id = $revenue_tracker['affiliate_id'];
        $campaign_id = $revenue_tracker['campaign_id'];
        $offer_id = $revenue_tracker['offer_id'];
        $revenue_tracker_id = $revenue_tracker['revenue_tracker_id'];
        $type = $revenue_tracker['type'];
        $allowBreakdown = isset($this->subIDBreakdown[$revenue_tracker_id]['status']) ? $this->subIDBreakdown[$revenue_tracker_id]['status'] : 0;
        $s1Breakdown = isset($this->subIDBreakdown[$revenue_tracker_id]['s1']) ? $this->subIDBreakdown[$revenue_tracker_id]['s1'] : 0;
        $s2Breakdown = isset($this->subIDBreakdown[$revenue_tracker_id]['s2']) ? $this->subIDBreakdown[$revenue_tracker_id]['s2'] : 0;
        $s3Breakdown = isset($this->subIDBreakdown[$revenue_tracker_id]['s3']) ? $this->subIDBreakdown[$revenue_tracker_id]['s3'] : 0;
        $s4Breakdown = isset($this->subIDBreakdown[$revenue_tracker_id]['s4']) ? $this->subIDBreakdown[$revenue_tracker_id]['s4'] : 0;
        // $s5Breakdown = $this->subIDBreakdown[$revenue_tracker_id]['s5'];

        //Clicks/Conversions
        $rt_data = [];
        $rowStart = 1;
        $rowLimit = 2500;
        $totalRows = 0;

        while (true) {
            $cakeurl = $cakeBaseConversionsURL;
            $cakeurl = str_replace('start_at_row=0', 'start_at_row='.$rowStart, $cakeurl);
            $cakeurl = str_replace('row_limit=0', "row_limit=$rowLimit", $cakeurl);
            $cakeurl = str_replace('campaign_id=0', "campaign_id=$campaign_id", $cakeurl);
            $cakeurl .= "&source_affiliate_id=$affiliate_id&start_date=$this->dateFromStr&end_date=$this->dateToStr";

            $response = $this->getCurlResponse($cakeurl);
            $totalRows = $response['row_count'];
            if ($totalRows == 0) {
                break;
            } else {
                $x = $rowStart - 1;
                $currentRows = $totalRows - $x;
                if ($currentRows <= 1) {
                    $conversions[] = $response['event_conversions']['event_conversion'];
                } else {
                    $conversions = $response['event_conversions']['event_conversion'];
                }
                foreach ($conversions as $conv) {

                    $s1 = ! is_array($conv['sub_id_1']) && $allowBreakdown && $s1Breakdown ? $conv['sub_id_1'] : '';
                    $s2 = ! is_array($conv['sub_id_2']) && $allowBreakdown && $s2Breakdown ? $conv['sub_id_2'] : '';
                    $s3 = ! is_array($conv['sub_id_3']) && $allowBreakdown && $s3Breakdown ? $conv['sub_id_3'] : '';
                    $s4 = ! is_array($conv['sub_id_4']) && $allowBreakdown && $s4Breakdown ? $conv['sub_id_4'] : '';
                    // $s5 = !is_array($conv['sub_id_5']) ? $conv['sub_id_5'] : '';
                    $s5 = '';

                    if (! isset($rt_data[$s1][$s2][$s3][$s4][$s5])) {
                        $rt_data[$s1][$s2][$s3][$s4][$s5] = [
                            'click' => 0,
                            'payout' => 0,
                        ];
                    }

                    //Count Click, Revenue & Payout
                    $rt_data[$s1][$s2][$s3][$s4][$s5]['click'] += 1;
                    $rt_data[$s1][$s2][$s3][$s4][$s5]['payout'] += isset($conv['paid']['amount']) ? $conv['paid']['amount'] : 0;
                }
            }

            $rowStart += $rowLimit;

            if ($rowStart > $totalRows) {
                break;
            }
        }

        $rows = [];

        //Prepare
        foreach ($rt_data as $s1 => $s1d) {
            foreach ($s1d as $s2 => $s2d) {
                foreach ($s2d as $s3 => $s3d) {
                    foreach ($s3d as $s4 => $s4d) {
                        foreach ($s4d as $s5 => $data) {
                            $rows[] = [
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
                                'clicks' => $data['click'],
                                'payout' => $data['payout'],
                            ];
                        }
                    }
                }
            }
        }

        $chunks = array_chunk($rows, 1000);
        foreach ($chunks as $chunk) {
            try {
                DB::table('revenue_tracker_cake_statistics')->insert($chunk);
                //Log::info('Rev Tracker Cake Stats Chunks total: ' . count($chunk));
            } catch (QueryException $e) {
                Log::info('Rev Tracker Cake Stats Saving Error!');
                Log::info($e->getMessage());

                $this->errors[] = [
                    'Area' => 'Rev Tracker Cake Stats',
                    'Info' => 'DB Saving',
                    'MSG' => $e->getMessage(),
                ];
            }
        }

        //Prepop
        $rt_data = [];
        $rowStart = 1;
        $rowLimit = 2500;
        $totalRows = 0;

        while (true) {
            $cakeurl = $cakeBaseClicksURL;
            $cakeurl = str_replace('offer_id=0', "offer_id=$offer_id", $cakeurl);
            $cakeurl = str_replace('start_at_row=0', 'start_at_row='.$rowStart, $cakeurl);
            $cakeurl = str_replace('row_limit=0', "row_limit=$rowLimit", $cakeurl);
            $cakeurl .= "&affiliate_id=$affiliate_id&campaign_id=$campaign_id&start_date=$this->dateFromStr&end_date=$this->dateToStr&include_duplicates=true";

            $response = $this->getCurlResponse($cakeurl);
            $totalRows = $response['row_count'];

            if ($totalRows == 0) {
                break;
            } else {
                if ($totalRows <= 1) {
                    $clicks[] = $response['clicks']['click'];
                } else {
                    $clicks = $response['clicks']['click'];
                }
                foreach ($clicks as $click) {
                    $s1 = ! is_array($click['sub_id_1']) && $allowBreakdown && $s1Breakdown ? $click['sub_id_1'] : '';
                    $s2 = ! is_array($click['sub_id_2']) && $allowBreakdown && $s2Breakdown ? $click['sub_id_2'] : '';
                    $s3 = ! is_array($click['sub_id_3']) && $allowBreakdown && $s3Breakdown ? $click['sub_id_3'] : '';
                    $s4 = ! is_array($click['sub_id_4']) && $allowBreakdown && $s4Breakdown ? $click['sub_id_4'] : '';
                    // $s5 = !is_array($click['sub_id_5']) ? $click['sub_id_5'] : '';
                    $s5 = '';

                    //Click Log Tracing
                    if ($this->clickLogAllAffiliates || in_array($revenue_tracker_id, $this->clickLogRevTrackers)) {
                        $requestURL = $click['request_url'];
                        $parts = parse_url($requestURL, PHP_URL_QUERY);
                        $queryParams = [];
                        parse_str($parts, $queryParams);

                        $email = isset($queryParams['email']) ? urldecode($queryParams['email']) : '';

                        if ($email != '' && ! in_array($email, $this->clickLogEmails)) {
                            $this->clickLogEmails[] = $email;
                        }

                        $this->clickLogs[] = [
                            'click_date' => Carbon::parse($click['click_date'])->toDateTimeString(),
                            'click_id' => $click['click_id'],
                            'email' => $email,
                            'affiliate_id' => $affiliate_id,
                            'revenue_tracker_id' => $revenue_tracker_id,
                            'ip' => $click['ip_address'],
                        ];
                    }

                    if (! isset($rt_data[$s1][$s2][$s3][$s4][$s5])) {
                        $rt_data[$s1][$s2][$s3][$s4][$s5] = [
                            'click' => 0,
                            'uclick' => 0,
                            'payout' => 0,
                            'no_prepop_count' => 0,
                            'prepop_count' => 0,
                            'prepop_werrors_count' => 0,
                        ];
                    }

                    //Count Click, Revenue & Payout
                    $rt_data[$s1][$s2][$s3][$s4][$s5]['click'] += 1;
                    $rt_data[$s1][$s2][$s3][$s4][$s5]['payout'] += isset($click['paid']['amount']) ? $click['paid']['amount'] : 0;
                    if ($click['duplicate'] == 'false' || $click === false) {
                        $rt_data[$s1][$s2][$s3][$s4][$s5]['uclick'] += 1;

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
                    } else {
                        // Log::info('Duplicate');
                        // Log::info($click);
                    }
                }
            }

            $rowStart += $rowLimit;
            if ($rowStart > $totalRows) {
                break;
            }
        }

        $rows = [];
        //Prep
        foreach ($rt_data as $s1 => $s1d) {
            foreach ($s1d as $s2 => $s2d) {
                foreach ($s2d as $s3 => $s3d) {
                    foreach ($s3d as $s4 => $s4d) {
                        foreach ($s4d as $s5 => $data) {
                            if ($data['uclick'] > 0) {
                                //PrePop Statistics
                                $npp = $data['no_prepop_count'] == 0 && $data['uclick'] == 0 ? 0 : (floatval($data['no_prepop_count']) / floatval($data['uclick']));
                                $pwep = $data['prepop_werrors_count'] == 0 && $data['uclick'] == 0 ? 0 : (floatval($data['prepop_werrors_count']) / floatval($data['uclick']));

                                $rows[] = [
                                    'affiliate_id' => $affiliate_id,
                                    'revenue_tracker_id' => $revenue_tracker_id,
                                    'created_at' => $this->dateFromStr,
                                    's1' => $s1,
                                    's2' => $s2,
                                    's3' => $s3,
                                    's4' => $s4,
                                    's5' => $s5,
                                    'total_clicks' => $data['click'],
                                    'prepop_count' => $data['prepop_count'],
                                    'no_prepop_count' => $data['no_prepop_count'],
                                    'prepop_with_errors_count' => $data['prepop_werrors_count'],
                                    'no_prepop_percentage' => $npp,
                                    'prepop_with_errors_percentage' => $pwep,
                                ];
                            }
                        }
                    }
                }
            }
        }

        $chunks = array_chunk($rows, 1000);
        foreach ($chunks as $chunk) {
            try {
                DB::table('prepop_statistics')->insert($chunk);
                //Log::info('Pre Pop Stats Chunks total: ' . count($chunk));
            } catch (QueryException $e) {
                Log::info('Pre Pop Stats Saving Error!');
                Log::info($e->getMessage());

                $this->errors[] = [
                    'Area' => 'Pre Pop Stats',
                    'Info' => 'DB Saving',
                    'MSG' => $e->getMessage(),
                ];
            }
        }
    }

    public function getRevenueTrackerCPMPayout($revenue_tracker)
    {
        $cakeBaseViewsURL = config('constants.CAKE_VIEWS_REPORTS_API_V1');
        $cakeBaseViewsURL2 = config('constants.CAKE_VIEWS_REPORTS_API_V2');
        $affiliate_id = $revenue_tracker['affiliate_id'];
        $campaign_id = $revenue_tracker['campaign_id'];
        $offer_id = $revenue_tracker['offer_id'];
        $revenue_tracker_id = $revenue_tracker['revenue_tracker_id'];
        $type = $revenue_tracker['type'];
        $allowBreakdown = isset($this->subIDBreakdown[$revenue_tracker_id]['status']) ? $this->subIDBreakdown[$revenue_tracker_id]['status'] : 0;
        $s1Breakdown = isset($this->subIDBreakdown[$revenue_tracker_id]['s1']) ? $this->subIDBreakdown[$revenue_tracker_id]['s1'] : 0;

        //CLICKS/VIEWs
        $rt_data = [];
        $rows = [];

        if ($allowBreakdown) {
            $cakeurl = $cakeBaseViewsURL;
            $cakeurl .= "&source_affiliate_id=$affiliate_id&site_offer_id=$offer_id&start_date=$this->dateFromStr&end_date=$this->dateToStr";
            $response = $this->getCurlResponse($cakeurl);
            $totalRows = $response['row_count'];
            if ($totalRows <= 1) {
                $views[] = $response['sub_ids']['sub_id_summary'];
            } else {
                $views = $response['sub_ids']['sub_id_summary'];
            }

            foreach ($views as $view) {
                $s1 = ! is_array($view['sub_id_name']) && $allowBreakdown && $s1Breakdown ? $view['sub_id_name'] : '';

                if (! isset($rt_data[$s1])) {
                    $rt_data[$s1] = [
                        'views' => 0,
                        'payout' => 0,
                    ];
                }
                //Count Click, Revenue & Payout
                $rt_data[$s1]['views'] += $view['views'];
                $rt_data[$s1]['payout'] += $view['cost'];
            }

            //Prepare
            foreach ($rt_data as $s1 => $data) {
                $rows[] = [
                    'affiliate_id' => $affiliate_id,
                    'revenue_tracker_id' => $revenue_tracker_id,
                    'cake_campaign_id' => $campaign_id,
                    'type' => 1,
                    'created_at' => $this->dateFromStr,
                    's1' => $s1,
                    's2' => '',
                    's3' => '',
                    's4' => '',
                    's5' => '',
                    'clicks' => $data['views'],
                    'payout' => $data['payout'],
                ];
            }
        } else {
            $cakeurl = $cakeBaseViewsURL2;
            $cakeurl .= "&source_affiliate_id=$affiliate_id&campaign_id=$campaign_id&start_date=$this->dateFromStr&end_date=$this->dateToStr";
            $response = $this->getCurlResponse($cakeurl);
            $totalRows = $response['row_count'];
            if ($totalRows <= 1) {
                $views[] = $response['campaigns']['campaign_summary'];
            } else {
                $views = $response['campaigns']['campaign_summary'];
            }

            foreach ($views as $view) {
                $rows[] = [
                    'affiliate_id' => $affiliate_id,
                    'revenue_tracker_id' => $revenue_tracker_id,
                    'cake_campaign_id' => $campaign_id,
                    'type' => 1,
                    'created_at' => $this->dateFromStr,
                    's1' => '',
                    's2' => '',
                    's3' => '',
                    's4' => '',
                    's5' => '',
                    'clicks' => $view['views'],
                    'payout' => $view['cost'],
                ];
            }
        }

        $chunks = array_chunk($rows, 1000);
        foreach ($chunks as $chunk) {
            try {
                DB::table('revenue_tracker_cake_statistics')->insert($chunk);
                //Log::info('Rev Tracker Cake Stats Chunks total: ' . count($chunk));
            } catch (QueryException $e) {
                Log::info('Rev Tracker Cake Stats Saving Error!');
                Log::info($e->getMessage());

                $this->errors[] = [
                    'Area' => 'Rev Tracker Cake Stats',
                    'Info' => 'DB Saving',
                    'MSG' => $e->getMessage(),
                ];
            }
        }
    }

    public function getRevTrackerCakeRevenue($affiliate_id, $revenue_tracker_id)
    {
        $allowBreakdown = isset($this->subIDBreakdown[$revenue_tracker_id]['status']) ? $this->subIDBreakdown[$revenue_tracker_id]['status'] : 0;
        $s1Breakdown = isset($this->subIDBreakdown[$revenue_tracker_id]['s1']) ? $this->subIDBreakdown[$revenue_tracker_id]['s1'] : 0;
        $s2Breakdown = isset($this->subIDBreakdown[$revenue_tracker_id]['s2']) ? $this->subIDBreakdown[$revenue_tracker_id]['s2'] : 0;
        $s3Breakdown = isset($this->subIDBreakdown[$revenue_tracker_id]['s3']) ? $this->subIDBreakdown[$revenue_tracker_id]['s3'] : 0;
        $s4Breakdown = isset($this->subIDBreakdown[$revenue_tracker_id]['s4']) ? $this->subIDBreakdown[$revenue_tracker_id]['s4'] : 0;
        // $s5Breakdown = $this->subIDBreakdown[$revenue_tracker_id]['s5'];
        $cakeBaseClicksURL = config('constants.CAKE_CLICKS_REPORTS_API_V12_UNIQUE');
        $cakeBaseConversionsURL = config('constants.CAKE_CONVERSIONS_API_V15');
        $cakeLRID = env('CPA_WALL_ENGAGEIQ_CAMPAIGN_ID', 94);
        $rt_data = [];
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

            $response = $this->getCakeXML($cakeurl, $revenue_tracker_id);

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
                    $s1 = ! is_array($click['sub_id_1']) && $allowBreakdown && $s1Breakdown ? $click['sub_id_1'] : '';
                    $s2 = ! is_array($click['sub_id_2']) && $allowBreakdown && $s2Breakdown ? $click['sub_id_2'] : '';
                    $s3 = ! is_array($click['sub_id_3']) && $allowBreakdown && $s3Breakdown ? $click['sub_id_3'] : '';
                    $s4 = ! is_array($click['sub_id_4']) && $allowBreakdown && $s4Breakdown ? $click['sub_id_4'] : '';
                    // $s5 = !is_array($click['sub_id_5']) ? $click['sub_id_5'] : '';
                    $s5 = '';

                    if (! isset($rt_data[$s1][$s2][$s3][$s4][$s5])) {
                        $rt_data[$s1][$s2][$s3][$s4][$s5] = 0;
                    }

                    $rt_data[$s1][$s2][$s3][$s4][$s5] += isset($click['received']['amount']) ? $click['received']['amount'] : 0;

                    //Cake Revenue Report
                    // Log::info($click);
                    $offer_id = $click['site_offer']['site_offer_id'];
                    if (! isset($cr_data[$s1][$s2][$s3][$s4][$s5][$offer_id])) {
                        $cr_data[$s1][$s2][$s3][$s4][$s5][$offer_id] = 0;
                    }
                    $cr_data[$s1][$s2][$s3][$s4][$s5][$offer_id] += isset($click['received']['amount']) ? $click['received']['amount'] : 0;
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

        while (true) {
            $cakeurl = $cakeBaseConversionsURL;
            $cakeurl = str_replace('start_at_row=0', 'start_at_row='.$rowStart, $cakeurl);
            $cakeurl = str_replace('row_limit=0', "row_limit=$rowLimit", $cakeurl);
            $cakeurl .= "&source_affiliate_id=$revenue_tracker_id&start_date=$this->dateFromStr&end_date=$this->dateToStr";

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

                    $s1 = ! is_array($conv['sub_id_1']) && $allowBreakdown && $s1Breakdown ? $conv['sub_id_1'] : '';
                    $s2 = ! is_array($conv['sub_id_2']) && $allowBreakdown && $s2Breakdown ? $conv['sub_id_2'] : '';
                    $s3 = ! is_array($conv['sub_id_3']) && $allowBreakdown && $s3Breakdown ? $conv['sub_id_3'] : '';
                    $s4 = ! is_array($conv['sub_id_4']) && $allowBreakdown && $s4Breakdown ? $conv['sub_id_4'] : '';
                    // $s5 = !is_array($conv['sub_id_5']) ? $conv['sub_id_5'] : '';
                    $s5 = '';

                    if (! isset($rt_data[$s1][$s2][$s3][$s4][$s5])) {
                        $rt_data[$s1][$s2][$s3][$s4][$s5] = 0;
                    }

                    $rt_data[$s1][$s2][$s3][$s4][$s5] += isset($conv['received']['amount']) ? $conv['received']['amount'] : 0;

                    //Cake Revenue Report
                    // Log::info($click);
                    $offer_id = $conv['site_offer']['site_offer_id'];
                    if (! isset($cr_data[$s1][$s2][$s3][$s4][$s5][$offer_id])) {
                        $cr_data[$s1][$s2][$s3][$s4][$s5][$offer_id] = 0;
                    }
                    $cr_data[$s1][$s2][$s3][$s4][$s5][$offer_id] += isset($conv['received']['amount']) ? $conv['received']['amount'] : 0;
                }
            }

            $rowStart += $rowLimit;

            if ($rowStart > $totalRows) {
                break;
            }
        }

        $rows = [];
        $rows_cr = [];

        $this->revenues[$affiliate_id][$revenue_tracker_id] = $rt_data;

        foreach ($rt_data as $s1 => $s1d) {
            foreach ($s1d as $s2 => $s2d) {
                foreach ($s2d as $s3 => $s3d) {
                    foreach ($s3d as $s4 => $s4d) {
                        foreach ($s4d as $s5 => $revenue) {
                            $rows[] = [
                                'affiliate_id' => $affiliate_id,
                                'revenue_tracker_id' => $revenue_tracker_id,
                                'campaign_id' => $cakeLRID,
                                'created_at' => $this->dateFromStr,
                                's1' => $s1,
                                's2' => $s2,
                                's3' => $s3,
                                's4' => $s4,
                                's5' => $s5,
                                'revenue' => $revenue,
                                'lead_count' => 0,
                                'reject_count' => 0,
                                'failed_count' => 0,
                            ];

                            //Cake Revenue
                            foreach ($cr_data[$s1][$s2][$s3][$s4][$s5] as $offer_id => $revenue) {
                                $rows_cr[] = [
                                    'affiliate_id' => $affiliate_id,
                                    'revenue_tracker_id' => $revenue_tracker_id,
                                    'created_at' => $this->dateFromStr,
                                    's1' => $s1,
                                    's2' => $s2,
                                    's3' => $s3,
                                    's4' => $s4,
                                    's5' => $s5,
                                    'offer_id' => $offer_id,
                                    'revenue' => $revenue,
                                ];
                                // $cakeRevenue = CakeRevenue::firstOrNew([
                                //     'affiliate_id' => $affiliate_id,
                                //     'revenue_tracker_id' => $revenue_tracker_id,
                                //     'created_at' => $this->dateFromStr,
                                //     's1' => $s1,
                                //     's2' => $s2,
                                //     's3' => $s3,
                                //     's4' => $s4,
                                //     's5' => $s5,
                                //     'offer_id' => $offer_id,
                                // ]);

                                // $cakeRevenue->revenue = $revenue;
                                // $cakeRevenue->save();
                            }
                        }
                    }
                }
            }
        }

        // Save in Affiliate Reports
        $chunks = array_chunk($rows, 1000);
        foreach ($chunks as $chunk) {
            try {
                //Save Revenue from Affiliate Report
                DB::table('affiliate_reports')->insert($chunk);
                //Log::info('Rev Tracker Revenue Chunks total: ' . count($chunk));
            } catch (QueryException $e) {
                Log::info('Rev Tracker Revenue Saving Error!');
                Log::info($e->getMessage());

                $this->errors[] = [
                    'Area' => 'Rev Tracker Revenue',
                    'Info' => 'DB Saving',
                    'MSG' => $e->getMessage(),
                ];
            }
        }

        // Save in Cake Revenues
        $chunks_cr = array_chunk($rows_cr, 1000);
        foreach ($chunks_cr as $chunk) {
            try {
                //Save Revenue from Affiliate Report
                DB::table('cake_revenues')->insert($chunk);
                //Log::info('Cake Revenue Chunks total: ' . count($chunk));
            } catch (QueryException $e) {
                Log::info('Cake Revenue Saving Error!');
                Log::info($e->getMessage());

                $this->errors[] = [
                    'Area' => 'Cake Revenue',
                    'Info' => 'DB Saving',
                    'MSG' => $e->getMessage(),
                ];
            }
        }
    }

    public function getRegistrationReport()
    {
        $start = Carbon::now();
        $registration_report = LeadUser::whereBetween('created_at', [$this->datedateFromStr.' 00:00:00', $this->datedateFromStr.' 23:59:59'])
            ->selectRAW('affiliate_id,revenue_tracker_id, s1, s2, s3, s4, s5, COUNT(*) as registrations')
            ->groupBy(['affiliate_id', 'revenue_tracker_id', 's1', 's2', 's3', 's4', 's5'])
            ->get();

        foreach ($registration_report as $report) {
            // $this->registrations[$report->affiliate_id][$report->revenue_tracker_id][$report->s1][$report->s2][$report->s3][$report->s4][$report->s5] = $report->registrations;
            $this->registrations[$report->affiliate_id][$report->revenue_tracker_id][$report->s1][$report->s2][$report->s3][$report->s4][''] = $report->registrations;
        }
        $this->timeTracker('NLR Registrations', $start, Carbon::now());
    }

    public function getRevTrackerLeadRevenue($affiliate_id = null, $rev_tracker = null)
    {
        $start = Carbon::now();
        // \DB::connection('secondary')->enableQueryLog();
        $leads = Lead::leftJoin('affiliate_revenue_trackers', 'affiliate_revenue_trackers.revenue_tracker_id', '=', 'leads.affiliate_id');

        if ($affiliate_id != null) {
            $leads->where('affiliate_revenue_trackers.affiliate_id', $affiliate_id);
        }
        if ($rev_tracker != null) {
            $leads->where('leads.affiliate_id', $rev_tracker);
        }

        $leads = $leads->where(DB::RAW('date(leads.created_at)'), $this->dateFromStr)->whereIn('lead_status', [0, 1, 2])
            ->selectRaw('leads.campaign_id, affiliate_revenue_trackers.affiliate_id, leads.affiliate_id as revenue_tracker_id, leads.s1,leads.s2,leads.s3,leads.s4,leads.s5, lead_status, COUNT(*) as count, SUM(received) as revenue')
        // ->groupBy(['leads.campaign_id', 'affiliate_revenue_trackers.affiliate_id', 'leads.affiliate_id', 'leads.s1','leads.s2','leads.s3','leads.s4','leads.s5', 'lead_status'])
            ->groupBy(['leads.campaign_id', 'affiliate_revenue_trackers.affiliate_id', 'leads.affiliate_id', 'leads.s1', 'leads.s2', 'leads.s3', 'leads.s4', 'lead_status'])
            ->get();
        // \Log::info(\DB::connection('secondary')->getQueryLog());
        // \Log::info($leads);
        $rt_data = [];
        foreach ($leads as $l) {
            $campaign = $l->campaign_id;
            $affiliate = $l->affiliate_id;
            $rev_tracker = $l->revenue_tracker_id;
            $allowBreakdown = isset($this->subIDBreakdown[$rev_tracker]['status']) ? $this->subIDBreakdown[$rev_tracker]['status'] : 0;
            $s1Breakdown = isset($this->subIDBreakdown[$rev_tracker]['s1']) ? $this->subIDBreakdown[$rev_tracker]['s1'] : 0;
            $s2Breakdown = isset($this->subIDBreakdown[$rev_tracker]['s2']) ? $this->subIDBreakdown[$rev_tracker]['s2'] : 0;
            $s3Breakdown = isset($this->subIDBreakdown[$rev_tracker]['s3']) ? $this->subIDBreakdown[$rev_tracker]['s3'] : 0;
            $s4Breakdown = isset($this->subIDBreakdown[$rev_tracker]['s4']) ? $this->subIDBreakdown[$rev_tracker]['s4'] : 0;
            // $s5Breakdown = $this->subIDBreakdown[$rev_tracker]['s5'];

            if ($allowBreakdown == 0) {
                $s1 = '';
                $s2 = '';
                $s3 = '';
                $s4 = '';
                $s5 = '';
            } else {
                $s1 = $s1Breakdown ? $l->s1 : '';
                $s2 = $s2Breakdown ? $l->s2 : '';
                $s3 = $s3Breakdown ? $l->s3 : '';
                $s4 = $s4Breakdown ? $l->s4 : '';
                // $s5 = $l->s5;
                $s5 = '';
            }

            if ($affiliate != null || $affiliate != '') { //Affiliate Reports ONLY
                if (! isset($rt_data[$campaign][$affiliate][$rev_tracker][$s1][$s2][$s3][$s4][$s5])) {
                    $rt_data[$campaign][$affiliate][$rev_tracker][$s1][$s2][$s3][$s4][$s5] = [
                        'lead_count' => 0,
                        'reject_count' => 0,
                        'failed_count' => 0,
                        'revenue' => 0,
                    ];
                }

                if ($l->lead_status == 0) { //failed
                    $rt_data[$campaign][$affiliate][$rev_tracker][$s1][$s2][$s3][$s4][$s5]['failed_count'] = $l->count;
                } elseif ($l->lead_status == 1) { //success
                    $rt_data[$campaign][$affiliate][$rev_tracker][$s1][$s2][$s3][$s4][$s5]['lead_count'] += $l->count;
                    $rt_data[$campaign][$affiliate][$rev_tracker][$s1][$s2][$s3][$s4][$s5]['revenue'] += $l->revenue;
                } elseif ($l->lead_status == 2) { //rejected
                    $rt_data[$campaign][$affiliate][$rev_tracker][$s1][$s2][$s3][$s4][$s5]['reject_count'] += $l->count;
                }
            }
        }
        // \Log::info($rt_data);
        // Log::info('RT_DATE COUNT: '. count($rt_data));
        $rows = [];

        foreach ($rt_data as $campaign_id => $cdata) {
            foreach ($cdata as $affiliate_id => $adata) {
                foreach ($adata as $revenue_tracker_id => $rdata) {
                    foreach ($rdata as $s1 => $s1d) {
                        foreach ($s1d as $s2 => $s2d) {
                            foreach ($s2d as $s3 => $s3d) {
                                foreach ($s3d as $s4 => $s4d) {
                                    foreach ($s4d as $s5 => $data) {
                                        $rows[] = [
                                            'affiliate_id' => $affiliate_id,
                                            'revenue_tracker_id' => $revenue_tracker_id,
                                            'campaign_id' => $campaign_id,
                                            'created_at' => $this->dateFromStr,
                                            's1' => $s1,
                                            's2' => $s2,
                                            's3' => $s3,
                                            's4' => $s4,
                                            's5' => $s5,
                                            'revenue' => $data['revenue'],
                                            'lead_count' => $data['lead_count'],
                                            'reject_count' => $data['reject_count'],
                                            'failed_count' => $data['failed_count'],
                                        ];
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        // \Log::info($rows);
        // exit;
        // Log::info('ROWS COUNT: '. count($rows));

        $chunks = array_chunk($rows, 1000);
        foreach ($chunks as $chunk) {
            try {
                DB::table('affiliate_reports')->insert($chunk);
                Log::info('Rev Tracker Lead Revenue Chunks total: '.count($chunk));
            } catch (QueryException $e) {
                Log::info('Rev Tracker Lead Revenue!');
                Log::info($e->getMessage());

                $this->errors[] = [
                    'Area' => 'Lead Revenue',
                    'Info' => 'DB Saving',
                    'MSG' => $e->getMessage(),
                ];
            }
        }

        $this->timeTracker('NLR Lead Revenue', $start, Carbon::now());
    }

    public function getAffluentRevenue()
    {
        $start = Carbon::now();
        $affluent_lr_id = env('CPA_WALL_AFFLUENT_CAMPAIGN_ID', 96);
        $clicks_url = config('constants.AFFLUENT_CLICKS_REPORT_API_V11');
        $clicks_url .= "&start_date=$this->dateFromStr&end_date=$this->dateToStr";
        $conversions_url = config('constants.AFFLUENT_CONVERSIONS_REPORT_API_V4');
        $conversions_url .= "&start_date=$this->dateFromStr&end_date=$this->dateToStr";
        $data = [];
        $rev_trackers = [];
        $clicks_response = $this->getCurlResponse($clicks_url);
        $clicks_total = $clicks_response['row_count'];

        if ($clicks_total > 0) {
            if ($clicks_total == 1) {
                $clicks[] = $clicks_response['clicks']['click'];
            } else {
                $clicks = $clicks_response['clicks']['click'];
            }
            foreach ($clicks as $click) {
                $rev_tracker = ! is_array($click['subid_1']) ? $click['subid_1'] : '';
                $revenue_tracker_id = str_replace('CD', '', $rev_tracker);
                $allowBreakdown = isset($this->subIDBreakdown[$revenue_tracker_id]['status']) ? $this->subIDBreakdown[$revenue_tracker_id]['status'] : 0;
                $s1Breakdown = isset($this->subIDBreakdown[$revenue_tracker_id]['s1']) ? $this->subIDBreakdown[$revenue_tracker_id]['s1'] : 0;
                $s2Breakdown = isset($this->subIDBreakdown[$revenue_tracker_id]['s2']) ? $this->subIDBreakdown[$revenue_tracker_id]['s2'] : 0;
                $s3Breakdown = isset($this->subIDBreakdown[$revenue_tracker_id]['s3']) ? $this->subIDBreakdown[$revenue_tracker_id]['s3'] : 0;
                $s4Breakdown = isset($this->subIDBreakdown[$revenue_tracker_id]['s4']) ? $this->subIDBreakdown[$revenue_tracker_id]['s4'] : 0;
                // $s5Breakdown = $this->subIDBreakdown[$revenue_tracker_id]['s5'];

                if ($allowBreakdown == 0) {
                    $s1 = '';
                    $s2 = '';
                    $s3 = '';
                    $s4 = '';
                    $s5 = '';
                } else {
                    $s1s2 = ! is_array($click['subid_2']) ? $click['subid_2'] : '';
                    $s3 = ! is_array($click['subid_3']) && $s3Breakdown ? $click['subid_3'] : '';
                    $s4 = ! is_array($click['subid_4']) && $s4Breakdown ? $click['subid_4'] : '';
                    // $s5 = !is_array($click['subid_5']) ? $click['subid_5'] : '';
                    $s5 = '';
                    $s12 = explode('__', $s1s2);
                    $s1 = isset($s12[0]) && $s1Breakdown ? $s12[0] : '';
                    $s2 = isset($s12[1]) && $s2Breakdown ? $s12[1] : '';
                }

                if (! isset($data[$revenue_tracker_id][$s1][$s2][$s3][$s4][$s5])) {
                    $data[$revenue_tracker_id][$s1][$s2][$s3][$s4][$s5] = 0;
                    $rev_trackers[] = $revenue_tracker_id;
                }

                $data[$revenue_tracker_id][$s1][$s2][$s3][$s4][$s5] += isset($click['price']) && ! is_array($click['price']) ? $click['price'] : 0;
            }
        }

        $conversions_response = $this->getCurlResponse($conversions_url);
        $conversions_total = $conversions_response['row_count'];

        if ($conversions_total > 0) {
            if ($conversions_total == 1) {
                $conversions[] = $conversions_response['conversions']['conversion'];
            } else {
                $conversions = $conversions_response['conversions']['conversion'];
            }
            foreach ($conversions as $conversion) {
                $rev_tracker = ! is_array($conversion['subid_1']) ? $conversion['subid_1'] : '';
                $revenue_tracker_id = str_replace('CD', '', $rev_tracker);
                $allowBreakdown = isset($this->subIDBreakdown[$revenue_tracker_id]['status']) ? $this->subIDBreakdown[$revenue_tracker_id]['status'] : 0;
                $s1Breakdown = isset($this->subIDBreakdown[$revenue_tracker_id]['s1']) ? $this->subIDBreakdown[$revenue_tracker_id]['s1'] : 0;
                $s2Breakdown = isset($this->subIDBreakdown[$revenue_tracker_id]['s2']) ? $this->subIDBreakdown[$revenue_tracker_id]['s2'] : 0;
                $s3Breakdown = isset($this->subIDBreakdown[$revenue_tracker_id]['s3']) ? $this->subIDBreakdown[$revenue_tracker_id]['s3'] : 0;
                $s4Breakdown = isset($this->subIDBreakdown[$revenue_tracker_id]['s4']) ? $this->subIDBreakdown[$revenue_tracker_id]['s4'] : 0;
                // $s5Breakdown = $this->subIDBreakdown[$revenue_tracker_id]['s5'];

                if ($allowBreakdown == 0) {
                    $s1 = '';
                    $s2 = '';
                    $s3 = '';
                    $s4 = '';
                    $s5 = '';
                } else {
                    $s1s2 = ! is_array($conversion['subid_2']) ? $conversion['subid_2'] : '';
                    $s3 = ! is_array($conversion['subid_3']) && $s3Breakdown ? $conversion['subid_3'] : '';
                    $s4 = ! is_array($conversion['subid_4']) && $s4Breakdown ? $conversion['subid_4'] : '';
                    // $s5 = !is_array($conversion['subid_5']) ? $conversion['subid_5'] : '';
                    $s5 = '';
                    $s12 = explode('__', $s1s2);
                    $s1 = isset($s12[0]) && $s1Breakdown ? $s12[0] : '';
                    $s2 = isset($s12[1]) && $s2Breakdown ? $s12[1] : '';
                }

                if (! isset($data[$revenue_tracker_id][$s1][$s2][$s3][$s4][$s5])) {
                    $data[$revenue_tracker_id][$s1][$s2][$s3][$s4][$s5] = 0;
                    $rev_trackers[] = $revenue_tracker_id;
                }

                $data[$revenue_tracker_id][$s1][$s2][$s3][$s4][$s5] += isset($conversion['price']) && ! is_array($conversion['price']) ? $conversion['price'] : 0;
            }
        }

        $rows = [];
        $affiliates = $this->affiliates;
        foreach ($data as $revenue_tracker_id => $rt_data) {
            foreach ($rt_data as $s1 => $s1d) {
                foreach ($s1d as $s2 => $s2d) {
                    foreach ($s2d as $s3 => $s3d) {
                        foreach ($s3d as $s4 => $s4d) {
                            foreach ($s4d as $s5 => $revenue) {
                                if (isset($affiliates[$revenue_tracker_id])) {
                                    $affiliate_id = $affiliates[$revenue_tracker_id];
                                    $rows[] = [
                                        'affiliate_id' => $affiliate_id,
                                        'revenue_tracker_id' => $revenue_tracker_id,
                                        'campaign_id' => $affluent_lr_id,
                                        'created_at' => $this->dateFromStr,
                                        's1' => $s1,
                                        's2' => $s2,
                                        's3' => $s3,
                                        's4' => $s4,
                                        's5' => $s5,
                                        'revenue' => $revenue,
                                        'lead_count' => 0,
                                        'reject_count' => 0,
                                        'failed_count' => 0,
                                    ];
                                }
                            }
                        }
                    }
                }
            }
        }

        $chunks = array_chunk($rows, 1000);
        foreach ($chunks as $chunk) {
            try {
                DB::table('affiliate_reports')->insert($chunk);
                //Log::info('Affluent Revenue Chunks total: ' . count($chunk));
            } catch (QueryException $e) {
                Log::info('Affluent Revenue!');
                Log::info($e->getMessage());

                $this->errors[] = [
                    'Area' => 'Affluent Revenue',
                    'Info' => 'DB Saving',
                    'MSG' => $e->getMessage(),
                ];
            }
        }
        $this->timeTracker('Affluent', $start, Carbon::now());
    }

    public function getSBGRevenue()
    {
        $start = Carbon::now();
        $sbg_lr_id = env('CPA_WALL_SBG_CAMPAIGN_ID', 95);
        $clicks_url = config('constants.SBG_CLICKS_REPORT_API_V11');
        $clicks_url .= "&start_date=$this->dateFromStr&end_date=$this->dateToStr";
        $conversions_url = config('constants.SBG_CONVERSIONS_REPORT_API_V4');
        $conversions_url .= "&start_date=$this->dateFromStr&end_date=$this->dateToStr";

        $data = [];
        $rev_trackers = [];
        $click_data = $this->getCurlResponse($clicks_url);

        if (isset($click_data['clicks']['click'])) {
            if ($click_data['row_count'] <= 1) {
                $clicks[] = $click_data['clicks']['click'];
            } else {
                $clicks = $click_data['clicks']['click'];
            }
            foreach ($clicks as $click) {
                $rev_tracker = ! is_array($click['subid_1']) ? $click['subid_1'] : '';
                $revenue_tracker_id = str_replace('CD', '', $rev_tracker);
                $revenue_tracker_id = $revenue_tracker_id == '' ? 1 : $revenue_tracker_id;
                $allowBreakdown = isset($this->subIDBreakdown[$revenue_tracker_id]['status']) ? $this->subIDBreakdown[$revenue_tracker_id]['status'] : 0;
                $s1Breakdown = isset($this->subIDBreakdown[$revenue_tracker_id]['s1']) ? $this->subIDBreakdown[$revenue_tracker_id]['s1'] : 0;
                $s2Breakdown = isset($this->subIDBreakdown[$revenue_tracker_id]['s2']) ? $this->subIDBreakdown[$revenue_tracker_id]['s2'] : 0;
                $s3Breakdown = isset($this->subIDBreakdown[$revenue_tracker_id]['s3']) ? $this->subIDBreakdown[$revenue_tracker_id]['s3'] : 0;
                $s4Breakdown = isset($this->subIDBreakdown[$revenue_tracker_id]['s4']) ? $this->subIDBreakdown[$revenue_tracker_id]['s4'] : 0;
                // $s5Breakdown = $this->subIDBreakdown[$revenue_tracker_id]['s5'];

                if ($allowBreakdown == 0) {
                    $s1 = '';
                    $s2 = '';
                    $s3 = '';
                    $s4 = '';
                    $s5 = '';
                } else {
                    $s1s2 = ! is_array($click['subid_2']) ? $click['subid_2'] : '';
                    $s3 = ! is_array($click['subid_3']) && $s3Breakdown ? $click['subid_3'] : '';
                    $s4 = ! is_array($click['subid_4']) && $s4Breakdown ? $click['subid_4'] : '';
                    // $s5 = !is_array($click['subid_5']) ? $click['subid_5'] : '';
                    $s5 = '';
                    $s12 = explode('__', $s1s2);
                    $s1 = isset($s12[0]) && $s1Breakdown ? $s12[0] : '';
                    $s2 = isset($s12[1]) && $s2Breakdown ? $s12[1] : '';
                }

                if (! isset($data[$revenue_tracker_id][$s1][$s2][$s3][$s4][$s5])) {
                    $data[$revenue_tracker_id][$s1][$s2][$s3][$s4][$s5] = 0;
                    $rev_trackers[] = $revenue_tracker_id;
                }
                $data[$revenue_tracker_id][$s1][$s2][$s3][$s4][$s5] += isset($click['price']) && ! is_array($click['price']) ? $click['price'] : 0;
            }
        }

        $conversion_data = $this->getCurlResponse($conversions_url);
        if (isset($conversion_data['conversions']['conversion'])) {
            if ($conversion_data['row_count'] <= 1) {
                $conversions[] = $conversion_data['conversions']['conversion'];
            } else {
                $conversions = $conversion_data['conversions']['conversion'];
            }
            foreach ($conversions as $conversion) {
                $rev_tracker = ! is_array($conversion['subid_1']) ? $conversion['subid_1'] : '';
                $revenue_tracker_id = str_replace('CD', '', $rev_tracker);
                $allowBreakdown = isset($this->subIDBreakdown[$revenue_tracker_id]['status']) ? $this->subIDBreakdown[$revenue_tracker_id]['status'] : 0;
                $s1Breakdown = isset($this->subIDBreakdown[$revenue_tracker_id]['s1']) ? $this->subIDBreakdown[$revenue_tracker_id]['s1'] : 0;
                $s2Breakdown = isset($this->subIDBreakdown[$revenue_tracker_id]['s2']) ? $this->subIDBreakdown[$revenue_tracker_id]['s2'] : 0;
                $s3Breakdown = isset($this->subIDBreakdown[$revenue_tracker_id]['s3']) ? $this->subIDBreakdown[$revenue_tracker_id]['s3'] : 0;
                $s4Breakdown = isset($this->subIDBreakdown[$revenue_tracker_id]['s4']) ? $this->subIDBreakdown[$revenue_tracker_id]['s4'] : 0;
                // $s5Breakdown = $this->subIDBreakdown[$revenue_tracker_id]['s5'];

                if ($allowBreakdown == 0) {
                    $s1 = '';
                    $s2 = '';
                    $s3 = '';
                    $s4 = '';
                    $s5 = '';
                } else {
                    $s1s2 = ! is_array($conversion['subid_2']) ? $conversion['subid_2'] : '';
                    $s3 = ! is_array($conversion['subid_3']) && $s3Breakdown ? $conversion['subid_3'] : '';
                    $s4 = ! is_array($conversion['subid_4']) && $s4Breakdown ? $conversion['subid_4'] : '';
                    // $s5 = !is_array($conversion['subid_5']) ? $conversion['subid_5'] : '';
                    $s5 = '';
                    $s12 = explode('__', $s1s2);
                    $s1 = isset($s12[0]) && $s1Breakdown ? $s12[0] : '';
                    $s2 = isset($s12[1]) && $s2Breakdown ? $s12[1] : '';
                }

                if (! isset($data[$revenue_tracker_id][$s1][$s2][$s3][$s4][$s5])) {
                    $data[$revenue_tracker_id][$s1][$s2][$s3][$s4][$s5] = 0;
                    $rev_trackers[] = $revenue_tracker_id;
                }

                $data[$revenue_tracker_id][$s1][$s2][$s3][$s4][$s5] += isset($conversion['price']) && ! is_array($conversion['price']) ? $conversion['price'] : 0;
            }
        }

        $rows = [];
        $affiliates = $this->affiliates;
        foreach ($data as $revenue_tracker_id => $rt_data) {
            foreach ($rt_data as $s1 => $s1d) {
                foreach ($s1d as $s2 => $s2d) {
                    foreach ($s2d as $s3 => $s3d) {
                        foreach ($s3d as $s4 => $s4d) {
                            foreach ($s4d as $s5 => $revenue) {
                                if (isset($affiliates[$revenue_tracker_id])) {
                                    $affiliate_id = $affiliates[$revenue_tracker_id];
                                    $rows[] = [
                                        'affiliate_id' => $affiliate_id,
                                        'revenue_tracker_id' => $revenue_tracker_id,
                                        'campaign_id' => $sbg_lr_id,
                                        'created_at' => $this->dateFromStr,
                                        's1' => $s1,
                                        's2' => $s2,
                                        's3' => $s3,
                                        's4' => $s4,
                                        's5' => $s5,
                                        'revenue' => $revenue,
                                        'lead_count' => 0,
                                        'reject_count' => 0,
                                        'failed_count' => 0,
                                    ];
                                }
                            }
                        }
                    }
                }
            }
        }

        $chunks = array_chunk($rows, 1000);
        foreach ($chunks as $chunk) {
            try {
                DB::table('affiliate_reports')->insert($chunk);
                //Log::info('SBG Revenue Chunks total: ' . count($chunk));
            } catch (QueryException $e) {
                Log::info('SBG Revenue!');
                Log::info($e->getMessage());

                $this->errors[] = [
                    'Area' => 'SBG Revenue',
                    'Info' => 'DB Saving',
                    'MSG' => $e->getMessage(),
                ];
            }
        }
        $this->timeTracker('SBG', $start, Carbon::now());
    }

    public function getEPollRevenue()
    {
        $start = Carbon::now();
        $lr_id = env('CPA_WALL_EPOLL_CAMPAIGN_ID', 285);
        $url = config('constants.EPOLL_REPORTS_API');
        $url .= "&data_start=$this->dateFromStr&data_end=$this->dateFromStr";

        $rt_data = [];
        $rev_trackers = [];
        $page = 1;
        while (true) {
            $stats = $this->getCurlResponse($url."&page=$page", 'json');
            if (isset($stats['response']['data'])) {
                if (count($stats['response']['data']['data']) == 0) {
                    break;
                } else {
                    foreach ($stats['response']['data']['data'] as $stat) {
                        $data = $stat['Stat'];
                        $rev_tracker = $data['affiliate_info1'];
                        $revenue_tracker_id = str_replace('CD', '', $rev_tracker);
                        $revenue_tracker_id = $revenue_tracker_id == '' ? 1 : $revenue_tracker_id;
                        $allowBreakdown = isset($this->subIDBreakdown[$revenue_tracker_id]['status']) ? $this->subIDBreakdown[$revenue_tracker_id]['status'] : 0;
                        $s1Breakdown = isset($this->subIDBreakdown[$revenue_tracker_id]['s1']) ? $this->subIDBreakdown[$revenue_tracker_id]['s1'] : 0;
                        $s2Breakdown = isset($this->subIDBreakdown[$revenue_tracker_id]['s2']) ? $this->subIDBreakdown[$revenue_tracker_id]['s2'] : 0;
                        $s3Breakdown = isset($this->subIDBreakdown[$revenue_tracker_id]['s3']) ? $this->subIDBreakdown[$revenue_tracker_id]['s3'] : 0;
                        $s4Breakdown = isset($this->subIDBreakdown[$revenue_tracker_id]['s4']) ? $this->subIDBreakdown[$revenue_tracker_id]['s4'] : 0;
                        // $s5Breakdown = $this->subIDBreakdown[$revenue_tracker_id]['s5'];

                        if ($allowBreakdown == 0) {
                            $s1 = '';
                            $s2 = '';
                            $s3 = '';
                            $s4 = '';
                            $s5 = '';
                        } else {
                            $s1s2 = $data['affiliate_info2'];
                            $s3 = $s3Breakdown ? $data['affiliate_info3'] : '';
                            $s4 = $s4Breakdown ? $data['affiliate_info4'] : '';
                            // $s5 = $data['affiliate_info5'];
                            $s5 = '';
                            $s12 = explode('__', $s1s2);
                            $s1 = isset($s12[0]) && $s1Breakdown ? $s12[0] : '';
                            $s2 = isset($s12[1]) && $s2Breakdown ? $s12[1] : '';
                        }

                        if (! isset($rt_data[$revenue_tracker_id][$s1][$s2][$s3][$s4][$s5])) {
                            $rt_data[$revenue_tracker_id][$s1][$s2][$s3][$s4][$s5] = 0;
                            $rev_trackers[] = $revenue_tracker_id;
                        }
                        $rt_data[$revenue_tracker_id][$s1][$s2][$s3][$s4][$s5] += $data['payout'];
                    }

                    if ($stats['response']['data']['pageCount'] == $page) {
                        break;
                    }
                }
            }

            $page++;
        }

        $rows = [];
        $affiliates = $this->affiliates;
        foreach ($rt_data as $revenue_tracker_id => $rtdata) {
            foreach ($rtdata as $s1 => $s1d) {
                foreach ($s1d as $s2 => $s2d) {
                    foreach ($s2d as $s3 => $s3d) {
                        foreach ($s3d as $s4 => $s4d) {
                            foreach ($s4d as $s5 => $revenue) {
                                if (isset($affiliates[$revenue_tracker_id])) {
                                    $affiliate_id = $affiliates[$revenue_tracker_id];
                                    $rows[] = [
                                        'affiliate_id' => $affiliate_id,
                                        'revenue_tracker_id' => $revenue_tracker_id,
                                        'campaign_id' => $lr_id,
                                        'created_at' => $this->dateFromStr,
                                        's1' => $s1,
                                        's2' => $s2,
                                        's3' => $s3,
                                        's4' => $s4,
                                        's5' => $s5,
                                        'revenue' => $revenue,
                                        'lead_count' => 0,
                                        'reject_count' => 0,
                                        'failed_count' => 0,
                                    ];
                                }
                            }
                        }
                    }
                }
            }
        }

        $chunks = array_chunk($rows, 1000);
        foreach ($chunks as $chunk) {
            try {
                DB::table('affiliate_reports')->insert($chunk);
            } catch (QueryException $e) {
                Log::info('EPoll Revenue!');
                Log::info($e->getMessage());

                $this->errors[] = [
                    'Area' => 'EPoll Revenue',
                    'Info' => 'DB Saving',
                    'MSG' => $e->getMessage(),
                ];
            }
        }
        $this->timeTracker('EPoll', $start, Carbon::now());
    }

    public function getJobs2ShopRevenue()
    {
        $start = Carbon::now();
        $lr_id = env('CPA_WALL_JOB_TO_SHOP_CAMPAIGN_ID', 297);
        $url = config('constants.JOBS2SHOP_REPORTS_API');
        $url .= "&data_start=$this->dateFromStr&data_end=$this->dateFromStr";

        $rt_data = [];
        $rev_trackers = [];
        $page = 1;
        while (true) {
            $stats = $this->getCurlResponse($url."&page=$page", 'json');
            if (isset($stats['response']['data'])) {
                if (count($stats['response']['data']['data']) == 0) {
                    break;
                } else {
                    foreach ($stats['response']['data']['data'] as $stat) {
                        $data = $stat['Stat'];
                        $rev_tracker = $data['affiliate_info1'];
                        $revenue_tracker_id = str_replace('CD', '', $rev_tracker);
                        $revenue_tracker_id = $revenue_tracker_id == '' ? 1 : $revenue_tracker_id;
                        $allowBreakdown = isset($this->subIDBreakdown[$revenue_tracker_id]['status']) ? $this->subIDBreakdown[$revenue_tracker_id]['status'] : 0;
                        $s1Breakdown = isset($this->subIDBreakdown[$revenue_tracker_id]['s1']) ? $this->subIDBreakdown[$revenue_tracker_id]['s1'] : 0;
                        $s2Breakdown = isset($this->subIDBreakdown[$revenue_tracker_id]['s2']) ? $this->subIDBreakdown[$revenue_tracker_id]['s2'] : 0;
                        $s3Breakdown = isset($this->subIDBreakdown[$revenue_tracker_id]['s3']) ? $this->subIDBreakdown[$revenue_tracker_id]['s3'] : 0;
                        $s4Breakdown = isset($this->subIDBreakdown[$revenue_tracker_id]['s4']) ? $this->subIDBreakdown[$revenue_tracker_id]['s4'] : 0;
                        // $s5Breakdown = $this->subIDBreakdown[$revenue_tracker_id]['s5'];

                        if ($allowBreakdown == 0) {
                            $s1 = '';
                            $s2 = '';
                            $s3 = '';
                            $s4 = '';
                            $s5 = '';
                        } else {
                            $s1s2 = $data['affiliate_info2'];
                            $s3 = $s3Breakdown ? $data['affiliate_info3'] : '';
                            $s4 = $s4Breakdown ? $data['affiliate_info4'] : '';
                            // $s5 = $data['affiliate_info5'];
                            $s5 = '';
                            $s12 = explode('__', $s1s2);
                            $s1 = isset($s12[0]) && $s1Breakdown ? $s12[0] : '';
                            $s2 = isset($s12[1]) && $s2Breakdown ? $s12[1] : '';
                        }

                        if (! isset($rt_data[$revenue_tracker_id][$s1][$s2][$s3][$s4][$s5])) {
                            $rt_data[$revenue_tracker_id][$s1][$s2][$s3][$s4][$s5] = 0;
                            $rev_trackers[] = $revenue_tracker_id;
                        }
                        $rt_data[$revenue_tracker_id][$s1][$s2][$s3][$s4][$s5] += $data['payout'];
                    }

                    if ($stats['response']['data']['pageCount'] == $page) {
                        break;
                    }
                }
            }

            $page++;
        }

        $rows = [];
        $affiliates = $this->affiliates;
        foreach ($rt_data as $revenue_tracker_id => $rtdata) {
            foreach ($rtdata as $s1 => $s1d) {
                foreach ($s1d as $s2 => $s2d) {
                    foreach ($s2d as $s3 => $s3d) {
                        foreach ($s3d as $s4 => $s4d) {
                            foreach ($s4d as $s5 => $revenue) {
                                if (isset($affiliates[$revenue_tracker_id])) {
                                    $affiliate_id = $affiliates[$revenue_tracker_id];
                                    $rows[] = [
                                        'affiliate_id' => $affiliate_id,
                                        'revenue_tracker_id' => $revenue_tracker_id,
                                        'campaign_id' => $lr_id,
                                        'created_at' => $this->dateFromStr,
                                        's1' => $s1,
                                        's2' => $s2,
                                        's3' => $s3,
                                        's4' => $s4,
                                        's5' => $s5,
                                        'revenue' => $revenue,
                                        'lead_count' => 0,
                                        'reject_count' => 0,
                                        'failed_count' => 0,
                                    ];
                                }
                            }
                        }
                    }
                }
            }
        }

        $chunks = array_chunk($rows, 1000);
        foreach ($chunks as $chunk) {
            try {
                DB::table('affiliate_reports')->insert($chunk);
            } catch (QueryException $e) {
                Log::info('Jobs2Shop Revenue!');
                Log::info($e->getMessage());

                $this->errors[] = [
                    'Area' => 'Jobs2Shop Revenue',
                    'Info' => 'DB Saving',
                    'MSG' => $e->getMessage(),
                ];
            }
        }
        $this->timeTracker('Jobs2Shop', $start, Carbon::now());
    }

    public function getTiburonRevenue()
    {
        $start = Carbon::now();
        $lr_id = env('EXTERNAL_PATH_TIBURON_CAMPAIGN_ID', 290);
        $url = config('constants.TIBURON_NEW_REPORTS_API');
        $from = urlencode($this->dateFromStr.'T00:00:00');
        $to = urlencode($this->dateFromStr.'T23:59:59');
        $url = str_replace('date_time_start', $from, $url);
        $url = str_replace('date_time_end', $to, $url);

        $curl = new Curl();
        $curl->setBasicAuthentication('EngageIq', 'Thunder@39');
        $curl->get($url);

        Log::info($curl->request_headers);
        $response = simplexml_load_string($curl->response, 'SimpleXMLElement', LIBXML_NOWARNING);
        $affiliates = $this->affiliates;
        $data = [];
        $rows = [];
        // if($response['summary']['no_of_records'] > 0) {
        if ($response->summary->no_of_records > 0) {
            foreach ($response->placements->placement as $placement) {
                foreach ($placement->transactions->transaction as $transaction) {
                    $revenue_tracker_id = str_replace('CD', '', $transaction->affId);
                    $revenue_tracker_id = $revenue_tracker_id == '' ? 1 : $revenue_tracker_id;
                    $allowBreakdown = isset($this->subIDBreakdown[$revenue_tracker_id]['status']) ? $this->subIDBreakdown[$revenue_tracker_id]['status'] : 0;
                    $s1Breakdown = isset($this->subIDBreakdown[$revenue_tracker_id]['s1']) ? $this->subIDBreakdown[$revenue_tracker_id]['s1'] : 0;
                    $s2Breakdown = isset($this->subIDBreakdown[$revenue_tracker_id]['s2']) ? $this->subIDBreakdown[$revenue_tracker_id]['s2'] : 0;
                    $s3Breakdown = isset($this->subIDBreakdown[$revenue_tracker_id]['s3']) ? $this->subIDBreakdown[$revenue_tracker_id]['s3'] : 0;
                    $s4Breakdown = isset($this->subIDBreakdown[$revenue_tracker_id]['s4']) ? $this->subIDBreakdown[$revenue_tracker_id]['s4'] : 0;
                    // $s5Breakdown = $this->subIDBreakdown[$revenue_tracker_id]['s5'];

                    if ($allowBreakdown == 0) {
                        $s1 = '';
                        $s2 = '';
                        $s3 = '';
                        $s4 = '';
                        $s5 = '';
                    } else {
                        $subID = $transaction->subId;
                        $sub_id = explode('__', $subID);
                        $s1 = isset($sub_id[0]) && $s1Breakdown ? trim($sub_id[0]) : '';
                        $s2 = isset($sub_id[1]) && $s2Breakdown ? trim($sub_id[1]) : '';
                        $s3 = isset($sub_id[2]) && $s3Breakdown ? trim($sub_id[2]) : '';
                        $s4 = isset($sub_id[3]) && $s4Breakdown ? trim($sub_id[3]) : '';
                        // $s5 = isset($sub_id[4]) ? trim($sub_id[4]) : '';
                        $s5 = '';
                    }

                    if (! isset($data[$revenue_tracker_id][$s1][$s2][$s3][$s4][$s5])) {
                        $data[$revenue_tracker_id][$s1][$s2][$s3][$s4][$s5] = 0;
                    }

                    $data[$revenue_tracker_id][$s1][$s2][$s3][$s4][$s5] += (float) $transaction->revenue;
                }
            }

            foreach ($data as $revenue_tracker_id => $rt_data) {
                foreach ($rt_data as $s1 => $s1_data) {
                    foreach ($s1_data as $s2 => $s2_data) {
                        foreach ($s2_data as $s3 => $s3_data) {
                            foreach ($s3_data as $s4 => $s4_data) {
                                foreach ($s4_data as $s5 => $revenue) {
                                    if (isset($affiliates[$revenue_tracker_id])) {
                                        $affiliate_id = $affiliates[$revenue_tracker_id];
                                        $rows[] = [
                                            'affiliate_id' => $affiliate_id,
                                            'revenue_tracker_id' => $revenue_tracker_id,
                                            'campaign_id' => $lr_id,
                                            'created_at' => $this->dateFromStr,
                                            's1' => $s1,
                                            's2' => $s2,
                                            's3' => $s3,
                                            's4' => $s4,
                                            's5' => $s5,
                                            'revenue' => $revenue,
                                            'lead_count' => 0,
                                            'reject_count' => 0,
                                            'failed_count' => 0,
                                        ];
                                    }
                                }
                            }
                        }
                    }
                }
            }

            Log::info($rows);

            $chunks = array_chunk($rows, 1000);
            foreach ($chunks as $chunk) {
                try {
                    DB::table('affiliate_reports')->insert($chunk);
                } catch (QueryException $e) {
                    Log::info('Tiburon Revenue!');
                    Log::info($e->getMessage());

                    $this->errors[] = [
                        'Area' => 'Tiburon Revenue',
                        'Info' => 'DB Saving',
                        'MSG' => $e->getMessage(),
                    ];
                }
            }
        }
        $this->timeTracker('Tiburon', $start, Carbon::now());
    }

    public function getIfficientRevenue()
    {
        $start = Carbon::now();
        $affiliates = $this->affiliates;
        $lr_id = env('EXTERNAL_PATH_IFFICIENT_CAMPAIGN_ID', 287);
        $url = 'https://api.ifficient.com/inquiry/pubrevenue';
        $date = Carbon::parse($this->dateFromStr);
        $dateStr = $date->month.'/'.$date->day.'/'.$date->year;

        $curl = new Curl();
        $curl->setBasicAuthentication('engageiq', 'engage26#2');
        $curl->get($url, [
            'sourceids' => 'all',
            'subids' => 'all',
            'startdate' => $dateStr,
            'enddate' => $dateStr,
        ]);

        Log::info($curl->request_headers);
        $response = json_decode($curl->response, true);
        $data = [];
        $rows = [];
        if (isset($response['Sources']) && count($response['Sources']) > 0) {
            foreach ($response['Sources'] as $source) {
                foreach ($source['Subids'] as $subid) {
                    $sid = explode('__', $subid['Sub']);
                    $rev_tracker = isset($sid[0]) ? $sid[0] : 'CD1';
                    $revenue_tracker_id = str_replace('CD', '', $rev_tracker);
                    $revenue_tracker_id = $revenue_tracker_id == '' ? 1 : $revenue_tracker_id;
                    $allowBreakdown = isset($this->subIDBreakdown[$revenue_tracker_id]['status']) ? $this->subIDBreakdown[$revenue_tracker_id]['status'] : 0;
                    $s1Breakdown = isset($this->subIDBreakdown[$revenue_tracker_id]['s1']) ? $this->subIDBreakdown[$revenue_tracker_id]['s1'] : 0;
                    $s2Breakdown = isset($this->subIDBreakdown[$revenue_tracker_id]['s2']) ? $this->subIDBreakdown[$revenue_tracker_id]['s2'] : 0;
                    $s3Breakdown = isset($this->subIDBreakdown[$revenue_tracker_id]['s3']) ? $this->subIDBreakdown[$revenue_tracker_id]['s3'] : 0;
                    $s4Breakdown = isset($this->subIDBreakdown[$revenue_tracker_id]['s4']) ? $this->subIDBreakdown[$revenue_tracker_id]['s4'] : 0;
                    // $s5Breakdown = $this->subIDBreakdown[$revenue_tracker_id]['s5'];

                    if ($allowBreakdown == 0) {
                        $s1 = '';
                        $s2 = '';
                        $s3 = '';
                        $s4 = '';
                        $s5 = '';
                    } else {
                        $s1 = isset($sid[1]) && $s1Breakdown ? $sid[1] : '';
                        $s2 = isset($sid[2]) && $s2Breakdown ? $sid[2] : '';
                        $s3 = isset($sid[3]) && $s3Breakdown ? $sid[3] : '';
                        $s4 = isset($sid[4]) && $s4Breakdown ? $sid[4] : '';
                        // $s5 = isset($sid[5]) ? $sid[5] : '';
                        $s5 = '';
                    }

                    if (! isset($data[$revenue_tracker_id][$s1][$s2][$s3][$s4][$s5])) {
                        $data[$revenue_tracker_id][$s1][$s2][$s3][$s4][$s5] = 0;
                    }

                    $revenue = str_replace('$', '', $subid['Revenue']);
                    $data[$revenue_tracker_id][$s1][$s2][$s3][$s4][$s5] += $revenue;
                }
            }

            // Log::info($data);

            foreach ($data as $revenue_tracker_id => $rt_data) {
                foreach ($rt_data as $s1 => $s1_data) {
                    foreach ($s1_data as $s2 => $s2_data) {
                        foreach ($s2_data as $s3 => $s3_data) {
                            foreach ($s3_data as $s4 => $s4_data) {
                                foreach ($s4_data as $s5 => $revenue) {
                                    if (isset($affiliates[$revenue_tracker_id])) {
                                        $affiliate_id = $affiliates[$revenue_tracker_id];
                                        $rows[] = [
                                            'affiliate_id' => $affiliate_id,
                                            'revenue_tracker_id' => $revenue_tracker_id,
                                            'campaign_id' => $lr_id,
                                            'created_at' => $this->dateFromStr,
                                            's1' => $s1,
                                            's2' => $s2,
                                            's3' => $s3,
                                            's4' => $s4,
                                            's5' => $s5,
                                            'revenue' => $revenue,
                                            'lead_count' => 0,
                                            'reject_count' => 0,
                                            'failed_count' => 0,
                                        ];
                                    }
                                }
                            }
                        }
                    }
                }
            }

            $chunks = array_chunk($rows, 1000);
            foreach ($chunks as $chunk) {
                try {
                    DB::table('affiliate_reports')->insert($chunk);
                } catch (QueryException $e) {
                    Log::info('Ifficient Saving Error!');
                    Log::info($e->getMessage());

                    $this->errors[] = [
                        'Area' => 'Ifficient',
                        'Info' => 'DB Saving',
                        'MSG' => $e->getMessage(),
                    ];
                }
            }
        } else {
            Log::info('No Sources found.');
            Log::info($response);
        }
        $this->timeTracker('Ifficient', $start, Carbon::now());
    }

    public function getRexadzRevenue()
    {
        $start = Carbon::now();
        $affiliates = $this->affiliates;
        $lr_id = env('EXTERNAL_PATH_REXADS_CAMPAIGN_ID', 289);
        $url = 'http://rexadz.com/api/?key=UGMzdVBlU3FkMWhBOUtiR2pwUjFCUT09';
        $date = Carbon::parse($this->dateFromStr);
        $dateStr = $date->month.'/'.$date->day.'/'.$date->year;
        $url .= "&startdate=$dateStr&enddate=$dateStr";
        Log::info('curl: '.$url);
        $curl = new Curl();
        $curl->get($url);
        $xml = simplexml_load_string($curl->response);
        $data = [];
        $rows = [];
        if (isset($xml->websites)) {
            foreach ($xml->websites as $website) {
                foreach ($website->subid1->subid as $value) {

                    $sid = explode('__', $value['id']);
                    $rev_tracker = isset($sid[0]) ? $sid[0] : 'CD1';
                    $revenue_tracker_id = str_replace('CD', '', $rev_tracker);
                    $revenue_tracker_id = $revenue_tracker_id == '' ? 1 : $revenue_tracker_id;
                    $allowBreakdown = isset($this->subIDBreakdown[$revenue_tracker_id]['status']) ? $this->subIDBreakdown[$revenue_tracker_id]['status'] : 0;
                    $s1Breakdown = isset($this->subIDBreakdown[$revenue_tracker_id]['s1']) ? $this->subIDBreakdown[$revenue_tracker_id]['s1'] : 0;
                    $s2Breakdown = isset($this->subIDBreakdown[$revenue_tracker_id]['s2']) ? $this->subIDBreakdown[$revenue_tracker_id]['s2'] : 0;
                    $s3Breakdown = isset($this->subIDBreakdown[$revenue_tracker_id]['s3']) ? $this->subIDBreakdown[$revenue_tracker_id]['s3'] : 0;
                    $s4Breakdown = isset($this->subIDBreakdown[$revenue_tracker_id]['s4']) ? $this->subIDBreakdown[$revenue_tracker_id]['s4'] : 0;
                    // $s5Breakdown = $this->subIDBreakdown[$revenue_tracker_id]['s5'];

                    if ($allowBreakdown == 0) {
                        $s1 = '';
                        $s2 = '';
                        $s3 = '';
                        $s4 = '';
                        $s5 = '';
                    } else {
                        $s1 = isset($sid[1]) && $s1Breakdown ? $sid[1] : '';
                        $s2 = isset($sid[2]) && $s2Breakdown ? $sid[2] : '';
                        $s3 = isset($sid[3]) && $s3Breakdown ? $sid[3] : '';
                        $s4 = isset($sid[4]) && $s4Breakdown ? $sid[4] : '';
                        // $s5 = isset($sid[5]) ? $sid[5] : '';
                        $s5 = '';
                    }

                    if (! isset($data[$revenue_tracker_id][$s1][$s2][$s3][$s4][$s5])) {
                        $data[$revenue_tracker_id][$s1][$s2][$s3][$s4][$s5] = 0;
                    }

                    $revenue = str_replace('$', '', $value);
                    $data[$revenue_tracker_id][$s1][$s2][$s3][$s4][$s5] += $revenue;
                }
            }

            foreach ($data as $revenue_tracker_id => $rt_data) {
                foreach ($rt_data as $s1 => $s1_data) {
                    foreach ($s1_data as $s2 => $s2_data) {
                        foreach ($s2_data as $s3 => $s3_data) {
                            foreach ($s3_data as $s4 => $s4_data) {
                                foreach ($s4_data as $s5 => $revenue) {
                                    if (isset($affiliates[$revenue_tracker_id])) {
                                        $affiliate_id = $affiliates[$revenue_tracker_id];
                                        $rows[] = [
                                            'affiliate_id' => $affiliate_id,
                                            'revenue_tracker_id' => $revenue_tracker_id,
                                            'campaign_id' => $lr_id,
                                            'created_at' => $this->dateFromStr,
                                            's1' => $s1,
                                            's2' => $s2,
                                            's3' => $s3,
                                            's4' => $s4,
                                            's5' => $s5,
                                            'revenue' => $revenue,
                                            'lead_count' => 0,
                                            'reject_count' => 0,
                                            'failed_count' => 0,
                                        ];
                                    }
                                }
                            }
                        }
                    }
                }
            }

            $chunks = array_chunk($rows, 1000);
            foreach ($chunks as $chunk) {
                try {
                    DB::table('affiliate_reports')->insert($chunk);
                } catch (QueryException $e) {
                    Log::info('Rexadz Revenue!');
                    Log::info($e->getMessage());

                    $this->errors[] = [
                        'Area' => 'Rexadz Revenue',
                        'Info' => 'DB Saving',
                        'MSG' => $e->getMessage(),
                    ];
                }
            }
        }
        $this->timeTracker('Rexadz', $start, Carbon::now());
    }

    public function getPermissionDataRevenue()
    {
        $start = Carbon::now();
        $date = Carbon::parse($this->dateFromStr)->format('Y-m-d');
        $affiliates = $this->affiliates;
        $lr_id = env('EXTERNAL_PATH_PERMISSION_DATA_CAMPAIGN_ID', 283);
        $settings = Setting::where('code', 'permission_data_pub_codes')->select('description')->first();
        $pub_codes = json_decode($settings->description, true);
        $data = [];
        $rows = [];
        foreach ($pub_codes as $pub_code) {
            Log::info('Pub Code: '.$pub_code);
            $session_url = 'http://api.pdreporting.com/API/aerAPI/v1/?output=XML&method=getSessionUUID&login=repAOP&pin=31N5T3in&pub_code='.$pub_code.'&pub_key=everything_is_awesome';
            Log::info('get session id:');
            $session_response = $this->getCurlResponse($session_url);

            if (isset($session_response['result']['sessionUUID'])) {
                $session_id = $session_response['result']['sessionUUID'];

                $url = "http://api.pdreporting.com/API/aerAPI/v1/?output=XML&method=getSourceTrackingByDateReport&date=date_range&pub_code=$pub_code&sessionUUID=$session_id";
                $url = str_replace('date_range', "$date,$date", $url);
                $curl = new Curl();
                $curl->get($url);
                Log::info('get data:');
                Log::info('curl :'.$url);
                $xml = simplexml_load_string($curl->response);
                if (isset($xml->result->report->groups->group->groupRecords->record)) {
                    foreach ($xml->result->report->groups->group->groupRecords->record as $value) {
                        $sid = explode('__', $value->sourceId);
                        $rev_tracker = isset($sid[0]) ? $sid[0] : 'pfr_CD1';
                        $revenue_tracker_id = str_replace('pfr_CD', '', $rev_tracker);
                        $revenue_tracker_id = $revenue_tracker_id == '' ? 1 : $revenue_tracker_id;
                        $allowBreakdown = isset($this->subIDBreakdown[$revenue_tracker_id]['status']) ? $this->subIDBreakdown[$revenue_tracker_id]['status'] : 0;
                        $s1Breakdown = isset($this->subIDBreakdown[$revenue_tracker_id]['s1']) ? $this->subIDBreakdown[$revenue_tracker_id]['s1'] : 0;
                        $s2Breakdown = isset($this->subIDBreakdown[$revenue_tracker_id]['s2']) ? $this->subIDBreakdown[$revenue_tracker_id]['s2'] : 0;
                        $s3Breakdown = isset($this->subIDBreakdown[$revenue_tracker_id]['s3']) ? $this->subIDBreakdown[$revenue_tracker_id]['s3'] : 0;
                        $s4Breakdown = isset($this->subIDBreakdown[$revenue_tracker_id]['s4']) ? $this->subIDBreakdown[$revenue_tracker_id]['s4'] : 0;
                        // $s5Breakdown = $this->subIDBreakdown[$revenue_tracker_id]['s5'];

                        if ($allowBreakdown == 0) {
                            $s1 = '';
                            $s2 = '';
                            $s3 = '';
                            $s4 = '';
                            $s5 = '';
                        } else {
                            $s1 = isset($sid[1]) && $s1Breakdown ? $sid[1] : '';
                            $s2 = isset($sid[2]) && $s2Breakdown ? $sid[2] : '';
                            $s3 = isset($sid[3]) && $s3Breakdown ? $sid[3] : '';
                            $s4 = isset($sid[4]) && $s4Breakdown ? $sid[4] : '';
                            // $s5 = isset($sid[5]) ? $sid[5] : '';
                            $s5 = '';
                        }

                        if (! isset($data[$revenue_tracker_id][$s1][$s2][$s3][$s4][$s5])) {
                            $data[$revenue_tracker_id][$s1][$s2][$s3][$s4][$s5] = 0;
                        }

                        $revenue = floatval($value->publisherRevenueShare);
                        $revenue = round($revenue, 2, PHP_ROUND_HALF_DOWN);

                        $data[$revenue_tracker_id][$s1][$s2][$s3][$s4][$s5] += $revenue;
                    }
                }
            }
        }

        // Log::info($data);
        foreach ($data as $revenue_tracker_id => $rt_data) {
            foreach ($rt_data as $s1 => $s1_data) {
                foreach ($s1_data as $s2 => $s2_data) {
                    foreach ($s2_data as $s3 => $s3_data) {
                        foreach ($s3_data as $s4 => $s4_data) {
                            foreach ($s4_data as $s5 => $revenue) {
                                if (isset($affiliates[$revenue_tracker_id])) {
                                    $affiliate_id = $affiliates[$revenue_tracker_id];
                                    $rows[] = [
                                        'affiliate_id' => $affiliate_id,
                                        'revenue_tracker_id' => $revenue_tracker_id,
                                        'campaign_id' => $lr_id,
                                        'created_at' => $this->dateFromStr,
                                        's1' => $s1,
                                        's2' => $s2,
                                        's3' => $s3,
                                        's4' => $s4,
                                        's5' => $s5,
                                        'revenue' => $revenue,
                                        'lead_count' => 0,
                                        'reject_count' => 0,
                                        'failed_count' => 0,
                                    ];
                                }
                            }
                        }
                    }
                }
            }
        }

        $chunks = array_chunk($rows, 1000);
        foreach ($chunks as $chunk) {
            try {
                DB::table('affiliate_reports')->insert($chunk);
            } catch (QueryException $e) {
                Log::info('Permission Data Revenue!');
                Log::info($e->getMessage());

                $this->errors[] = [
                    'Area' => 'Permission Data Revenue',
                    'Info' => 'DB Saving',
                    'MSG' => $e->getMessage(),
                ];
            }
        }
        $this->timeTracker('Permission Data', $start, Carbon::now());
    }

    public function pushCrewRevenue()
    {
        $start = Carbon::now();
        $lr_id = env('PUSH_CREW_NOTIFICATIONS_CAMPAIGN_ID', 304);
        $cakeBaseClicksURL = config('constants.CAKE_CLICKS_REPORTS_API_V12_UNIQUE');
        $cakeBaseConversionsURL = config('constants.CAKE_CONVERSIONS_API_V15');
        $pushCrewAffiliateID = config('constants.PUSH_CREW_NOTIFICATIONS_CAKE_AFFILIATE_ID');

        $data = [];
        $rowStart = 1;
        $rowLimit = 2500;

        while (true) {
            $cakeurl = $cakeBaseClicksURL;
            $cakeurl = str_replace('start_at_row=0', 'start_at_row='.$rowStart, $cakeurl);
            $cakeurl = str_replace('row_limit=0', "row_limit=$rowLimit", $cakeurl);
            $cakeurl .= "&affiliate_id=$pushCrewAffiliateID&campaign_id=0&start_date=$this->dateFromStr&end_date=$this->dateToStr";

            $response = $this->getCurlResponse($cakeurl);
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
                    $rev_tracker = ! is_array($click['sub_id_1']) ? $click['sub_id_1'] : '';
                    $revenue_tracker_id = str_replace('CD', '', $rev_tracker);
                    $revenue_tracker_id = $revenue_tracker_id == '' ? 1 : $revenue_tracker_id;
                    $allowBreakdown = isset($this->subIDBreakdown[$revenue_tracker_id]['status']) ? $this->subIDBreakdown[$revenue_tracker_id]['status'] : 0;
                    $s1Breakdown = isset($this->subIDBreakdown[$revenue_tracker_id]['s1']) ? $this->subIDBreakdown[$revenue_tracker_id]['s1'] : 0;
                    $s2Breakdown = isset($this->subIDBreakdown[$revenue_tracker_id]['s2']) ? $this->subIDBreakdown[$revenue_tracker_id]['s2'] : 0;
                    $s3Breakdown = isset($this->subIDBreakdown[$revenue_tracker_id]['s3']) ? $this->subIDBreakdown[$revenue_tracker_id]['s3'] : 0;
                    $s4Breakdown = isset($this->subIDBreakdown[$revenue_tracker_id]['s4']) ? $this->subIDBreakdown[$revenue_tracker_id]['s4'] : 0;
                    // $s5Breakdown = $this->subIDBreakdown[$revenue_tracker_id]['s5'];

                    if ($allowBreakdown == 0) {
                        $s1 = '';
                        $s2 = '';
                        $s3 = '';
                        $s4 = '';
                        $s5 = '';
                    } else {
                        $s1s2 = ! is_array($click['sub_id_2']) ? $click['sub_id_2'] : '';
                        $s3 = ! is_array($click['sub_id_3']) && $s3Breakdown ? $click['sub_id_3'] : '';
                        $s4 = ! is_array($click['sub_id_4']) && $s4Breakdown ? $click['sub_id_4'] : '';
                        // $s5 = !is_array($click['sub_id_5']) ? $click['sub_id_5'] : '';
                        $s5 = '';
                        $s12 = explode('__', $s1s2);
                        $s1 = isset($s12[0]) && $s1Breakdown ? $s12[0] : '';
                        $s2 = isset($s12[1]) && $s2Breakdown ? $s12[1] : '';
                    }

                    if (! isset($data[$revenue_tracker_id][$s1][$s2][$s3][$s4][$s5])) {
                        $data[$revenue_tracker_id][$s1][$s2][$s3][$s4][$s5] = 0;
                    }

                    $data[$revenue_tracker_id][$s1][$s2][$s3][$s4][$s5] += isset($click['received']['amount']) ? $click['received']['amount'] : 0;
                }
            }

            $rowStart += $rowLimit;

            if ($rowStart > $totalRows) {
                break;
            }
        }

        $rowStart = 1;
        $rowLimit = 2500;
        while (true) {
            $cakeurl = $cakeBaseConversionsURL;
            $cakeurl = str_replace('start_at_row=0', 'start_at_row='.$rowStart, $cakeurl);
            $cakeurl = str_replace('row_limit=0', "row_limit=$rowLimit", $cakeurl);
            $cakeurl .= "&source_affiliate_id=$pushCrewAffiliateID&start_date=$this->dateFromStr&end_date=$this->dateToStr";

            $response = $this->getCurlResponse($cakeurl);
            $totalRows = $response['row_count'];

            if ($totalRows == 0) {
                break;
            } else {
                $x = $rowStart - 1;
                $currentRows = $totalRows - $x;
                if ($currentRows <= 1) {
                    $conversions[] = $response['event_conversions']['event_conversion'];
                } else {
                    $conversions = $response['event_conversions']['event_conversion'];
                }
                foreach ($conversions as $conversion) {
                    $rev_tracker = ! is_array($conversion['sub_id_1']) ? $conversion['sub_id_1'] : '';
                    $revenue_tracker_id = str_replace('CD', '', $rev_tracker);
                    $revenue_tracker_id = $revenue_tracker_id == '' ? 1 : $revenue_tracker_id;
                    $allowBreakdown = isset($this->subIDBreakdown[$revenue_tracker_id]['status']) ? $this->subIDBreakdown[$revenue_tracker_id]['status'] : 0;
                    $s1Breakdown = isset($this->subIDBreakdown[$revenue_tracker_id]['s1']) ? $this->subIDBreakdown[$revenue_tracker_id]['s1'] : 0;
                    $s2Breakdown = isset($this->subIDBreakdown[$revenue_tracker_id]['s2']) ? $this->subIDBreakdown[$revenue_tracker_id]['s2'] : 0;
                    $s3Breakdown = isset($this->subIDBreakdown[$revenue_tracker_id]['s3']) ? $this->subIDBreakdown[$revenue_tracker_id]['s3'] : 0;
                    $s4Breakdown = isset($this->subIDBreakdown[$revenue_tracker_id]['s4']) ? $this->subIDBreakdown[$revenue_tracker_id]['s4'] : 0;
                    // $s5Breakdown = $this->subIDBreakdown[$revenue_tracker_id]['s5'];
                    $s1s2 = ! is_array($conversion['sub_id_2']) ? $conversion['sub_id_2'] : '';
                    $s3 = ! is_array($conversion['sub_id_3']) && $s3Breakdown ? $conversion['sub_id_3'] : '';
                    $s4 = ! is_array($conversion['sub_id_4']) && $s4Breakdown ? $conversion['sub_id_4'] : '';
                    // $s5 = !is_array($conversion['sub_id_5']) ? $conversion['sub_id_5'] : '';
                    $s5 = '';
                    $s12 = explode('__', $s1s2);
                    $s1 = isset($s12[0]) && $s1Breakdown ? $s12[0] : '';
                    $s2 = isset($s12[1]) && $s2Breakdown ? $s12[1] : '';

                    if (! isset($data[$revenue_tracker_id][$s1][$s2][$s3][$s4][$s5])) {
                        $data[$revenue_tracker_id][$s1][$s2][$s3][$s4][$s5] = 0;
                    }

                    $data[$revenue_tracker_id][$s1][$s2][$s3][$s4][$s5] += isset($conversion['received']['amount']) ? $conversion['received']['amount'] : 0;
                }
            }

            $rowStart += $rowLimit;
            if ($rowStart > $totalRows) {
                break;
            }
        }

        $rows = [];
        $affiliates = $this->affiliates;
        foreach ($data as $revenue_tracker_id => $rt_data) {
            foreach ($rt_data as $s1 => $s1d) {
                foreach ($s1d as $s2 => $s2d) {
                    foreach ($s2d as $s3 => $s3d) {
                        foreach ($s3d as $s4 => $s4d) {
                            foreach ($s4d as $s5 => $revenue) {
                                if (isset($affiliates[$revenue_tracker_id])) {
                                    $affiliate_id = $affiliates[$revenue_tracker_id];
                                    $rows[] = [
                                        'affiliate_id' => $affiliate_id,
                                        'revenue_tracker_id' => $revenue_tracker_id,
                                        'campaign_id' => $lr_id,
                                        'created_at' => $this->dateFromStr,
                                        's1' => $s1,
                                        's2' => $s2,
                                        's3' => $s3,
                                        's4' => $s4,
                                        's5' => $s5,
                                        'revenue' => $revenue,
                                        'lead_count' => 0,
                                        'reject_count' => 0,
                                        'failed_count' => 0,
                                    ];
                                }
                            }
                        }
                    }
                }
            }
        }

        $chunks = array_chunk($rows, 1000);
        foreach ($chunks as $chunk) {
            try {
                DB::table('affiliate_reports')->insert($chunk);
            } catch (QueryException $e) {
                Log::info('PushCrew Revenue!');
                Log::info($e->getMessage());

                $this->errors[] = [
                    'Area' => 'PushCrew Revenue',
                    'Info' => 'DB Saving',
                    'MSG' => $e->getMessage(),
                ];
            }
        }
        $this->timeTracker('Push Crew', $start, Carbon::now());
    }

    public function getCampaignRevenueViewStatistics()
    {
        $start = Carbon::now();
        // DB::enableQueryLog();
        $campaignRevenues = AffiliateReport::whereRaw("affiliate_reports.created_at = '$this->dateFromStr'")
            ->select('affiliate_reports.revenue_tracker_id', 'affiliate_reports.campaign_id', 'affiliate_reports.s1', 'affiliate_reports.s2', 'affiliate_reports.s3', 'affiliate_reports.s4', 'affiliate_reports.s5', DB::raw('SUM(affiliate_reports.revenue) AS revenue'))
            ->groupBy('affiliate_reports.revenue_tracker_id', 'affiliate_reports.campaign_id', 'affiliate_reports.s1', 'affiliate_reports.s2', 'affiliate_reports.s3', 'affiliate_reports.s4', 'affiliate_reports.s5')
            ->get();
        $campaignViewsStats = CampaignView::whereRaw("created_at BETWEEN '$this->dateFromStr 00:00:00' AND '$this->dateFromStr 23:59:59'")
            ->select(DB::RAW('campaign_id, affiliate_id, s1, s2, s3, s4, s5, COUNT(campaign_views.id) as views'))
            ->groupBy('affiliate_id', 'campaign_id', 's1', 's2', 's3', 's4', 's5')->get();
        // Log::info(DB::getQueryLog());
        // Log::info($campaignViewsStats);
        $rows = [];
        $campaignViews = [];
        foreach ($campaignViewsStats as $stat) {
            $campaignViews[$stat->campaign_id][$stat->affiliate_id][$stat->s1][$stat->s2][$stat->s3][$stat->s4][$stat->s5] = $stat->views;
        }

        foreach ($campaignRevenues as $campaignRevenueView) {
            $view = isset($campaignViews[$campaignRevenueView->campaign_id][$campaignRevenueView->revenue_tracker_id][$campaignRevenueView->s1][$campaignRevenueView->s2][$campaignRevenueView->s3][$campaignRevenueView->s4][$campaignRevenueView->s5]) ? $campaignViews[$campaignRevenueView->campaign_id][$campaignRevenueView->revenue_tracker_id][$campaignRevenueView->s1][$campaignRevenueView->s2][$campaignRevenueView->s3][$campaignRevenueView->s4][$campaignRevenueView->s5] : 0;
            $rows[] = [
                'revenue_tracker_id' => $campaignRevenueView->revenue_tracker_id,
                's1' => $campaignRevenueView->s1,
                's2' => $campaignRevenueView->s2,
                's3' => $campaignRevenueView->s3,
                's4' => $campaignRevenueView->s4,
                // 's5' => $campaignRevenueView->s5,
                's5' => '',
                'campaign_id' => $campaignRevenueView->campaign_id,
                'created_at' => $this->dateFromStr,
                'revenue' => is_null($campaignRevenueView->revenue) ? 0 : $campaignRevenueView->revenue,
                'views' => $view,
                'created_at' => $this->dateFromStr,
            ];
        }

        // Log::info($rows);

        //Delete Existing Data
        DB::table('campaign_revenue_view_statistics')->where('created_at', $this->dateFromStr)->delete();

        //Insert New Data
        $chunks = array_chunk($rows, 1000);
        foreach ($chunks as $chunk) {
            DB::table('campaign_revenue_view_statistics')->insert($chunk);
        }

        $this->timeTracker('Campaign Revenue View Statistics', $start, Carbon::now());
    }

    public function setRevenueForNoRevenue()
    {
        $start = Carbon::now();
        $cakeLRID = env('CPA_WALL_ENGAGEIQ_CAMPAIGN_ID', 94);
        $date = $this->dateFromStr;
        // DB::enableQueryLog();
        //Get Rev Trackers with no revenue
        $rev_trackers = RevenueTrackerCakeStatistic::leftJoin('affiliate_reports', function ($q) {
            $q->on('revenue_tracker_cake_statistics.created_at', '=', 'affiliate_reports.created_at')
                ->where('revenue_tracker_cake_statistics.affiliate_id', '=', 'affiliate_reports.affiliate_id')
                ->where('revenue_tracker_cake_statistics.revenue_tracker_id', '=', 'affiliate_reports.revenue_tracker_id')
                ->where('revenue_tracker_cake_statistics.s1', '=', 'affiliate_reports.s1')
                ->where('revenue_tracker_cake_statistics.s2', '=', 'affiliate_reports.s2')
                ->where('revenue_tracker_cake_statistics.s3', '=', 'affiliate_reports.s3')
                ->where('revenue_tracker_cake_statistics.s4', '=', 'affiliate_reports.s4')
                ->where('revenue_tracker_cake_statistics.s5', '=', 'affiliate_reports.s5');
        })->where('revenue_tracker_cake_statistics.created_at', '=', "$date")
            ->whereNull('affiliate_reports.id')
            ->where(function ($q) {
                $q->where('clicks', '>', 0)->orWhere('payout', '>', 0);
            })
            ->select(DB::RAW('revenue_tracker_cake_statistics.*'))
            ->get();
        // Log::info(DB::getQueryLog());

        $rows = [];

        foreach ($rev_trackers as $rt) {
            $rows[] = [
                'affiliate_id' => $rt->affiliate_id,
                'revenue_tracker_id' => $rt->revenue_tracker_id,
                'campaign_id' => $cakeLRID,
                'created_at' => $date,
                's1' => $rt->s1,
                's2' => $rt->s2,
                's3' => $rt->s3,
                's4' => $rt->s4,
                's5' => $rt->s5,
                'revenue' => 0,
                'lead_count' => 0,
                'reject_count' => 0,
                'failed_count' => 0,
            ];
        }

        $chunks = array_chunk($rows, 1000);
        foreach ($chunks as $chunk) {
            try {
                DB::table('affiliate_reports')->insert($chunk);
            } catch (QueryException $e) {
                Log::info('Cake No Revenue Saving Error!');
                Log::info($e->getMessage());

                $this->errors[] = [
                    'Area' => 'Cake No Revenue',
                    'Info' => 'DB Saving',
                    'MSG' => $e->getMessage(),
                ];
            }

        }
        $this->timeTracker('Set Revenue for No Revenue', $start, Carbon::now());
    }

    public function setPayoutforNoClicks()
    {
        $start = Carbon::now();
        $cakeLRID = env('CPA_WALL_ENGAGEIQ_CAMPAIGN_ID', 94);
        $date = $this->dateFromStr;
        // DB::enableQueryLog();
        $rev_campaigns = AffiliateRevenueTracker::pluck('campaign_id', 'revenue_tracker_id')->toArray();

        $rev_trackers = AffiliateReport::leftJoin('revenue_tracker_cake_statistics', function ($q) {
            $q->on('revenue_tracker_cake_statistics.created_at', '=', 'affiliate_reports.created_at')
                ->where('revenue_tracker_cake_statistics.affiliate_id', '=', 'affiliate_reports.affiliate_id')
                ->where('revenue_tracker_cake_statistics.revenue_tracker_id', '=', 'affiliate_reports.revenue_tracker_id')
                ->where('revenue_tracker_cake_statistics.s1', '=', 'affiliate_reports.s1')
                ->where('revenue_tracker_cake_statistics.s2', '=', 'affiliate_reports.s2')
                ->where('revenue_tracker_cake_statistics.s3', '=', 'affiliate_reports.s3')
                ->where('revenue_tracker_cake_statistics.s4', '=', 'affiliate_reports.s4')
                ->where('revenue_tracker_cake_statistics.s5', '=', 'affiliate_reports.s5');
        })
            ->where('affiliate_reports.created_at', '=', "$date")
            ->whereNull('revenue_tracker_cake_statistics.id')
            ->where(function ($q) {
                $q->where('affiliate_reports.lead_count', '>', 0)->orWhere('affiliate_reports.revenue', '>', 0);
            })
            ->where('affiliate_reports.revenue_tracker_id', '!=', 1)
            ->select(DB::RAW('affiliate_reports.affiliate_id, affiliate_reports.revenue_tracker_id, affiliate_reports.s1, affiliate_reports.s2, affiliate_reports.s3, affiliate_reports.s4, affiliate_reports.s5'))
            ->groupBy(DB::RAW('affiliate_reports.affiliate_id, affiliate_reports.revenue_tracker_id, affiliate_reports.s1, affiliate_reports.s2, affiliate_reports.s3, affiliate_reports.s4, affiliate_reports.s5'))
            ->get();
        // Log::info(DB::getQueryLog());

        $rows = [];

        foreach ($rev_trackers as $rt) {
            $campaign_id = isset($rev_campaigns[$rt->campaign_id]) ? $rev_campaigns[$rt->campaign_id] : 0;
            $rows[] = [
                'affiliate_id' => $rt->affiliate_id,
                'revenue_tracker_id' => $rt->revenue_tracker_id,
                'cake_campaign_id' => $campaign_id,
                'created_at' => $date,
                's1' => $rt->s1,
                's2' => $rt->s2,
                's3' => $rt->s3,
                's4' => $rt->s4,
                's5' => $rt->s5,
                'type' => 1,
                'clicks' => 0,
                'payout' => 0,
            ];
        }

        $chunks = array_chunk($rows, 1000);
        foreach ($chunks as $chunk) {
            try {
                DB::table('revenue_tracker_cake_statistics')->insert($chunk);
            } catch (QueryException $e) {
                Log::info('Cake No Clicks Saving Error!');
                Log::info($e->getMessage());

                $this->errors[] = [
                    'Area' => 'Cake No Payout',
                    'Info' => 'DB Saving',
                    'MSG' => $e->getMessage(),
                ];
            }

        }
        $this->timeTracker('Set Payout for No Clicks', $start, Carbon::now());
    }

    public function deleteZeroValues()
    {
        $date = $this->dateFromStr;
        $process = new \App\Helpers\CleanAffiliateReportHelper($date);
        $process->clean();
        // $con = config('app.type') == 'reports' ? 'mysql' : 'secondary';
        // $reports = DB::connection($con)->select("select revenue_tracker_cake_statistics.id as cake_stat_id, affiliate_reports.id as affiliate_report_id, revenue_tracker_cake_statistics.clicks, revenue_tracker_cake_statistics.payout, affiliate_reports.lead_count, affiliate_reports.revenue from `revenue_tracker_cake_statistics` left join `affiliate_reports` on `revenue_tracker_cake_statistics`.`created_at` = `affiliate_reports`.`created_at` and `revenue_tracker_cake_statistics`.`affiliate_id` = affiliate_reports.affiliate_id and `revenue_tracker_cake_statistics`.`revenue_tracker_id` = affiliate_reports.revenue_tracker_id and `revenue_tracker_cake_statistics`.`s1` = affiliate_reports.s1 and `revenue_tracker_cake_statistics`.`s2` = affiliate_reports.s2 and `revenue_tracker_cake_statistics`.`s3` = affiliate_reports.s3 and `revenue_tracker_cake_statistics`.`s4` = affiliate_reports.s4 and `revenue_tracker_cake_statistics`.`s5` = affiliate_reports.s5 where `revenue_tracker_cake_statistics`.`created_at` = '$date'");

        // $affiliate_reports = [];
        // $cake_stats = [];
        // foreach($reports as $report) {
        //     if( ($report->clicks == 0 || $report->clicks == '') &&
        //         ($report->payout == 0 || $report->payout == '') &&
        //         ($report->lead_count == 0 || $report->lead_count == '') &&
        //         ($report->revenue == 0 || $report->revenue == '')
        //     ){
        //         $affiliate_reports[] = $report->affiliate_report_id;
        //         $cake_stats[] = $report->cake_stat_id;
        //     }
        // }

        // // Log::info($affiliate_reports);
        // // Log::info($cake_stats);

        // if(count($affiliate_reports) > 0) {
        //     AffiliateReport::whereIn('id', $affiliate_reports)->delete();
        // }

        // if(count($cake_stats) > 0) {
        //     RevenueTrackerCakeStatistic::whereIn('id', $cake_stats)->delete();
        // }
    }

    public function getJsPathRevenue()
    {
        $rows = [];
        $rev_trackers = AffiliateRevenueTracker::where('offer_id', 1)->where('revenue_tracker_id', '!=', 1)->get();

        // $affiliate_websites = AffiliateWebsite::where('affiliate_id', '!=', 0)
        //     ->select(['affiliate_id', 'id'])
        //     ->pluck('affiliate_id', 'id')->toArray();
        // // \Log::info($affiliate_websites);

        // $affiliate_website_reports = [];
        // $reports = AffiliateWebsiteReport::where('date', $this->dateFromStr)->get();
        // // \Log::info($reports);
        // foreach($reports as $r) {
        //     if(in_array($r->website_id, array_keys($affiliate_websites))) {
        //         $aff_id = $affiliate_websites[$r->website_id];

        //         if(!isset($affiliate_website_reports[$aff_id])) {
        //             $affiliate_website_reports[$aff_id] = [
        //                 'payout' => 0,
        //                 'clicks' => 0
        //             ];
        //         }

        //         $affiliate_website_reports[$aff_id]['payout'] += $r->payout;
        //         $affiliate_website_reports[$aff_id]['clicks'] += $r->count;
        //     }
        // }
        // \Log::info($affiliate_website_reports);

        foreach ($rev_trackers as $rt) {
            Log::info('------------------------------');
            Log::info("Affiliate_id: $rt->affiliate_id");
            Log::info("Revenue_tracker_id: $rt->revenue_tracker_id");
            $affiliate_id = $rt->affiliate_id;
            $revenue_tracker_id = $rt->revenue_tracker_id;
            Log::info('------------------------------');
            Log::info('Get Revenue Tracker Revenue');
            $this->getRevTrackerCakeRevenue($affiliate_id, $revenue_tracker_id);

            // Log::info("------------------------------");
            // Log::info("Generate Revenue Tracker Clicks & Payout");
            // $allowBreakdown = isset($this->subIDBreakdown[$revenue_tracker_id]['status']) ? $this->subIDBreakdown[$revenue_tracker_id]['status'] : 0;
            // $s1Breakdown = isset($this->subIDBreakdown[$revenue_tracker_id]['s1']) ? $this->subIDBreakdown[$revenue_tracker_id]['s1'] : 0;
            // $s2Breakdown = isset($this->subIDBreakdown[$revenue_tracker_id]['s2']) ? $this->subIDBreakdown[$revenue_tracker_id]['s2'] : 0;
            // $s3Breakdown = isset($this->subIDBreakdown[$revenue_tracker_id]['s3']) ? $this->subIDBreakdown[$revenue_tracker_id]['s3'] : 0;
            // $s4Breakdown = isset($this->subIDBreakdown[$revenue_tracker_id]['s4']) ? $this->subIDBreakdown[$revenue_tracker_id]['s4'] : 0;
            // $s5Breakdown = isset($this->subIDBreakdown[$revenue_tracker_id]['s5']) ? $this->subIDBreakdown[$revenue_tracker_id]['s5'] : 0;

            // $rows[] = [
            //     'affiliate_id' => $affiliate_id,
            //     'revenue_tracker_id' => $revenue_tracker_id,
            //     'cake_campaign_id' => 0,
            //     'type' => 1,
            //     'created_at' => $this->dateFromStr,
            //     's1' => '',
            //     's2' => '',
            //     's3' => '',
            //     's4' => '',
            //     's5' => '',
            //     'clicks' => isset($affiliate_website_reports[$affiliate_id]) ? $affiliate_website_reports[$affiliate_id]['clicks'] : 0,
            //     'payout' => isset($affiliate_website_reports[$affiliate_id]) ? $affiliate_website_reports[$affiliate_id]['payout'] : 0
            // ];
        }
        // \Log::info($rows);

        Log::info('Generate Revenue Tracker Clicks & Payout');
        $rows = AffiliateWebsiteReport::where('date', $this->dateFromStr)
            ->groupBy(['affiliate_id', 'revenue_tracker_id', 's1', 's2', 's3', 's4', 's5'])
            ->select(DB::RAW('0 as cake_campaign_id'), DB::RAW('1 as type'), DB::RAW('date as created_at'), DB::RAW('SUM(payout) as payout'), DB::RAW('SUM(count) as clicks'), 'affiliate_id', 'revenue_tracker_id', 's1', 's2', 's3', 's4', 's5')->get()->toArray();
        // \Log::info($rows);
        $chunks = array_chunk($rows, 1000);
        foreach ($chunks as $chunk) {
            try {
                DB::table('revenue_tracker_cake_statistics')->insert($chunk);
                //Log::info('Rev Tracker Cake Stats Chunks total: ' . count($chunk));
            } catch (QueryException $e) {
                Log::info('Rev Tracker Cake Stats Saving Error!');
                Log::info($e->getMessage());

                $this->errors[] = [
                    'Area' => 'Rev Tracker Cake Stats',
                    'Info' => 'DB Saving',
                    'MSG' => $e->getMessage(),
                ];
            }
        }
    }

    /*Helpers*/

    public function getCakeXML($cakeBaseClicksURL, $revenueTracker)
    {
        $cakeClickURL = $cakeBaseClicksURL."&affiliate_id=$revenueTracker&campaign_id=0&start_date=$this->dateFromStr&end_date=$this->dateToStr";
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
                    'MSG' => $e->getMessage(),
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

    public function getCurlXMLResponse($url, $options = null)
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
                    if ($options == 'LIBXML_NOWARNING') {
                        $xml = simplexml_load_string($curl->response, 'SimpleXMLElement', LIBXML_NOWARNING);
                    } else {
                        $xml = simplexml_load_string($curl->response);
                    }
                    $response = $xml;
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

    public function runRestrict()
    {
        Log::info('Generating Internal Affiliate Reports SPECIFIC FUNCTION ONLY');
        Log::info('------------------------------');
        switch ($this->restriction) {
            case 'epoll':
                Log::info('EPOLL');
                $lr_id = env('CPA_WALL_EPOLL_CAMPAIGN_ID', 285);
                $this->deleteCurrentRecord(null, null, $lr_id);
                $this->getEPollRevenue();
                break;
            case 'affluent':
                Log::info('Affluent');
                $lr_id = env('CPA_WALL_AFFLUENT_CAMPAIGN_ID', 96);
                $this->deleteCurrentRecord(null, null, $lr_id);
                $this->getAffluentRevenue();
                break;
            case 'sbg':
                Log::info('SBG');
                $lr_id = env('CPA_WALL_SBG_CAMPAIGN_ID', 95);
                $this->deleteCurrentRecord(null, null, $lr_id);
                $this->getSBGRevenue();
                break;
            case 'jobs2shop':
                Log::info('Jobs2Shop');
                $lr_id = env('CPA_WALL_JOB_TO_SHOP_CAMPAIGN_ID', 297);
                $this->deleteCurrentRecord(null, null, $lr_id);
                $this->getJobs2ShopRevenue();
                break;
            case 'tiburon':
                Log::info('Tiburon');
                $lr_id = env('EXTERNAL_PATH_TIBURON_CAMPAIGN_ID', 290);
                $this->deleteCurrentRecord(null, null, $lr_id);
                $this->getTiburonRevenue();
                break;
            case 'ifficient':
                Log::info('Ifficient');
                $lr_id = env('EXTERNAL_PATH_IFFICIENT_CAMPAIGN_ID', 287);
                $this->deleteCurrentRecord(null, null, $lr_id);
                $this->getIfficientRevenue();
                break;
            case 'rexadz':
                Log::info('Rexadz');
                $lr_id = env('EXTERNAL_PATH_REXADS_CAMPAIGN_ID', 289);
                $this->deleteCurrentRecord(null, null, $lr_id);
                $this->getRexadzRevenue();
                break;
            case 'permission_data':
                Log::info('Permission Data');
                $lr_id = env('EXTERNAL_PATH_PERMISSION_DATA_CAMPAIGN_ID', 283);
                $this->deleteCurrentRecord(null, null, $lr_id);
                $this->getPermissionDataRevenue();
                break;
            case 'push_crew':
                Log::info('Push Crew');
                $lr_id = env('PUSH_CREW_NOTIFICATIONS_CAMPAIGN_ID', 304);
                $this->deleteCurrentRecord(null, null, $lr_id);
                $this->pushCrewRevenue();
            case 'no_revenue':
                Log::info('Set Revenue for No Revenue');
                $this->setRevenueForNoRevenue();
                $this->setPayoutforNoClicks();
            case 'excel':
                Log::info('Excel Report');
                $excel = new AffiliateReportExcelGeneratorHelper($this->dateFromStr, $this->dateFromStr, 1);
                $excel->deleteTempFile();
                $excel->generate();
                break;
        }
        Log::info('Time Tracker');
        Log::info($this->timeLog);
        Log::info('------------------------------');

    }

    public function timeTracker($name, $start, $end)
    {
        $duration = $start->diffInMinutes($end);
        $this->timeLog[] = [
            'name' => $name,
            'duration' => $duration,
        ];
    }

    public function mail()
    {
        $diffInHours = $this->startLog->diffInMinutes($this->endLog).' minute/s';

        $emailNotificationRecipient = env('REPORTS_EMAIL_NOTIFICATION_RECIPIENT', 'marwilburton@hotmail.com');

        //send email to Burt to notify that Affiliate Report Queue was successfully finished
        Mail::send('emails.affiliate_report_execution_email',
            ['startDate' => $this->dateFromStr, 'endDate' => $this->dateToStr, 'executionDuration' => $diffInHours, 'errors' => $this->errors, 'time_log' => $this->timeLog],
            function ($m) use ($emailNotificationRecipient) {
                $m->from('ariel@engageiq.com', 'Ariel Magbanua');
                $m->to($emailNotificationRecipient, 'Marwil Burton')->subject('Affiliate Reports Job Queue Successfully Executed!');
            });
    }

    public function runAffiliateRestrict()
    {
        Log::info('Generating Internal Affiliate Reports SPECIFIC Aff/Rev ONLY');
        Log::info('------------------------------');

        $rev_tracker = $this->revTrackerRestrict != '' ? $this->revTrackerRestrict : null;
        $affiliate_id = $this->affiliateRestrict != '' ? $this->affiliateRestrict : null;

        Log::info('------------------------------');
        Log::info('Get Lead Total Revenue');
        $this->getRevTrackerLeadRevenue($affiliate_id, $rev_tracker);

        // Log::info("------------------------------");
        // Log::info("Get Rev Tracker Registrations");
        // $this->getRegistrationReport();

        Log::info('------------------------------');
        Log::info('Delete Existing Data');
        $this->deleteCurrentRecord($affiliate_id = null, $rev_tracker = null, $campaign_id = null);

        if ($this->revTrackerRestrict == 1) {
            $this->getCD1PayoutAndRevenue();
        } else {
            if ($this->revTrackerRestrict != '') {
                $revenue_trackers = AffiliateRevenueTracker::where('revenue_tracker_id', $this->revTrackerRestrict)->get();
            } elseif ($this->affiliateRestrict != '') {
                $revenue_trackers = AffiliateRevenueTracker::where('affiliate_id', $this->affiliateRestrict)->get();
            }

            if ($revenue_trackers) {
                $this->revTrackers = $revenue_trackers;

                Log::info('------------------------------');
                Log::info('Get Available Revenue Trackers');
                $this->getAvaliableRevTrackers();

                //Get Rev Tracker Payout and Revenue
                if (count($this->availableRevTrackers) > 0) {
                    foreach ($this->availableRevTrackers as $rt) {
                        $affiliate_id = $rt['affiliate_id'];
                        $campaign_id = $rt['campaign_id'];
                        $offer_id = $rt['offer_id'];
                        $revenue_tracker_id = $rt['revenue_tracker_id'];
                        $type = $rt['type'];

                        Log::info("affiliate_id: $affiliate_id");
                        Log::info("revenue_tracker_id: $revenue_tracker_id");

                        Log::info('------------------------------');
                        Log::info('Generate Revenue Tracker Cake Statistics');
                        if ($type == 'CPA') {
                            $this->getRevenueTrackerCPAPayout($rt);
                        } elseif ($type == 'CPM') {
                            $this->getRevenueTrackerCPMPayout($rt);
                        } else {
                            $this->getRevenueTrackerCPCPayout($rt);
                        }

                        Log::info('------------------------------');
                        Log::info('Get Cake Total Revenue');
                        $this->getRevTrackerCakeRevenue($affiliate_id, $revenue_tracker_id);
                    }
                }
            }
        }

        Log::info('------------------------------');
    }
}
