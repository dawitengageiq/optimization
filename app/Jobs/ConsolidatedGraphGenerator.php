<?php

namespace App\Jobs;

use App\AffiliateReport;
use App\AffiliateRevenueTracker;
use App\CakeRevenue;
use App\Campaign;
use App\ClicksVsRegistrationStatistics;
use App\PageViewStatistics;
use App\RevenueTrackerCakeStatistic;
use App\Setting;
use Carbon\Carbon;
use DB;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;
use Mail;

class ConsolidatedGraphGenerator extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $date;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($date)
    {
        $this->date = $date;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $startTime = Carbon::now();

        $date = $this->date;

        Log::info('Consolidated Graph Start for '.$date);

        //get campaign ids
        $all_inbox_offer_id = env('ALL_INBOX_CAMPAIGN_ID', '286');
        $push_crew_id = env('PUSH_PRO_CAMPAIGN_ID', '1672');
        $cpawall_id = env('CPA_WALL_ENGAGEIQ_CAMPAIGN_ID', '94');
        $pd_external_id = env('EXTERNAL_PATH_PERMISSION_DATA_CAMPAIGN_ID', '283');
        $tbr_external_id = env('EXTERNAL_PATH_TIBURON_CAMPAIGN_ID', '290');
        $rex_external_id = env('EXTERNAL_PATH_REXADS_CAMPAIGN_ID', '289');
        $iff_external_id = env('EXTERNAL_PATH_IFFICIENT_CAMPAIGN_ID', '287');
        $ads_external_id = env('EXTERNAL_PATH_ADSMITH_CAMPAIGN_ID', '2127');
        $external_ids = [$pd_external_id, $tbr_external_id, $rex_external_id, $iff_external_id]; //, $ads_external_id

        $data = [];

        //Settings
        //get campaign type benchmarks
        //get default exit page campaign id
        //get admin email
        $settings = Setting::whereIn('code', ['campaign_type_benchmarks', 'default_admin_email'])
            ->select('code', 'description', 'string_value')->get();
        foreach ($settings as $setting) {
            if ($setting->code == 'campaign_type_benchmarks') {
                $benchmark = json_decode($setting->description, true);
                $default_exit_page = $benchmark[6];
            } else {
                $default_admin_email = $setting->string_value;
            }
        }
        $coreg_campaign_types = config('constants.COREG_CAMPAIGN_TYPES');

        //Campaigns
        //get campaign type
        // $campaigns_type = Campaign::pluck('campaign_type','id')->toArray();
        $campaigns_type = [];
        $campaigns_oid = [];
        $campaigns = Campaign::select('id', 'campaign_type', 'linkout_offer_id')->get();
        foreach ($campaigns as $cmp) {
            $campaigns_type[$cmp->id] = $cmp->campaign_type;
            $campaigns_oid[$cmp->id] = $cmp->linkout_offer_id;
        }

        //Affiliate Revenue Trackers Exit Page
        $has_rev_tracker_exit = AffiliateRevenueTracker::whereNotNull('exit_page_id')
            ->pluck('exit_page_id', 'revenue_tracker_id')->toArray();

        //get default exit page's offer_id
        $def_exit_oid = $campaigns_oid[$default_exit_page];

        //get offer ids of exit page campaigns
        $hrte_qry = $has_rev_tracker_exit;
        $hrte_qry[] = $default_exit_page;
        $hrte_qry = array_unique($hrte_qry);
        $exit_oids = [];

        foreach ($hrte_qry as $cid) {
            if (! is_null($campaigns_oid[$cid]) && $campaigns_oid[$cid] != '') {
                $exit_oids[] = $campaigns_oid[$cid];
            }
        }

        $exit_page_linkout_ids = Campaign::where('campaign_type', 6)->where('linkout_offer_id', '>', 0)->pluck('linkout_offer_id');
        $crevs = CakeRevenue::whereIn('offer_id', $exit_page_linkout_ids)->where('created_at', $date)
            ->select(DB::RAW('revenue_tracker_id, s1, s2, s3, s4, s5, SUM(revenue) as revenue'))->groupBy('revenue_tracker_id', 's1', 's2', 's3', 's4', 's5')->get();
        $exit_page_revenues = [];
        foreach ($crevs as $rev) {
            $exit_page_revenues[$rev->revenue_tracker_id][$rev->s1][$rev->s2][$rev->s3][$rev->s4][$rev->s5] = $rev->revenue;
        }

        //Affiliate Report
        $aff_rep = AffiliateReport::where('created_at', $date)
            ->select('revenue_tracker_id', 'campaign_id', 'revenue', 's1', 's2', 's3', 's4', 's5')->get();

        $total_revenues = [];
        $coreg_revenues = [];
        $mid_revenues = [];
        $cpa_revenues = [];
        foreach ($aff_rep as $ar) {
            $rev_tracker = $ar->revenue_tracker_id;
            $ctype = $campaigns_type[$ar->campaign_id];

            //get total revenue per revenue tracker
            if (! isset($total_revenues[$rev_tracker][$ar->s1][$ar->s2][$ar->s3][$ar->s4][$ar->s5])) {
                $total_revenues[$rev_tracker][$ar->s1][$ar->s2][$ar->s3][$ar->s4][$ar->s5] = 0;
            }
            $total_revenues[$rev_tracker][$ar->s1][$ar->s2][$ar->s3][$ar->s4][$ar->s5] += $ar->revenue;

            //get All Inbox
            if ($ar->campaign_id == $all_inbox_offer_id) {
                if (! isset($data[$rev_tracker][$ar->s1][$ar->s2][$ar->s3][$ar->s4][$ar->s5]['all_inbox_revenue'])) {
                    $data[$rev_tracker][$ar->s1][$ar->s2][$ar->s3][$ar->s4][$ar->s5]['all_inbox_revenue'] = 0;
                }
                $data[$rev_tracker][$ar->s1][$ar->s2][$ar->s3][$ar->s4][$ar->s5]['all_inbox_revenue'] += $ar->revenue;
            }

            //get Push Revenue
            elseif ($ar->campaign_id == $push_crew_id) {
                if (! isset($data[$rev_tracker][$ar->s1][$ar->s2][$ar->s3][$ar->s4][$ar->s5]['push_revenue'])) {
                    $data[$rev_tracker][$ar->s1][$ar->s2][$ar->s3][$ar->s4][$ar->s5]['push_revenue'] = 0;
                }
                $data[$rev_tracker][$ar->s1][$ar->s2][$ar->s3][$ar->s4][$ar->s5]['push_revenue'] += $ar->revenue;
            }

            //get CPAWall Revenue
            elseif ($ar->campaign_id == $cpawall_id) {
                if (! isset($cpa_revenues[$rev_tracker][$ar->s1][$ar->s2][$ar->s3][$ar->s4][$ar->s5])) {
                    $cpa_revenues[$rev_tracker][$ar->s1][$ar->s2][$ar->s3][$ar->s4][$ar->s5] = 0;
                }
                $cpa_revenues[$rev_tracker][$ar->s1][$ar->s2][$ar->s3][$ar->s4][$ar->s5] += $ar->revenue;
            }

            //get all midpath & separte revenue
            elseif (in_array($ar->campaign_id, $external_ids)) {
                if (! isset($mid_revenues[$rev_tracker][$ar->s1][$ar->s2][$ar->s3][$ar->s4][$ar->s5])) {
                    $mid_revenues[$rev_tracker][$ar->s1][$ar->s2][$ar->s3][$ar->s4][$ar->s5] = [
                        'total' => 0,
                        'pd' => 0,
                        'tbr' => 0,
                        'iff' => 0,
                        'rex' => 0,
                        //'ads'   => 0,
                    ];
                }

                $mid_revenues[$rev_tracker][$ar->s1][$ar->s2][$ar->s3][$ar->s4][$ar->s5]['total'] += $ar->revenue;

                switch ($ar->campaign_id) {
                    case $pd_external_id:
                        $mid_revenues[$rev_tracker][$ar->s1][$ar->s2][$ar->s3][$ar->s4][$ar->s5]['pd'] += $ar->revenue;
                        break;
                    case $tbr_external_id:
                        $mid_revenues[$rev_tracker][$ar->s1][$ar->s2][$ar->s3][$ar->s4][$ar->s5]['tbr'] += $ar->revenue;
                        break;
                    case $iff_external_id:
                        $mid_revenues[$rev_tracker][$ar->s1][$ar->s2][$ar->s3][$ar->s4][$ar->s5]['iff'] += $ar->revenue;
                        break;
                    case $rex_external_id:
                        $mid_revenues[$rev_tracker][$ar->s1][$ar->s2][$ar->s3][$ar->s4][$ar->s5]['rex'] += $ar->revenue;
                        break;
                        // case $ads_external_id:
                        //     $mid_revenues[$rev_tracker][$ar->s1][$ar->s2][$ar->s3][$ar->s4][$ar->s5]['ads'] += $ar->revenue;
                        //     break;
                }
            }

            //get all coreg revenue & coreg p1 to p4 revenue
            elseif (in_array($ctype, $coreg_campaign_types)) {
                if (! isset($coreg_revenues[$rev_tracker][$ar->s1][$ar->s2][$ar->s3][$ar->s4][$ar->s5])) {
                    $coreg_revenues[$rev_tracker][$ar->s1][$ar->s2][$ar->s3][$ar->s4][$ar->s5] = [
                        'total' => 0,
                        'mo1' => 0,
                        'mo2' => 0,
                        'mo3' => 0,
                        'mo4' => 0,
                    ];
                }
                $coreg_revenues[$rev_tracker][$ar->s1][$ar->s2][$ar->s3][$ar->s4][$ar->s5]['total'] += $ar->revenue;

                switch ($ctype) {
                    case 1:
                        $coreg_revenues[$rev_tracker][$ar->s1][$ar->s2][$ar->s3][$ar->s4][$ar->s5]['mo1'] += $ar->revenue;
                        break;
                    case 2:
                        $coreg_revenues[$rev_tracker][$ar->s1][$ar->s2][$ar->s3][$ar->s4][$ar->s5]['mo2'] += $ar->revenue;
                        break;
                    case 8:
                        $coreg_revenues[$rev_tracker][$ar->s1][$ar->s2][$ar->s3][$ar->s4][$ar->s5]['mo3'] += $ar->revenue;
                        break;
                    case 13:
                        $coreg_revenues[$rev_tracker][$ar->s1][$ar->s2][$ar->s3][$ar->s4][$ar->s5]['mo4'] += $ar->revenue;
                        break;
                }
            }
        }

        //Page View Statistics
        $view_stats = PageViewStatistics::where('created_at', $date)->get();
        $page_views = [];
        foreach ($view_stats as $views) {
            $rev_tracker = $views->revenue_tracker_id;
            if (! isset($page_views[$rev_tracker][$views->s1][$views->s2][$views->s3][$views->s4][$views->s5])) {
                $page_views[$rev_tracker][$views->s1][$views->s2][$views->s3][$views->s4][$views->s5] = [
                    'all_coreg' => 0,
                    'mo1' => 0,
                    'mo2' => 0,
                    'mo3' => 0,
                    'mo4' => 0,
                    'all_mid' => 0,
                    'pd' => 0,
                    'tbr' => 0,
                    'iff' => 0,
                    'rex' => 0,
                    //'ads'       => 0,
                    'cpa' => 0,
                    'exit' => 0,
                ];
            }
            $page_views[$rev_tracker][$views->s1][$views->s2][$views->s3][$views->s4][$views->s5]['all_coreg'] += $views->to1 + $views->to2 + $views->mo1 + $views->mo2 + $views->mo3 + $views->mo4 + $views->lfc1 + $views->lfc2;
            $page_views[$rev_tracker][$views->s1][$views->s2][$views->s3][$views->s4][$views->s5]['mo1'] += $views->mo1;
            $page_views[$rev_tracker][$views->s1][$views->s2][$views->s3][$views->s4][$views->s5]['mo2'] += $views->mo2;
            $page_views[$rev_tracker][$views->s1][$views->s2][$views->s3][$views->s4][$views->s5]['mo3'] += $views->mo3;
            $page_views[$rev_tracker][$views->s1][$views->s2][$views->s3][$views->s4][$views->s5]['mo4'] += $views->mo4;
            $page_views[$rev_tracker][$views->s1][$views->s2][$views->s3][$views->s4][$views->s5]['all_mid'] += $views->tbr1 + $views->pd + $views->tbr2 + $views->iff + $views->rex;
            $page_views[$rev_tracker][$views->s1][$views->s2][$views->s3][$views->s4][$views->s5]['pd'] += $views->pd;
            $page_views[$rev_tracker][$views->s1][$views->s2][$views->s3][$views->s4][$views->s5]['tbr'] += $views->tbr1 + $views->tbr2;
            $page_views[$rev_tracker][$views->s1][$views->s2][$views->s3][$views->s4][$views->s5]['iff'] += $views->iff;
            $page_views[$rev_tracker][$views->s1][$views->s2][$views->s3][$views->s4][$views->s5]['rex'] += $views->rex;
            // $page_views[$rev_tracker][$views->s1][$views->s2][$views->s3][$views->s4][$views->s5]['ads'] += $views->ads;
            $page_views[$rev_tracker][$views->s1][$views->s2][$views->s3][$views->s4][$views->s5]['cpa'] += $views->cpawall;
            $page_views[$rev_tracker][$views->s1][$views->s2][$views->s3][$views->s4][$views->s5]['exit'] += $views->exitpage;
        }

        // \Log::info($mid_revenues);
        // \Log::info($page_views);

        //CLicks vs Registration
        $cvrs = ClicksVsRegistrationStatistics::where('created_at', $date)
            ->select('revenue_tracker_id', 'clicks', 'registration_count', 's1', 's2', 's3', 's4', 's5')->get();
        foreach ($cvrs as $cvr) {
            $rev_tracker = $cvr->revenue_tracker_id;

            //get All Clicks - clicks
            $data[$rev_tracker][$cvr->s1][$cvr->s2][$cvr->s3][$cvr->s4][$cvr->s5]['all_clicks'] = $cvr->clicks;

            //get Survey Takers - registration_count
            $data[$rev_tracker][$cvr->s1][$cvr->s2][$cvr->s3][$cvr->s4][$cvr->s5]['survey_takers'] = $cvr->registration_count;

            //get Survey Takers / All Clicks
            $data[$rev_tracker][$cvr->s1][$cvr->s2][$cvr->s3][$cvr->s4][$cvr->s5]['survey_takers_per_clicks'] = ($cvr->clicks != 0 && $cvr->registration_count != 0) ? $cvr->registration_count / $cvr->clicks : 0;

            //get All Inbox / Survey Takers
            $all_inbox = isset($data[$rev_tracker][$cvr->s1][$cvr->s2][$cvr->s3][$cvr->s4][$cvr->s5]['all_inbox_revenue']) ? $data[$rev_tracker][$cvr->s1][$cvr->s2][$cvr->s3][$cvr->s4][$cvr->s5]['all_inbox_revenue'] : 0;
            $data[$rev_tracker][$cvr->s1][$cvr->s2][$cvr->s3][$cvr->s4][$cvr->s5]['all_inbox_per_survey_takers'] = ($all_inbox != 0 && $cvr->registration_count != 0) ? $all_inbox / $cvr->registration_count : 0;

            //get Push CPA Revenue / ST
            $push_revenue = isset($data[$rev_tracker][$cvr->s1][$cvr->s2][$cvr->s3][$cvr->s4][$cvr->s5]['push_revenue']) ? $data[$rev_tracker][$cvr->s1][$cvr->s2][$cvr->s3][$cvr->s4][$cvr->s5]['push_revenue'] : 0;
            $data[$rev_tracker][$cvr->s1][$cvr->s2][$cvr->s3][$cvr->s4][$cvr->s5]['push_cpa_revenue_per_survey_takers'] = ($push_revenue != 0 && $cvr->registration_count != 0) ? $push_revenue / $cvr->registration_count : 0;
        }

        //Revenue Tracker Cake Statistics
        $rtcks = RevenueTrackerCakeStatistic::where('created_at', $date)
            ->select('revenue_tracker_id', DB::RAW('SUM(payout) as payout'), 's1', 's2', 's3', 's4', 's5')
            ->groupBy('revenue_tracker_id', 's1', 's2', 's3', 's4', 's5')->get();
        foreach ($rtcks as $rtck) {
            $rev_tracker = $rtck->revenue_tracker_id;

            //get Cost - payout
            $data[$rev_tracker][$rtck->s1][$rtck->s2][$rtck->s3][$rtck->s4][$rtck->s5]['cost'] = $rtck->payout;

            //get Cost / All Clicks
            $all_clicks = isset($data[$rev_tracker][$rtck->s1][$rtck->s2][$rtck->s3][$rtck->s4][$rtck->s5]['all_clicks']) ? $data[$rev_tracker][$rtck->s1][$rtck->s2][$rtck->s3][$rtck->s4][$rtck->s5]['all_clicks'] : 0;
            $data[$rev_tracker][$rtck->s1][$rtck->s2][$rtck->s3][$rtck->s4][$rtck->s5]['cost_per_all_clicks'] = ($rtck->payout != 0 && $all_clicks != 0) ? $rtck->payout / $all_clicks : 0;
        }

        foreach ($total_revenues as $rev_tracker => $rt_data) {
            foreach ($rt_data as $s1 => $s1_data) {
                foreach ($s1_data as $s2 => $s2_data) {
                    foreach ($s2_data as $s3 => $s3_data) {
                        foreach ($s3_data as $s4 => $s4_data) {
                            foreach ($s4_data as $s5 => $revenue) {
                                //get Total Revenue
                                $data[$rev_tracker][$s1][$s2][$s3][$s4][$s5]['source_revenue'] = $revenue;

                                //get Margin
                                $cost = isset($data[$rev_tracker][$s1][$s2][$s3][$s4][$s5]['cost']) ? $data[$rev_tracker][$s1][$s2][$s3][$s4][$s5]['cost'] : 0;
                                $data[$rev_tracker][$s1][$s2][$s3][$s4][$s5]['margin'] = ($revenue != 0) ? ($revenue - $cost) / $revenue : 0;

                                //get Revenue / All Clicks
                                $all_clicks = isset($data[$rev_tracker][$s1][$s2][$s3][$s4][$s5]['all_clicks']) ? $data[$rev_tracker][$s1][$s2][$s3][$s4][$s5]['all_clicks'] : 0;
                                $data[$rev_tracker][$s1][$s2][$s3][$s4][$s5]['source_revenue_per_all_clicks'] = ($revenue != 0 && $all_clicks != 0) ? $revenue / $all_clicks : 0;

                                //get Revenue / Survey Takers
                                $survey_takers = isset($data[$rev_tracker][$s1][$s2][$s3][$s4][$s5]['survey_takers']) ? $data[$rev_tracker][$s1][$s2][$s3][$s4][$s5]['survey_takers'] : 0;
                                $data[$rev_tracker][$s1][$s2][$s3][$s4][$s5]['source_revenue_per_survey_takers'] = ($revenue != 0 && $survey_takers != 0) ? $revenue / $survey_takers : 0;
                            }
                        }
                    }
                }
            }
        }

        foreach ($coreg_revenues as $rev_tracker => $rt_data) {
            foreach ($rt_data as $s1 => $s1_data) {
                foreach ($s1_data as $s2 => $s2_data) {
                    foreach ($s2_data as $s3 => $s3_data) {
                        foreach ($s3_data as $s4 => $s4_data) {
                            foreach ($s4_data as $s5 => $co_rev) {
                                //get All Coreg Revenue
                                $data[$rev_tracker][$s1][$s2][$s3][$s4][$s5]['all_coreg_revenue'] = $co_rev['total'];

                                //get All Coreg Views
                                $all_coreg_views = isset($page_views[$rev_tracker][$s1][$s2][$s3][$s4][$s5]['all_coreg']) ? $page_views[$rev_tracker][$s1][$s2][$s3][$s4][$s5]['all_coreg'] : 0;
                                $data[$rev_tracker][$s1][$s2][$s3][$s4][$s5]['all_coreg_views'] = $all_coreg_views;

                                //get All Coreg Revenue / All Views
                                $data[$rev_tracker][$s1][$s2][$s3][$s4][$s5]['all_coreg_revenue_per_all_coreg_views'] = ($co_rev['total'] != 0 && $all_coreg_views != 0) ? $co_rev['total'] / $all_coreg_views : 0;

                                //get Coreg P1 Revenue
                                $data[$rev_tracker][$s1][$s2][$s3][$s4][$s5]['coreg_p1_revenue'] = $co_rev['mo1'];

                                //get Coreg P1 Views
                                $coreg1_views = isset($page_views[$rev_tracker][$s1][$s2][$s3][$s4][$s5]['mo1']) ? $page_views[$rev_tracker][$s1][$s2][$s3][$s4][$s5]['mo1'] : 0;
                                $data[$rev_tracker][$s1][$s2][$s3][$s4][$s5]['coreg_p1_views'] = $coreg1_views;

                                //get Coreg P1 Revenue / Views
                                $data[$rev_tracker][$s1][$s2][$s3][$s4][$s5]['coreg_p1_revenue_vs_views'] = ($co_rev['mo1'] != 0 && $coreg1_views != 0) ? $co_rev['mo1'] / $coreg1_views : 0;

                                //get Coreg P2 Revenue
                                $data[$rev_tracker][$s1][$s2][$s3][$s4][$s5]['coreg_p2_revenue'] = $co_rev['mo2'];

                                //get Coreg P2 Views
                                $coreg2_views = isset($page_views[$rev_tracker][$s1][$s2][$s3][$s4][$s5]['mo2']) ? $page_views[$rev_tracker][$s1][$s2][$s3][$s4][$s5]['mo2'] : 0;
                                $data[$rev_tracker][$s1][$s2][$s3][$s4][$s5]['coreg_p2_views'] = $coreg2_views;

                                //get Coreg P2 Revenue / Views
                                $data[$rev_tracker][$s1][$s2][$s3][$s4][$s5]['coreg_p2_revenue_vs_views'] = ($co_rev['mo2'] != 0 && $coreg2_views != 0) ? $co_rev['mo2'] / $coreg2_views : 0;

                                //get Coreg P3 Revenue
                                $data[$rev_tracker][$s1][$s2][$s3][$s4][$s5]['coreg_p3_revenue'] = $co_rev['mo3'];

                                //get Coreg P3 Views
                                $coreg3_views = isset($page_views[$rev_tracker][$s1][$s2][$s3][$s4][$s5]['mo3']) ? $page_views[$rev_tracker][$s1][$s2][$s3][$s4][$s5]['mo3'] : 0;
                                $data[$rev_tracker][$s1][$s2][$s3][$s4][$s5]['coreg_p3_views'] = $coreg3_views;

                                //get Coreg P3 Revenue / Views
                                $data[$rev_tracker][$s1][$s2][$s3][$s4][$s5]['coreg_p3_revenue_vs_views'] = ($co_rev['mo3'] != 0 && $coreg3_views != 0) ? $co_rev['mo3'] / $coreg3_views : 0;

                                //get Coreg P4 Revenue
                                $data[$rev_tracker][$s1][$s2][$s3][$s4][$s5]['coreg_p4_revenue'] = $co_rev['mo4'];

                                //get Coreg P4 Views
                                $coreg4_views = isset($page_views[$rev_tracker][$s1][$s2][$s3][$s4][$s5]['mo4']) ? $page_views[$rev_tracker][$s1][$s2][$s3][$s4][$s5]['mo4'] : 0;
                                $data[$rev_tracker][$s1][$s2][$s3][$s4][$s5]['coreg_p4_views'] = $coreg4_views;

                                //get Coreg P4 Revenue / Views
                                $data[$rev_tracker][$s1][$s2][$s3][$s4][$s5]['coreg_p4_revenue_vs_views'] = ($co_rev['mo4'] != 0 && $coreg4_views != 0) ? $co_rev['mo4'] / $coreg4_views : 0;
                            }
                        }
                    }
                }
            }
        }

        foreach ($mid_revenues as $rev_tracker => $rt_data) {
            foreach ($rt_data as $s1 => $s1_data) {
                foreach ($s1_data as $s2 => $s2_data) {
                    foreach ($s2_data as $s3 => $s3_data) {
                        foreach ($s3_data as $s4 => $s4_data) {
                            foreach ($s4_data as $s5 => $mid_rev) {
                                //get All Mid Paths Revenue
                                $data[$rev_tracker][$s1][$s2][$s3][$s4][$s5]['all_mp_revenue'] = $mid_rev['total'];

                                //get All Mid Paths Views
                                $all_mid_views = isset($page_views[$rev_tracker][$s1][$s2][$s3][$s4][$s5]['all_mid']) ? $page_views[$rev_tracker][$s1][$s2][$s3][$s4][$s5]['all_mid'] : 0;
                                $data[$rev_tracker][$s1][$s2][$s3][$s4][$s5]['all_mp_views'] = $all_mid_views;

                                //get All Mid Path Revenue / Views
                                $data[$rev_tracker][$s1][$s2][$s3][$s4][$s5]['mp_per_views'] = ($mid_rev['total'] != 0 && $all_mid_views != 0) ? $mid_rev['total'] / $all_mid_views : 0;

                                //get Permission Data Revenue
                                $data[$rev_tracker][$s1][$s2][$s3][$s4][$s5]['pd_revenue'] = $mid_rev['pd'];

                                //get Permission Data Views
                                $pd_views = isset($page_views[$rev_tracker][$s1][$s2][$s3][$s4][$s5]['pd']) ? $page_views[$rev_tracker][$s1][$s2][$s3][$s4][$s5]['pd'] : 0;
                                $data[$rev_tracker][$s1][$s2][$s3][$s4][$s5]['pd_views'] = $pd_views;

                                //get Permission Data Revenue / Views
                                $data[$rev_tracker][$s1][$s2][$s3][$s4][$s5]['pd_revenue_vs_views'] = ($mid_rev['pd'] != 0 && $pd_views != 0) ? $mid_rev['pd'] / $pd_views : 0;

                                //get Tiburon Revenue
                                $data[$rev_tracker][$s1][$s2][$s3][$s4][$s5]['tb_revenue'] = $mid_rev['tbr'];

                                //get Tiburon Views
                                $tbr_views = isset($page_views[$rev_tracker][$s1][$s2][$s3][$s4][$s5]['tbr']) ? $page_views[$rev_tracker][$s1][$s2][$s3][$s4][$s5]['tbr'] : 0;
                                $data[$rev_tracker][$s1][$s2][$s3][$s4][$s5]['tb_views'] = $tbr_views;

                                //get Tiburon Revenue / Views
                                $data[$rev_tracker][$s1][$s2][$s3][$s4][$s5]['tb_revenue_vs_views'] = ($mid_rev['tbr'] != 0 && $tbr_views != 0) ? $mid_rev['tbr'] / $tbr_views : 0;

                                //get Ifficent Revenue
                                $data[$rev_tracker][$s1][$s2][$s3][$s4][$s5]['iff_revenue'] = $mid_rev['iff'];

                                //get Ifficent Views
                                $iff_views = isset($page_views[$rev_tracker][$s1][$s2][$s3][$s4][$s5]['iff']) ? $page_views[$rev_tracker][$s1][$s2][$s3][$s4][$s5]['iff'] : 0;
                                $data[$rev_tracker][$s1][$s2][$s3][$s4][$s5]['iff_views'] = $iff_views;

                                //get Ifficent Revenue / Views
                                $data[$rev_tracker][$s1][$s2][$s3][$s4][$s5]['iff_revenue_vs_views'] = ($mid_rev['iff'] != 0 && $iff_views != 0) ? $mid_rev['iff'] / $iff_views : 0;

                                //get Rexadz Revenue
                                $data[$rev_tracker][$s1][$s2][$s3][$s4][$s5]['rexadz_revenue'] = $mid_rev['rex'];

                                //get Rexadz Views
                                $rex_views = isset($page_views[$rev_tracker][$s1][$s2][$s3][$s4][$s5]['rex']) ? $page_views[$rev_tracker][$s1][$s2][$s3][$s4][$s5]['rex'] : 0;
                                $data[$rev_tracker][$s1][$s2][$s3][$s4][$s5]['rexadz_views'] = $rex_views;

                                //get Rexadz Revenue / Views
                                $data[$rev_tracker][$s1][$s2][$s3][$s4][$s5]['rexadz_revenue_vs_views'] = ($mid_rev['rex'] != 0 && $rex_views != 0) ? $mid_rev['rex'] / $rex_views : 0;

                                // //get Adsmith Revenue
                                // $data[$rev_tracker][$s1][$s2][$s3][$s4][$s5]['adsmith_revenue'] = $mid_rev['ads'];

                                // //get Adsmith Views
                                // $ads_views = isset($page_views[$rev_tracker][$s1][$s2][$s3][$s4][$s5]['ads']) ? $page_views[$rev_tracker][$s1][$s2][$s3][$s4][$s5]['ads'] : 0;
                                // $data[$rev_tracker][$s1][$s2][$s3][$s4][$s5]['adsmith_views'] = $ads_views;

                                // //get Adsmith Revenue / Views
                                // $data[$rev_tracker][$s1][$s2][$s3][$s4][$s5]['adsmith_revenue_vs_views'] = ($mid_rev['ads'] != 0 && $ads_views != 0) ? $mid_rev['ads'] / $ads_views : 0;
                            }
                        }
                    }
                }
            }
        }

        foreach ($exit_page_revenues as $rev_tracker => $rt_data) {
            foreach ($rt_data as $s1 => $s1_data) {
                foreach ($s1_data as $s2 => $s2_data) {
                    foreach ($s2_data as $s3 => $s3_data) {
                        foreach ($s3_data as $s4 => $s4_data) {
                            foreach ($s4_data as $s5 => $revenue) {
                                //get Last Page Revenue
                                $data[$rev_tracker][$s1][$s2][$s3][$s4][$s5]['lsp_revenue'] = $revenue;

                                //get Last Page Views
                                $data[$rev_tracker][$s1][$s2][$s3][$s4][$s5]['lsp_views'] = isset($page_views[$rev_tracker][$s1][$s2][$s3][$s4][$s5]['exit']) ? $page_views[$rev_tracker][$s1][$s2][$s3][$s4][$s5]['exit'] : 0;

                                //get Last Page Revenue / Views
                                $data[$rev_tracker][$s1][$s2][$s3][$s4][$s5]['lsp_revenue_vs_views'] = ($data[$rev_tracker][$s1][$s2][$s3][$s4][$s5]['lsp_revenue'] != 0 && $data[$rev_tracker][$s1][$s2][$s3][$s4][$s5]['lsp_views'] != 0) ? $data[$rev_tracker][$s1][$s2][$s3][$s4][$s5]['lsp_revenue'] / $data[$rev_tracker][$s1][$s2][$s3][$s4][$s5]['lsp_views'] : 0;
                            }
                        }
                    }
                }
            }
        }

        foreach ($cpa_revenues as $rev_tracker => $rt_data) {
            foreach ($rt_data as $s1 => $s1_data) {
                foreach ($s1_data as $s2 => $s2_data) {
                    foreach ($s2_data as $s3 => $s3_data) {
                        foreach ($s3_data as $s4 => $s4_data) {
                            foreach ($s4_data as $s5 => $cpa_revenue) {
                                $last_page_revenue = isset($data[$rev_tracker][$s1][$s2][$s3][$s4][$s5]['lsp_revenue']) ? $data[$rev_tracker][$s1][$s2][$s3][$s4][$s5]['lsp_revenue'] : 0;
                                $last_page_views = isset($data[$rev_tracker][$s1][$s2][$s3][$s4][$s5]['lsp_views']) ? $data[$rev_tracker][$s1][$s2][$s3][$s4][$s5]['lsp_views'] : 0;
                                $last_page_rev_view = isset($data[$rev_tracker][$s1][$s2][$s3][$s4][$s5]['lsp_revenue_vs_views']) ? $data[$rev_tracker][$s1][$s2][$s3][$s4][$s5]['lsp_revenue_vs_views'] : 0;

                                //get CPA Revenue
                                $data[$rev_tracker][$s1][$s2][$s3][$s4][$s5]['cpa_revenue'] = $cpa_revenue - $last_page_revenue;

                                //get CPA Views
                                $cpa_views = isset($page_views[$rev_tracker][$s1][$s2][$s3][$s4][$s5]['cpa']) ? $page_views[$rev_tracker][$s1][$s2][$s3][$s4][$s5]['cpa'] : 0;
                                $data[$rev_tracker][$s1][$s2][$s3][$s4][$s5]['cpa_views'] = $cpa_views;

                                //get CPA Revenue / Views
                                $data[$rev_tracker][$s1][$s2][$s3][$s4][$s5]['cpa_revenue_per_views'] = ($cpa_views != 0 && $data[$rev_tracker][$s1][$s2][$s3][$s4][$s5]['cpa_revenue'] != 0) ? $data[$rev_tracker][$s1][$s2][$s3][$s4][$s5]['cpa_revenue'] / $cpa_views : 0;

                                //get CPA / Survey Taker
                                $survey_takers = isset($data[$rev_tracker][$s1][$s2][$s3][$s4][$s5]['survey_takers']) ? $data[$rev_tracker][$s1][$s2][$s3][$s4][$s5]['survey_takers'] : 0;
                                $data[$rev_tracker][$s1][$s2][$s3][$s4][$s5]['cpa_per_survey_takers'] = ($data[$rev_tracker][$s1][$s2][$s3][$s4][$s5]['cpa_revenue'] != 0 && $survey_takers != 0) ? $data[$rev_tracker][$s1][$s2][$s3][$s4][$s5]['cpa_revenue'] / $survey_takers : 0;
                            }
                        }
                    }
                }
            }
        }

        $inserts = [];
        $created_at = Carbon::parse($date)->toDateTimeString();
        $updated_at = Carbon::now()->toDateTimeString();
        $counter = 0;
        //Save to Consolidated Graph
        foreach ($data as $rev_tracker => $rt_data) {
            foreach ($rt_data as $s1 => $s1_data) {
                foreach ($s1_data as $s2 => $s2_data) {
                    foreach ($s2_data as $s3 => $s3_data) {
                        foreach ($s3_data as $s4 => $s4_data) {
                            foreach ($s4_data as $s5 => $rev_data) {
                                $counter++;

                                $all_mp_views = 0;
                                if (isset($rev_data['all_mp_views'])) {
                                    $all_mp_views = $rev_data['all_mp_views'];
                                } elseif (isset($page_views[$rev_tracker][$s1][$s2][$s3][$s4][$s5]['all_mid'])) {
                                    $all_mp_views = $page_views[$rev_tracker][$s1][$s2][$s3][$s4][$s5]['all_mid'];
                                }

                                $pd_views = 0;
                                if (isset($rev_data['pd_views'])) {
                                    $pd_views = $rev_data['pd_views'];
                                } elseif (isset($page_views[$rev_tracker][$s1][$s2][$s3][$s4][$s5]['pd'])) {
                                    $pd_views = $page_views[$rev_tracker][$s1][$s2][$s3][$s4][$s5]['pd'];
                                }

                                $tb_views = 0;
                                if (isset($rev_data['tb_views'])) {
                                    $tb_views = $rev_data['tb_views'];
                                } elseif (isset($page_views[$rev_tracker][$s1][$s2][$s3][$s4][$s5]['tbr'])) {
                                    $tb_views = $page_views[$rev_tracker][$s1][$s2][$s3][$s4][$s5]['tbr'];
                                }

                                $iff_views = 0;
                                if (isset($rev_data['iff_views'])) {
                                    $iff_views = $rev_data['iff_views'];
                                } elseif (isset($page_views[$rev_tracker][$s1][$s2][$s3][$s4][$s5]['iff'])) {
                                    $iff_views = $page_views[$rev_tracker][$s1][$s2][$s3][$s4][$s5]['iff'];
                                }

                                $rexadz_views = 0;
                                if (isset($rev_data['rexadz_views'])) {
                                    $rexadz_views = $rev_data['rexadz_views'];
                                } elseif (isset($page_views[$rev_tracker][$s1][$s2][$s3][$s4][$s5]['rex'])) {
                                    $rexadz_views = $page_views[$rev_tracker][$s1][$s2][$s3][$s4][$s5]['rex'];
                                }

                                // $adsmith_views = 0;
                                // if(isset($rev_data['adsmith_views'])) $adsmith_views = $rev_data['adsmith_views'];
                                // else if(isset($page_views[$rev_tracker][$s1][$s2][$s3][$s4][$s5]['ads'])) $adsmith_views = $page_views[$rev_tracker][$s1][$s2][$s3][$s4][$s5]['ads'];

                                $cpa_views = 0;
                                if (isset($rev_data['cpa_views'])) {
                                    $cpa_views = $rev_data['cpa_views'];
                                } elseif (isset($page_views[$rev_tracker][$s1][$s2][$s3][$s4][$s5]['cpa'])) {
                                    $cpa_views = $page_views[$rev_tracker][$s1][$s2][$s3][$s4][$s5]['cpa'];
                                }

                                $lsp_views = 0;
                                if (isset($rev_data['lsp_views'])) {
                                    $lsp_views = $rev_data['lsp_views'];
                                } elseif (isset($page_views[$rev_tracker][$s1][$s2][$s3][$s4][$s5]['exit'])) {
                                    $lsp_views = $page_views[$rev_tracker][$s1][$s2][$s3][$s4][$s5]['exit'];
                                }

                                $inserts[] = [
                                    'revenue_tracker_id' => $rev_tracker,
                                    's1' => $s1,
                                    's2' => $s2,
                                    's3' => $s3,
                                    's4' => $s4,
                                    's5' => $s5,
                                    'created_at' => $created_at,
                                    'updated_at' => $updated_at,
                                    'survey_takers' => isset($rev_data['survey_takers']) ? $rev_data['survey_takers'] : 0,
                                    'source_revenue' => isset($rev_data['source_revenue']) ? $rev_data['source_revenue'] : 0,
                                    'source_revenue_per_survey_takers' => isset($rev_data['source_revenue_per_survey_takers']) ? $rev_data['source_revenue_per_survey_takers'] : 0,
                                    'all_clicks' => isset($rev_data['all_clicks']) ? $rev_data['all_clicks'] : 0,
                                    'source_revenue_per_all_clicks' => isset($rev_data['source_revenue_per_all_clicks']) ? $rev_data['source_revenue_per_all_clicks'] : 0,
                                    'margin' => isset($rev_data['margin']) ? $rev_data['margin'] : 0,
                                    'cpa_revenue' => isset($rev_data['cpa_revenue']) ? $rev_data['cpa_revenue'] : 0,
                                    'cpa_views' => $cpa_views,
                                    'cpa_revenue_per_views' => isset($rev_data['cpa_revenue_per_views']) ? $rev_data['cpa_revenue_per_views'] : 0,
                                    'lsp_revenue' => isset($rev_data['lsp_revenue']) ? $rev_data['lsp_revenue'] : 0,
                                    'lsp_views' => $lsp_views,
                                    'lsp_revenue_vs_views' => isset($rev_data['lsp_revenue_vs_views']) ? $rev_data['lsp_revenue_vs_views'] : 0,
                                    'pd_revenue' => isset($rev_data['pd_revenue']) ? $rev_data['pd_revenue'] : 0,
                                    'pd_views' => $pd_views,
                                    'pd_revenue_vs_views' => isset($rev_data['pd_revenue_vs_views']) ? $rev_data['pd_revenue_vs_views'] : 0,
                                    'tb_revenue' => isset($rev_data['tb_revenue']) ? $rev_data['tb_revenue'] : 0,
                                    'tb_views' => $tb_views,
                                    'tb_revenue_vs_views' => isset($rev_data['tb_revenue_vs_views']) ? $rev_data['tb_revenue_vs_views'] : 0,
                                    'iff_revenue' => isset($rev_data['iff_revenue']) ? $rev_data['iff_revenue'] : 0,
                                    'iff_views' => $iff_views,
                                    'iff_revenue_vs_views' => isset($rev_data['iff_revenue_vs_views']) ? $rev_data['iff_revenue_vs_views'] : 0,
                                    'rexadz_revenue' => isset($rev_data['rexadz_revenue']) ? $rev_data['rexadz_revenue'] : 0,
                                    'rexadz_views' => $rexadz_views,
                                    'rexadz_revenue_vs_views' => isset($rev_data['rexadz_revenue_vs_views']) ? $rev_data['rexadz_revenue_vs_views'] : 0,
                                    'adsmith_revenue' => 0, //isset($rev_data['adsmith_revenue']) ? $rev_data['adsmith_revenue'] : 0,
                                    'adsmith_views' => 0, //$adsmith_views,
                                    'adsmith_revenue_vs_views' => 0, //isset($rev_data['adsmith_revenue_vs_views']) ? $rev_data['adsmith_revenue_vs_views'] : 0,
                                    'all_inbox_revenue' => isset($rev_data['all_inbox_revenue']) ? $rev_data['all_inbox_revenue'] : 0,
                                    'coreg_p1_revenue' => isset($rev_data['coreg_p1_revenue']) ? $rev_data['coreg_p1_revenue'] : 0,
                                    'coreg_p1_views' => isset($rev_data['coreg_p1_views']) ? $rev_data['coreg_p1_views'] : 0,
                                    'coreg_p1_revenue_vs_views' => isset($rev_data['coreg_p1_revenue_vs_views']) ? $rev_data['coreg_p1_revenue_vs_views'] : 0,
                                    'coreg_p2_revenue' => isset($rev_data['coreg_p2_revenue']) ? $rev_data['coreg_p2_revenue'] : 0,
                                    'coreg_p2_views' => isset($rev_data['coreg_p2_views']) ? $rev_data['coreg_p2_views'] : 0,
                                    'coreg_p2_revenue_vs_views' => isset($rev_data['coreg_p2_revenue_vs_views']) ? $rev_data['coreg_p2_revenue_vs_views'] : 0,
                                    'coreg_p3_revenue' => isset($rev_data['coreg_p3_revenue']) ? $rev_data['coreg_p3_revenue'] : 0,
                                    'coreg_p3_views' => isset($rev_data['coreg_p3_views']) ? $rev_data['coreg_p3_views'] : 0,
                                    'coreg_p3_revenue_vs_views' => isset($rev_data['coreg_p3_revenue_vs_views']) ? $rev_data['coreg_p3_revenue_vs_views'] : 0,
                                    'coreg_p4_revenue' => isset($rev_data['coreg_p4_revenue']) ? $rev_data['coreg_p4_revenue'] : 0,
                                    'coreg_p4_views' => isset($rev_data['coreg_p4_views']) ? $rev_data['coreg_p4_views'] : 0,
                                    'coreg_p4_revenue_vs_views' => isset($rev_data['coreg_p4_revenue_vs_views']) ? $rev_data['coreg_p4_revenue_vs_views'] : 0,
                                    'survey_takers_per_clicks' => isset($rev_data['survey_takers_per_clicks']) ? $rev_data['survey_takers_per_clicks'] : 0,
                                    'cpa_per_survey_takers' => isset($rev_data['cpa_per_survey_takers']) ? $rev_data['cpa_per_survey_takers'] : 0,
                                    'mp_per_views' => isset($rev_data['mp_per_views']) ? $rev_data['mp_per_views'] : 0,
                                    'cost_per_all_clicks' => isset($rev_data['cost_per_all_clicks']) ? $rev_data['cost_per_all_clicks'] : 0,
                                    'cost' => isset($rev_data['cost']) ? $rev_data['cost'] : 0,
                                    'all_inbox_per_survey_takers' => isset($rev_data['all_inbox_per_survey_takers']) ? $rev_data['all_inbox_per_survey_takers'] : 0,
                                    'all_coreg_revenue' => isset($rev_data['all_coreg_revenue']) ? $rev_data['all_coreg_revenue'] : 0,
                                    'all_coreg_views' => isset($rev_data['all_coreg_views']) ? $rev_data['all_coreg_views'] : 0,
                                    'all_coreg_revenue_per_all_coreg_views' => isset($rev_data['all_coreg_revenue_per_all_coreg_views']) ? $rev_data['all_coreg_revenue_per_all_coreg_views'] : 0,
                                    'all_mp_revenue' => isset($rev_data['all_mp_revenue']) ? $rev_data['all_mp_revenue'] : 0,
                                    'all_mp_views' => $all_mp_views,
                                    'push_revenue' => isset($rev_data['push_revenue']) ? $rev_data['push_revenue'] : 0,
                                    'push_cpa_revenue_per_survey_takers' => isset($rev_data['push_cpa_revenue_per_survey_takers']) ? $rev_data['push_cpa_revenue_per_survey_takers'] : 0,
                                ];
                            }
                        }
                    }
                }
            }
        }

        $insert_chunk = array_chunk($inserts, 1000);

        //DELETE Existing Consolidated Data
        Log::info('Deleting Existing Consolidated Graph Data for '.$date);
        DB::table('consolidated_graph')->where(DB::RAW('created_at'), $date)->delete();

        //INSERT New Consolidated Data
        Log::info('Inserting New Consolidated Graph Data for '.$date);
        Log::info('Attempting to insert '.$counter.' number of data.');
        foreach ($insert_chunk as $chunk) {
            DB::table('consolidated_graph')->insert($chunk);
            Log::info('Inserted total: '.count($chunk));
        }
        Log::info('Insert Done.');
        $endTime = Carbon::now();

        if ($counter > 0) {
            //Send Email
            Mail::send(
                'emails.consolidated_graph',
                [
                    'revTrackerIDs' => array_keys($data),
                    'dateExecuted' => $date,
                    'executionDuration' => $startTime->diffInSeconds($endTime).' seconds',
                ],
                function ($mail) use ($default_admin_email) {
                    $mail->from('noreply@engageiq.com')
                        ->to($default_admin_email)
                        ->subject('Job: Consolidated Graph Data');
                }
            );
        }

        Log::info('Consolidated Graph End for '.$date);
    }
}
