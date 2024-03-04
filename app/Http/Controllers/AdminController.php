<?php

namespace App\Http\Controllers;

use App\Action;
use App\ActionRole;
use App\Advertiser;
use App\Affiliate;
use App\AffiliateReport;
use App\AffiliateRevenueTracker;
use App\Campaign;
use App\CampaignRevenueViewStatistic;
use App\Category;
use App\ClicksVsRegistrationStatistics;
use App\Commands\GetUserActionPermission;
use App\Cron;
use App\Events\UserActionEvent;
use App\FilterType;
use App\Http\Requests\AdminSettingRequest;
use App\Http\Services\RejectedLeads as RejectedLeadsHandler;
use App\Http\Services\UserActionLogger;
use App\Lead;
use App\LeadArchive;
use App\LeadDataAdv;
use App\LeadDataCsv;
use App\LeadDuplicate;
use App\LeadMessage;
use App\LeadSentResult;
use App\PageViewStatistics;
use App\Path;
use App\Setting;
use App\User;
use App\UserActionLog;
use Bus;
use Cache;
use Carbon\Carbon;
use DB;
use Excel;
use GetAdvertisersCompanyIDPair;
use GetInternalAffiliatesCompanyIDPair;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Request as Input;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Log;
use Session;
use Illuminate\Support\Facades\Storage;;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AdminController extends Controller
{
    protected $dashboardPermitted = false;

    protected $contactsPermitted = false;

    protected $affiliatesPermitted = false;

    protected $campaignsPermitted = false;

    protected $advertisersPermitted = false;

    protected $filterTypesPermitted = false;

    protected $reportsAndStatisticsPermitted = false;

    protected $searchLeadsPermitted = false;

    protected $revenueStatisticsPermitted = false;

    protected $revenueTrackersPermitted = false;

    protected $galleryPermitted = false;

    protected $zipMasterPermitted = false;

    protected $surveyTakersPermitted = false;

    protected $usersAndRolesPermitted = false;

    protected $settingsPermitted = false;

    protected $surveyPathsPermitted = false;

    protected $logger;

    public function __construct(UserActionLogger $logger)
    {
        $this->middleware('auth');
        $this->middleware('admin');
        $this->middleware('restrict_access', ['only' => [
            'dashboard',
            'contacts',
            'affiliates',
            'campaigns',
            'advertisers',
            'filterTypes',
            'searchLeads',
            'revenueStatistics',
            'revenueTrackers',
            'gallery',
            'zip_master',
            'categories',
            'apply_to_run',
            'survey_takers',
            'surveyPaths',
            'settings',
            'prepopStatistics',
        ]]);

        $user = auth()->user();
        $this->logger = $logger;

        $this->dashboardPermitted = Bus::dispatch(new GetUserActionPermission($user, 'access_dashboard'));
        $this->contactsPermitted = Bus::dispatch(new GetUserActionPermission($user, 'access_contacts'));
        $this->affiliatesPermitted = Bus::dispatch(new GetUserActionPermission($user, 'access_affiliates'));
        $this->campaignsPermitted = Bus::dispatch(new GetUserActionPermission($user, 'access_campaigns'));
        $this->advertisersPermitted = Bus::dispatch(new GetUserActionPermission($user, 'access_advertisers'));
        $this->filterTypesPermitted = Bus::dispatch(new GetUserActionPermission($user, 'access_filter_types'));
        $this->reportsAndStatisticsPermitted = Bus::dispatch(new GetUserActionPermission($user, 'access_reports_and_statistics'));
        $this->searchLeadsPermitted = Bus::dispatch(new GetUserActionPermission($user, 'access_search_leads'));
        $this->revenueStatisticsPermitted = Bus::dispatch(new GetUserActionPermission($user, 'access_revenue_statistics'));
        $this->revenueTrackersPermitted = Bus::dispatch(new GetUserActionPermission($user, 'access_revenue_trackers'));
        $this->galleryPermitted = Bus::dispatch(new GetUserActionPermission($user, 'access_gallery'));
        $this->zipMasterPermitted = Bus::dispatch(new GetUserActionPermission($user, 'access_zip_master'));
        $this->surveyTakersPermitted = Bus::dispatch(new GetUserActionPermission($user, 'access_survey_takers'));
        $this->usersAndRolesPermitted = Bus::dispatch(new GetUserActionPermission($user, 'access_users_and_roles'));
        $this->settingsPermitted = Bus::dispatch(new GetUserActionPermission($user, 'access_settings'));
        $this->surveyPathsPermitted = Bus::dispatch(new GetUserActionPermission($user, 'access_survey_paths'));
    }

    /**
     * Index page for admin user
     */
    public function index(): RedirectResponse
    {
        //determine what page should be displayed base on the main section permission
        if ($this->dashboardPermitted || auth()->user()->isSuperUser()) {
            return redirect()->action([\App\Http\Controllers\AdminController::class, 'dashboard']);
        } elseif ($this->contactsPermitted) {
            return redirect()->action([\App\Http\Controllers\AdminController::class, 'contacts']);
        } elseif ($this->affiliatesPermitted) {
            return redirect()->action([\App\Http\Controllers\AdminController::class, 'affiliates']);
        } elseif ($this->campaignsPermitted) {
            return redirect()->action([\App\Http\Controllers\AdminController::class, 'campaigns']);
        } elseif ($this->advertisersPermitted) {
            return redirect()->action([\App\Http\Controllers\AdminController::class, 'advertisers']);
        } elseif ($this->filterTypesPermitted) {
            return redirect()->action([\App\Http\Controllers\AdminController::class, 'filterTypes']);
        } elseif ($this->reportsAndStatisticsPermitted) {
            if ($this->searchLeadsPermitted) {
                return redirect()->action([\App\Http\Controllers\AdminController::class, 'searchLeads']);
            } elseif ($this->revenueStatisticsPermitted) {
                return redirect()->action([\App\Http\Controllers\AdminController::class, 'revenueStatistics']);
            }
        } elseif ($this->revenueTrackersPermitted) {
            return redirect()->action([\App\Http\Controllers\AdminController::class, 'revenueTrackers']);
        } elseif ($this->galleryPermitted) {
            return redirect()->action([\App\Http\Controllers\AdminController::class, 'gallery']);
        } elseif ($this->zipMasterPermitted) {
            return redirect()->action([\App\Http\Controllers\AdminController::class, 'zip_master']);
        } elseif ($this->surveyTakersPermitted) {
            return redirect()->action([\App\Http\Controllers\AdminController::class, 'survey_takers']);
        } elseif ($this->usersAndRolesPermitted) {
            return redirect()->action([\App\Http\Controllers\RoleController::class, 'index']);
        } elseif ($this->surveyPathsPermitted) {
            return redirect()->action([\App\Http\Controllers\AdminController::class, 'surveyPaths']);
        } elseif ($this->settingsPermitted) {
            return redirect()->action([\App\Http\Controllers\AdminController::class, 'settings']);
        }

        return redirect()->action([\App\Http\Controllers\AdminController::class, 'dashboard']);
    }

    /**
     * Dashboard page
     */
    public function dashboard(): View
    {
        return view('admin.dashboard');
    }

    /**
     * Survey takers page
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function survey_takers(): View
    {
        //this is to determine if fresh visit in order to sort records by created at
        Session::forget('no_visit');

        return view('admin.survey_takers');
    }

    /**
     * Different lead counts
     */
    public function leadCounts(): JsonResponse
    {
        $selectSQL = "SELECT (CASE WHEN lead_status=0 THEN '#failed-leads' WHEN lead_status=1 THEN '#success-leads' WHEN lead_status=2 THEN '#rejected-leads' WHEN lead_status=3 THEN '#pending-leads' ELSE '#nah' END) AS lead_type, COUNT(id) AS lead_count FROM leads GROUP BY lead_status ORDER BY lead_status";
        $counts = DB::select($selectSQL);
        $total = 0;

        foreach ($counts as $val) {
            $total += $val->lead_count;
        }

        $totalArray = ['lead_type' => '#total-leads', 'lead_count' => $total];

        array_push($counts, $totalArray);

        return response()->json($counts);
    }

    /**
     * Top 10 campaigns by revenue yesterday
     */
    public function topTenCampaignsByRevenueYesterday(): JsonResponse
    {

        if (Cache::has('topTenCampaignsByRevenueYesterday')) {
            $campaigns = Cache::get('topTenCampaignsByRevenueYesterday');
        } else {
            $yesterday = Carbon::now()->subDay()->toDateString();
            $selectSQL = "SELECT leads.campaign_id,CONCAT(campaigns.name,'(',leads.campaign_id,')') AS campaign, SUM(leads.received) AS revenue, leads.created_at FROM leads INNER JOIN campaigns ON leads.campaign_id=campaigns.id WHERE leads.lead_status=1 AND DATE(leads.created_at) = DATE('".$yesterday."') GROUP BY leads.campaign_id ORDER BY revenue DESC LIMIT 10";
            $campaigns = DB::select($selectSQL);
            $expiresAt = Carbon::now()->addDay();
            Cache::put('topTenCampaignsByRevenueYesterday', $campaigns, $expiresAt);
        }

        return response()->json($campaigns);
    }

    /**
     * Top 10 campaigns by revenue from the current week
     */
    public function topTenCampaignsByRevenueForCurrentWeek(): JsonResponse
    {
        if (Cache::has('topTenCampaignsByRevenueForCurrentWeek')) {
            $campaigns = Cache::get('topTenCampaignsByRevenueForCurrentWeek');
        } else {
            // $currentDate = Carbon::now()->toDateString();
            Carbon::setWeekStartsAt(Carbon::SUNDAY);
            Carbon::setWeekEndsAt(Carbon::SATURDAY);
            $startOfWeek = Carbon::now()->startOfWeek();
            $endOfWeek = Carbon::now()->endOfWeek();

            $selectSQL = "SELECT 
                leads.campaign_id,
                CONCAT(campaigns.name,'(',leads.campaign_id,')') AS campaign, 
                SUM(leads.received) AS revenue, 
                leads.created_at 
                FROM leads 
                INNER JOIN campaigns ON leads.campaign_id=campaigns.id 
                WHERE leads.lead_status=1 
                AND leads.created_at BETWEEN '$startOfWeek' AND '$endOfWeek'
                GROUP BY leads.campaign_id 
                UNION
                SELECT 
                leads_archive.campaign_id,
                CONCAT(campaigns.name,'(',leads_archive.campaign_id,')') AS campaign, 
                SUM(leads_archive.received) AS revenue, 
                leads_archive.created_at 
                FROM leads_archive 
                INNER JOIN campaigns ON leads_archive.campaign_id=campaigns.id 
                WHERE leads_archive.lead_status=1 
                AND leads_archive.created_at BETWEEN '$startOfWeek' AND '$endOfWeek'
                GROUP BY leads_archive.campaign_id 
                ORDER BY revenue DESC LIMIT 10";

            // $selectSQL = "SELECT leads.campaign_id,CONCAT(campaigns.name,'(',leads.campaign_id,')') AS campaign, (SUM(leads.received) - SUM(leads.payout)) AS profit, SUM(leads.received) AS revenue, SUM(leads.payout) AS cost, leads.created_at FROM leads INNER JOIN campaigns ON leads.campaign_id=campaigns.id WHERE leads.lead_status=1 AND YEARWEEK(DATE(leads.created_at)) = YEARWEEK(DATE('".$currentDate."')) GROUP BY leads.campaign_id ORDER BY profit DESC, revenue DESC LIMIT 10";
            $campaigns = DB::select($selectSQL);
            $expiresAt = Carbon::now()->addMinutes(15);
            Cache::put('topTenCampaignsByRevenueForCurrentWeek', $campaigns, $expiresAt);
        }

        return response()->json($campaigns);
    }

    /**
     * Top 10 campaigns by revenue from the current month
     */
    public function topTenCampaignsByRevenueForCurrentMonth(): JsonResponse
    {
        if (Cache::has('topTenCampaignsByRevenueForCurrentMonth')) {
            $campaigns = Cache::get('topTenCampaignsByRevenueForCurrentMonth');
        } else {
            //$currentDate = Carbon::now()->toDateString();
            $startOfMonth = Carbon::now()->startOfMonth();
            $endOfMonth = Carbon::now()->endOfMonth();
            $selectSQL = "SELECT 
                leads.campaign_id,
                CONCAT(campaigns.name,'(',leads.campaign_id,')') AS campaign, 
                SUM(leads.received) AS revenue, 
                leads.created_at 
                FROM leads 
                INNER JOIN campaigns ON leads.campaign_id=campaigns.id 
                WHERE leads.lead_status=1 
                AND leads.created_at BETWEEN '$startOfMonth' AND '$endOfMonth'
                GROUP BY leads.campaign_id 
                UNION
                SELECT 
                leads_archive.campaign_id,
                CONCAT(campaigns.name,'(',leads_archive.campaign_id,')') AS campaign, 
                SUM(leads_archive.received) AS revenue, 
                leads_archive.created_at 
                FROM leads_archive 
                INNER JOIN campaigns ON leads_archive.campaign_id=campaigns.id 
                WHERE leads_archive.lead_status=1 
                AND leads_archive.created_at BETWEEN '$startOfMonth' AND '$endOfMonth'
                GROUP BY leads_archive.campaign_id 
                ORDER BY revenue DESC LIMIT 10";

            // $selectSQL = "SELECT leads.campaign_id,CONCAT(campaigns.name,'(',leads.campaign_id,')') AS campaign, (SUM(leads.received) - SUM(leads.payout)) AS profit, SUM(leads.received) AS revenue, SUM(leads.payout) AS cost, leads.created_at FROM leads INNER JOIN campaigns ON leads.campaign_id=campaigns.id WHERE leads.lead_status=1 AND MONTH(DATE(leads.created_at)) = MONTH(DATE('".$currentDate."')) GROUP BY leads.campaign_id ORDER BY profit DESC, revenue DESC LIMIT 10";
            $campaigns = DB::select($selectSQL);
            $expiresAt = Carbon::now()->addMinutes(15);
            Cache::put('topTenCampaignsByRevenueForCurrentMonth', $campaigns, $expiresAt);
        }

        return response()->json($campaigns);
    }

    /**
     * Top 10 affiliates by revenue yesterday
     */
    public function topTenAffiliatesByRevenueYesterday(): JsonResponse
    {
        if (Cache::has('topTenAffiliatesByRevenueYesterday')) {
            $affiliates = Cache::get('topTenAffiliatesByRevenueYesterday');
        } else {
            $yesterday = Carbon::now()->subDay()->toDateString();

            $selectSQL = "SELECT leads.affiliate_id,CONCAT(affiliates.company,'(',leads.affiliate_id,')') AS affiliate, SUM(leads.received) AS revenue, leads.created_at FROM leads INNER JOIN affiliates ON leads.affiliate_id=affiliates.id WHERE leads.lead_status=1 AND DATE(leads.created_at) = DATE('".$yesterday."') GROUP BY leads.affiliate_id ORDER BY revenue DESC LIMIT 10";
            $affiliates = DB::select($selectSQL);
            $expiresAt = Carbon::now()->addDay();
            Cache::put('topTenAffiliatesByRevenueYesterday', $affiliates, $expiresAt);
        }

        return response()->json($affiliates);
    }

    /**
     * Top 10 affiliates by revenue in this current week
     */
    public function topTenAffiliatesByRevenueForCurrentWeek(): JsonResponse
    {
        if (Cache::has('topTenAffiliatesByRevenueForCurrentWeek')) {
            $affiliates = Cache::get('topTenAffiliatesByRevenueForCurrentWeek');
        } else {
            Carbon::setWeekStartsAt(Carbon::SUNDAY);
            Carbon::setWeekEndsAt(Carbon::SATURDAY);
            $startOfWeek = Carbon::now()->startOfWeek();
            $endOfWeek = Carbon::now()->endOfWeek();
            // $currentDate = Carbon::now()->toDateString();

            $selectSQL = "SELECT leads.affiliate_id,CONCAT(affiliates.company,'(',leads.affiliate_id,')') AS affiliate, SUM(leads.received) AS revenue, leads.created_at FROM leads 
            INNER JOIN affiliates ON leads.affiliate_id=affiliates.id 
            WHERE leads.lead_status=1 AND leads.created_at BETWEEN '$startOfWeek' AND '$endOfWeek' GROUP BY leads.affiliate_id 
            UNION
            SELECT leads_archive.affiliate_id,CONCAT(affiliates.company,'(',leads_archive.affiliate_id,')') AS affiliate,  SUM(leads_archive.received) AS revenue, leads_archive.created_at FROM leads_archive 
            INNER JOIN affiliates ON leads_archive.affiliate_id=affiliates.id 
            WHERE leads_archive.lead_status=1 AND leads_archive.created_at BETWEEN '$startOfWeek' AND '$endOfWeek' GROUP BY leads_archive.affiliate_id 
            ORDER BY revenue DESC LIMIT 10";
            // $selectSQL = "SELECT leads.affiliate_id,CONCAT(affiliates.company,'(',leads.affiliate_id,')') AS affiliate, (SUM(leads.received) - SUM(leads.payout)) AS profit, SUM(leads.received) AS revenue, SUM(leads.payout) AS cost, leads.created_at FROM leads INNER JOIN affiliates ON leads.affiliate_id=affiliates.id WHERE leads.lead_status=1 AND YEARWEEK(DATE(leads.created_at)) = YEARWEEK(DATE('".$currentDate."')) GROUP BY leads.affiliate_id ORDER BY profit DESC, revenue DESC LIMIT 10";
            $affiliates = DB::select($selectSQL);
            $expiresAt = Carbon::now()->addMinutes(15);
            Cache::put('topTenAffiliatesByRevenueForCurrentWeek', $affiliates, $expiresAt);
        }

        return response()->json($affiliates);
    }

    /**
     * Top 10 affiliates by revenue in this current week
     */
    public function topTenAffiliatesByRevenueForCurrentMonth(): JsonResponse
    {
        if (Cache::has('topTenAffiliatesByRevenueForCurrentMonth')) {
            $affiliates = Cache::get('topTenAffiliatesByRevenueForCurrentMonth');
        } else {
            // $currentDate = Carbon::now()->toDateString();
            $startOfMonth = Carbon::now()->startOfMonth();
            $endOfMonth = Carbon::now()->endOfMonth();

            $selectSQL = "SELECT leads.affiliate_id,CONCAT(affiliates.company,'(',leads.affiliate_id,')') AS affiliate, 
            (SUM(leads.received) - SUM(leads.payout)) AS profit, SUM(leads.received) AS revenue, 
            SUM(leads.payout) AS cost, leads.created_at FROM leads 
            INNER JOIN affiliates ON leads.affiliate_id=affiliates.id 
            WHERE leads.lead_status=1 AND leads.created_at BETWEEN '$startOfMonth' AND '$endOfMonth' GROUP BY leads.affiliate_id 
            UNION
            SELECT leads_archive.affiliate_id,CONCAT(affiliates.company,'(',leads_archive.affiliate_id,')') AS affiliate, 
            (SUM(leads_archive.received) - SUM(leads_archive.payout)) AS profit, SUM(leads_archive.received) AS revenue, 
            SUM(leads_archive.payout) AS cost, leads_archive.created_at FROM leads_archive 
            INNER JOIN affiliates ON leads_archive.affiliate_id=affiliates.id 
            WHERE leads_archive.lead_status=1 AND leads_archive.created_at BETWEEN '$startOfMonth' AND '$endOfMonth' GROUP BY leads_archive.affiliate_id 
            ORDER BY profit DESC, revenue DESC LIMIT 10";
            // $selectSQL = "SELECT leads.affiliate_id,CONCAT(affiliates.company,'(',leads.affiliate_id,')') AS affiliate, (SUM(leads.received) - SUM(leads.payout)) AS profit, SUM(leads.received) AS revenue, SUM(leads.payout) AS cost, leads.created_at FROM leads INNER JOIN affiliates ON leads.affiliate_id=affiliates.id WHERE leads.lead_status=1 AND MONTH(DATE(leads.created_at)) = MONTH(DATE('".$currentDate."')) GROUP BY leads.affiliate_id ORDER BY profit DESC, revenue DESC LIMIT 10";
            $affiliates = DB::select($selectSQL);
            $expiresAt = Carbon::now()->addMinutes(15);
            Cache::put('topTenAffiliatesByRevenueForCurrentMonth', $affiliates, $expiresAt);
        }

        return response()->json($affiliates);
    }

    /**
     * Will return active campaigns
     */
    public function activeCampaigns()
    {
        return view('admin.activecampaigns');
        // $campaigns = Campaign::activeCampaigns()->get();
        // return response()->json($campaigns);
    }

    /**
     * Admin page route for contacts
     */
    public function contacts(): View
    {
        return view('admin.contacts');
    }

    /**
     * Admin page route for affiliates
     */
    public function affiliates(): View
    {
        //$affiliates = Affiliate::all();
        $states = config('constants.US_STATES_ABBR');
        $types = config('constants.AFFILIATE_TYPE');
        //return view('admin.affiliates',compact('affiliates','states'));
        return view('admin.affiliates', compact('states', 'types'));
    }

    /**
     * Admin page route for advertisers
     */
    public function advertisers(): View
    {
        //$advertisers = Advertiser::all()->toArray();
        $advertisers = Advertiser::all();
        $states = config('constants.US_STATES_ABBR');
        //dd($advertisers);

        return view('admin.advertisers', compact('advertisers', 'states'));
    }

    /**
     * Admin page route for campaigns
     */
    public function campaigns(): View
    {
        //$campaigns = Campaign::all();
        //$campaigns = Campaign::take(5)->get();
        $totalCampaignCount = Campaign::count();

        //$advertisers = Advertiser::pluck('company','id')->toArray();
        //$affiliates = Affiliate::orderBy('company','asc')->pluck('company','id')->toArray();
        /*
        $advertiser_campaign = Campaign::groupBy('advertiser_id')->pluck('advertiser_id');
        $advertisers = Advertiser::select(DB::raw('CONCAT(company," (",id,")") AS name'),'id')
                                   ->whereIn('id',$advertiser_campaign)
                                   ->where('status',1)
                                   ->get()
                                   ->pluck('name','id')->toArray();
        */

        $advertisers = [null => ''] + Bus::dispatch(new GetAdvertisersCompanyIDPair());

        $affiliates = Affiliate::select('id', DB::raw('CONCAT(id, " - ",company) AS company_name'))
            ->orderBy('id', 'asc')
            ->pluck('company_name', 'id')->toArray();

        $lead_types = config('constants.LEAD_CAP_TYPES');
        $campaign_types = config('constants.CAMPAIGN_TYPES');
        $campaign_statuses = config('constants.CAMPAIGN_STATUS');

        $filter_types = FilterType::select('id', DB::raw('CONCAT(type, " - ",name) AS filter_name'))
            ->orderBy('filter_name', 'asc')
            ->pluck('filter_name', 'id')->toArray();

        $categories = Category::where('status', '=', 1)->pluck('name', 'id')->toArray();

        $forceEdit = Action::where('code', 'use_force_edit_default_received_payout')->first();
        $forceUserToUpdate = 0;
        if ($forceEdit) {
            $exist = ActionRole::where('role_id', auth()->user()->role_id)->where('action_id', $forceEdit->id)->where('permitted', 1)->count();
            if ($exist > 0) {
                $forceUserToUpdate = 1;
            }
        }

        //return view('admin.campaigns',compact('advertisers','lead_types','campaigns','affiliates','filter_types','campaign_types'));
        return view('admin.campaigns', compact('advertisers', 'lead_types', 'totalCampaignCount', 'affiliates', 'filter_types', 'campaign_types', 'categories', 'campaign_statuses', 'forceUserToUpdate'));
    }

    /**
     * Zip master page
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function zip_master(): View
    {
        return view('admin.zip_master');
    }

    /**
     * Zip Code page
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function zip_codes(): View
    {
        return view('admin.zip_codes');
    }

    /**
     * Filter type page
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function filterTypes(): View
    {
        //$filterTypes = FilterType::all();
        return view('admin.filtertypes');
    }

    public function pageOptinRateStats(Request $request): View
    {
        //get campaign type order
        $order = Setting::where('code', 'stack_path_campaign_type_order')->first();
        $order_type = json_decode($order->string_value);
        // $exit_key = array_search(6, $order_type); //6 = exit page
        // $order_type = array_slice($order_type,0,$exit_key + 1);
        session(['pors_campaign_order_type' => $order_type]);

        //get campaign type benchmark
        $benchmark = Setting::where('code', 'campaign_type_benchmarks')->first();
        $benchmarks = json_decode($benchmark->description, true);
        session(['pors_default_benchmark' => $benchmarks]);

        //get campaigns per campaign type
        $all_campaigns = Campaign::orderBy('name', 'ASC')->select('id', 'campaign_type', 'name', 'linkout_offer_id')->get();
        $campaigns = [];
        $linkout_campaigns = [];
        foreach ($all_campaigns as $c) {
            $campaigns[$c->campaign_type][$c->id] = $c->name;
            if ($c->campaign_type == 5 || $c->campaign_type == 6) {
                $linkout_campaigns[$c->id] = $c->linkout_offer_id;
            }
        }

        return view('admin.pageoptinratestats', compact('order_type', 'campaigns', 'benchmarks', 'linkout_campaigns'));
    }

    public function pageOptIn(Request $request): JsonResponse
    {
        $columns = [
            'page_view_statistics.created_at',
            'affiliates.company',
            'page_view_statistics.affiliate_id',
            'page_view_statistics.s1',
            'page_view_statistics.s2',
            'page_view_statistics.s3',
            'page_view_statistics.s4',
            'page_view_statistics.s5',
        ];
        $inputs = $request->all();
        $pvs_campaign_types = config('constants.PAGE_VIEW_STATISTICS_CAMPAIGN_TYPES');
        $external_cols = config('constants.EXTERNAL_CAMPAIGN_PAGE_VIEW_STATISTICS_COL');
        $hiddenCols = [];
        $totalCount = 0;
        $statsData = [];
        $hasDataCT = [];
        // Log::info($inputs);

        //Date From and To
        if ($inputs['date_range'] != '') {
            //Date Range
            switch ($inputs['date_range']) {
                case 'yesterday':
                    $inputs['date_from'] = Carbon::yesterday()->toDateString();
                    $inputs['date_to'] = Carbon::yesterday()->toDateString();
                    break;
                case 'week':
                    $inputs['date_from'] = Carbon::now()->startOfWeek()->toDateString();
                    $inputs['date_to'] = Carbon::now()->endOfWeek()->toDateString();
                    break;
                case 'month':
                    $inputs['date_from'] = Carbon::now()->startOfMonth()->toDateString();
                    $inputs['date_to'] = Carbon::now()->endOfMonth()->toDateString();
                    break;
                case 'last_month':
                    $inputs['date_from'] = Carbon::now()->subMonth()->startOfMonth()->toDateString();
                    $inputs['date_to'] = Carbon::now()->subMonth()->endOfMonth()->toDateString();
                    break;
            }
        } else {
            //get the date if no date_from and date_to
            $inputs['date_from'] = $inputs['date_from'] != '' ? $inputs['date_from'] : Carbon::yesterday()->toDateString();
            $inputs['date_to'] = $inputs['date_to'] != '' ? $inputs['date_to'] : $inputs['date_from'];
        }

        $isMultiDate = $inputs['date_from'] != $inputs['date_to'] && $inputs['group_by'] != 'day';

        if (isset($inputs['order'])) {
            $inputs['order_col'] = $columns[$inputs['order'][0]['column']];
        }

        //benchmark;
        $def_bm_count = 13; //default benchmark total count
        if (! isset($inputs['benchmarks'])) {
            $benchmarks = session('pors_default_benchmark');
            if ($benchmarks == null) {
                $benchmark = Setting::where('code', 'campaign_type_benchmarks')->first();
                $benchmarks = json_decode($benchmark->description, true);
                session(['pors_default_benchmark' => $benchmarks]);
            }
        } else {
            $input_bm = array_filter($inputs['benchmarks']);
            if (count($input_bm) < $def_bm_count) {
                $default_bms = session('pors_default_benchmark');
                foreach ($default_bms as $type => $def_bm) {
                    if (! isset($input_bm[$type])) {
                        Log::info($type);
                    }
                    if (isset($input_bm[$type])) {
                        $benchmarks[$type] = $input_bm[$type];
                    } else {
                        $benchmarks[$type][] = $def_bm;
                    }
                    // $benchmarks[$type] = isset($input_bm[$type]) ? $input_bm[$type] : $def_bm;
                }
            } else {
                $benchmarks = $input_bm;
            }
        }
        $inputs['benchmarks'] = $benchmarks;

        $benchmark_types = [];
        $forAR = [];
        foreach ($benchmarks as $type => $bm) {
            foreach ($bm as $id) {
                $benchmark_types[$id] = $type;
                if ($type != 5 && $type != 6 && $id != 'all') {
                    $forArr[] = $id;
                } else {
                    if ($id != '' || ! is_null($id) && $id != 'all') {
                        $forCR[] = $id;
                    }
                }
            }
        }
        $inputs['benchmark_types'] = $benchmark_types;
        $inputs['benchmark_campaign_ids'] = $forArr; //for affiliate report query
        $inputs['benchmark_offer_ids'] = $forCR; //for cake revenue query

        $offer_ids = array_filter($inputs['offer_ids']);
        $forCR = [];
        $offer_types = [];
        foreach ($offer_ids as $type => $oids) {
            foreach ($oids as $oid) {
                $forCR[] = $oid;
                $offer_types[$oid] = $type;
            }
        }
        $inputs['offer_ids'] = $forCR; //for cake revenue query
        $inputs['offer_types'] = $offer_types;

        session(['pors_inputs' => $inputs]);

        //get campaign type order
        if (session()->has('pors_campaign_order_type')) {
            $campaign_type_active = session('pors_campaign_order_type');
        } else {
            $setting = Setting::where('code', 'stack_path_campaign_type_order')->first();
            $campaign_type_active = json_decode($setting->string_value);
        }

        // DB::enableQueryLog();
        // DB::connection('secondary')->enableQueryLog();
        //Get page views
        $statistics = PageViewStatistics::pageOptInRateStats(false, $inputs)->get();
        // Log::info(DB::getQueryLog());
        if (isset($statistics[0])) { //check if not empty

            //get total count of page view statistics
            $total_qry = PageViewStatistics::pageOptInRateStats(true, $inputs)->toSql();
            $total = DB::select('SELECT COUNT(*) as total FROM ('.$total_qry.') t');
            $totalCount = $total[0]->total;

            //Get external campaign's revenue and coreg campaign's success and reject count
            $affiliate_reports = \App\AffiliateReport::pageOptInRateStats($inputs['benchmark_campaign_ids'], $inputs)->get();
            $campaign_catalog = [];
            $external_catalog = [];
            foreach ($affiliate_reports as $aff_rep) {
                $date = $isMultiDate ? $inputs['date_from'] : $aff_rep->created_at;
                // $cmp_type = array_search($aff_rep->campaign_id, $benchmarks);
                $cmp_type = $benchmark_types[$aff_rep->campaign_id];
                // $cmp_type = $aff_rep->campaign_type;
                $date = $aff_rep->created_at;
                $s1 = $inputs['sib_s1'] == 'true' ? $aff_rep->s1 : '';
                $s2 = $inputs['sib_s2'] == 'true' ? $aff_rep->s2 : '';
                $s3 = $inputs['sib_s3'] == 'true' ? $aff_rep->s3 : '';
                $s4 = $inputs['sib_s4'] == 'true' ? $aff_rep->s4 : '';
                $s5 = $aff_rep->s5;
                if ($cmp_type == 4) {
                    if (! isset($external_catalog[$aff_rep->revenue_tracker_id][$s1][$s2][$s3][$s4][$s5][$date])) {
                        $external_catalog[$aff_rep->revenue_tracker_id][$s1][$s2][$s3][$s4][$s5][$date] = 0;
                    }
                    $external_catalog[$aff_rep->revenue_tracker_id][$s1][$s2][$s3][$s4][$s5][$date] += $aff_rep->revenue;
                } else {
                    //[campaign_type][revenue_tracker][date] = total
                    if (! isset($campaign_catalog[$cmp_type][$aff_rep->revenue_tracker_id][$s1][$s2][$s3][$s4][$s5][$date])) {
                        $campaign_catalog[$cmp_type][$aff_rep->revenue_tracker_id][$s1][$s2][$s3][$s4][$s5][$date] = 0;
                    }
                    $campaign_catalog[$cmp_type][$aff_rep->revenue_tracker_id][$s1][$s2][$s3][$s4][$s5][$date] += $aff_rep->lead_count + $aff_rep->reject_count + $aff_rep->failed_count;
                }
            }

            //Get link out and exit page campaign revenues
            $cake_revenues = \App\CakeRevenue::pageOptInRateReport($inputs['offer_ids'], $inputs)->get();
            //cataloging cake
            //[revenue_tracker][campaign_type][created_at] = revenue
            $cake_catalog = [];
            foreach ($cake_revenues as $cr) {
                $s1 = $inputs['sib_s1'] == 'true' ? $cr->s1 : '';
                $s2 = $inputs['sib_s2'] == 'true' ? $cr->s2 : '';
                $s3 = $inputs['sib_s3'] == 'true' ? $cr->s3 : '';
                $s4 = $inputs['sib_s4'] == 'true' ? $cr->s4 : '';
                $s5 = $cr->s5;
                $date = $isMultiDate ? $inputs['date_from'] : $cr->created_at;
                $ct = $inputs['offer_types'][$cr->offer_id]; //campaign_type
                if (! isset($cake_catalog[$cr->revenue_tracker_id][$s1][$s2][$s3][$s4][$s5][$ct][$date])) {
                    $cake_catalog[$cr->revenue_tracker_id][$s1][$s2][$s3][$s4][$s5][$ct][$date] = 0;
                }
                $cake_catalog[$cr->revenue_tracker_id][$s1][$s2][$s3][$s4][$s5][$ct][$date] += $cr->revenue;
            }

            foreach ($statistics as $stat) {

                $s1 = $inputs['sib_s1'] == 'true' ? $stat->s1 : '';
                $s2 = $inputs['sib_s2'] == 'true' ? $stat->s2 : '';
                $s3 = $inputs['sib_s3'] == 'true' ? $stat->s3 : '';
                $s4 = $inputs['sib_s4'] == 'true' ? $stat->s4 : '';
                $s5 = $stat->s5;

                //get publisher
                if ($stat->affiliate_id == $stat->revenue_tracker_id) {
                    $publisher = $stat->affiliate_id;
                } else {
                    $publisher = $stat->revenue_tracker_id.' ['.$stat->affiliate_id.']';
                }

                $created_at = $isMultiDate ? '' : $stat->created_at;
                $data = [
                    $created_at,
                    $stat->company,
                    $publisher,
                    $s1,
                    $s2,
                    $s3,
                    $s4,
                    $s5,
                ];

                $date = $isMultiDate ? $inputs['date_from'] : $stat->created_at;
                //get total leads and rate of benchmarks per campaign type

                foreach ($campaign_type_active as $type) {
                    if ($pvs_campaign_types[$type] != '') {
                        if ($type == 4) { //if external
                            // $ct = $external_cols[$benchmarks[4][1]];
                            $views = 0;
                            foreach ($benchmarks[4] as $ec) {
                                if ($ec != 'all') {
                                    $ct = $external_cols[$ec];
                                    $views += $stat->$ct;
                                }
                            }
                        } else {
                            $ct = $pvs_campaign_types[$type]; //get campaign type column
                            $views = $stat->$ct; //get stat value
                        }

                        //hiddenColumnChecker
                        if ($views > 0 && ! in_array($type, $hasDataCT)) {
                            $hasDataCT[] = $type;
                        }

                        if (isset($benchmarks[$type])) {
                            if ($type == 4) { //get revenue from affiliate reports
                                $total = isset($external_catalog[$stat->revenue_tracker_id][$s1][$s2][$s3][$s4][$s5][$date]) ? $external_catalog[$stat->revenue_tracker_id][$s1][$s2][$s3][$s4][$s5][$date] : 0;
                            } elseif ($type == 5 || $type == 6) { //get cake revenue
                                //[revenue_tracker][offer_id] = revenue
                                $total = isset($cake_catalog[$stat->revenue_tracker_id][$s1][$s2][$s3][$s4][$s5][$type][$date]) ? $cake_catalog[$stat->revenue_tracker_id][$s1][$s2][$s3][$s4][$s5][$type][$date] : 0;
                            } else { //get lead count
                                $total = isset($campaign_catalog[$type][$stat->revenue_tracker_id][$s1][$s2][$s3][$s4][$s5][$date]) ? $campaign_catalog[$type][$stat->revenue_tracker_id][$s1][$s2][$s3][$s4][$s5][$date] : 0;
                            }

                            $rate = 0;
                            if ($views != 0 && $total != 0) {
                                $rate = round(($total / $views) * 100);
                            }
                            $rate .= '%';
                        } else {
                            $rate = '0%';
                        }

                    } else {
                        $rate = '0%';
                    }

                    $data[] = $rate;
                }
                $statsData[] = $data;
            }
        }

        //determine if campaign type has any data
        foreach ($campaign_type_active as $type) {
            if (! in_array($type, $hasDataCT)) {
                $hiddenCols[] = $type;
            }
        }
        session(['pors_hidden_cols' => $hiddenCols]);

        $dateDisplay = $inputs['date_from'];
        if ($dateDisplay != $inputs['date_to']) {
            $dateDisplay .= ' to '.$inputs['date_to'];
        }

        $responseData = [
            'draw' => intval($inputs['draw']),
            'recordsTotal' => $totalCount,
            'recordsFiltered' => $totalCount,
            'data' => $statsData,
            'hiddenCols' => $hiddenCols,
            'benchmarks' => $benchmarks,
            'date' => $dateDisplay,
        ];

        // Log::info(DB::getQueryLog());
        // Log::info(DB::connection('secondary')->getQueryLog());
        // Log::info($statsData);

        return response()->json($responseData, 200);
    }

    public function downloadPageOptinRateStats(Request $request)
    {
        $inputs = session('pors_inputs');
        //unset to display all
        unset($inputs['start']);
        unset($inputs['length']);
        $benchmarks = $inputs['benchmarks'];
        $benchmark_types = $inputs['benchmark_types'];
        $offer_ids = $inputs['offer_ids'];
        $hiddenCols = session('pors_hidden_cols');
        $campaign_types = config('constants.CAMPAIGN_TYPES');
        $pvs_campaign_types = config('constants.PAGE_VIEW_STATISTICS_CAMPAIGN_TYPES');
        $external_cols = config('constants.EXTERNAL_CAMPAIGN_PAGE_VIEW_STATISTICS_COL');
        $external_campaigns = config('constants.EXTERNAL_CAMPAIGN_AFFILIATE_REPORT_ID');
        $external_ids = array_keys($external_campaigns);
        $no_leads = [4, 5, 6];
        $statsData = [];
        $isMultiDate = $inputs['date_from'] != $inputs['date_to'] && $inputs['group_by'] != 'day';

        if (session()->has('pors_campaign_order_type')) {
            $campaign_type_active = session('pors_campaign_order_type');
        } else {
            $setting = Setting::where('code', 'stack_path_campaign_type_order')->first();
            $campaign_type_active = json_decode($setting->string_value);
        }

        //get benchmark names
        $campaigns = Campaign::whereIn('id', array_keys($inputs['benchmark_types']))->pluck('name', 'id');

        $activeTypes = [];
        foreach ($campaign_type_active as $type) {
            if (! in_array($type, $hiddenCols)) {
                $activeTypes[] = $type;
                if ($type == 4) {
                    $activeTypes[] = $type;
                    $activeTypes[] = $type;
                    $activeTypes[] = $type;
                    $activeTypes[] = 'external_combine';
                }
            }
        }

        // DB::enableQueryLog();
        $statistics = PageViewStatistics::pageOptInRateStats(false, $inputs)->get(); //get views
        if (isset($statistics[0])) {
            //Get external campaign's revenue and coreg campaign's success and reject count
            $forAF = array_merge($inputs['benchmark_campaign_ids'], $external_ids);
            $affiliate_reports = \App\AffiliateReport::pageOptInRateStats($forAF, $inputs)->get();
            $campaign_catalog = [];
            $external_catalog = [];
            foreach ($affiliate_reports as $aff_rep) {
                $s1 = $inputs['sib_s1'] == 'true' ? $aff_rep->s1 : '';
                $s2 = $inputs['sib_s2'] == 'true' ? $aff_rep->s2 : '';
                $s3 = $inputs['sib_s3'] == 'true' ? $aff_rep->s3 : '';
                $s4 = $inputs['sib_s4'] == 'true' ? $aff_rep->s4 : '';
                $s5 = $aff_rep->s5;
                $date = $isMultiDate ? $inputs['date_from'] : $aff_rep->created_at;
                if (in_array($aff_rep->campaign_id, $external_ids)) {
                    $external_catalog[$aff_rep->revenue_tracker_id][$s1][$s2][$s3][$s4][$s5][$aff_rep->campaign_id][$date] = $aff_rep->revenue;
                } else {
                    // $cmp_type = array_search($aff_rep->campaign_id, $benchmarks);
                    $cmp_type = $benchmark_types[$aff_rep->campaign_id];

                    if (! isset($campaign_catalog[$cmp_type][$aff_rep->revenue_tracker_id][$s1][$s2][$s3][$s4][$s5][1][$date])) {
                        $campaign_catalog[$cmp_type][$aff_rep->revenue_tracker_id][$s1][$s2][$s3][$s4][$s5][1][$date] = 0;
                        $campaign_catalog[$cmp_type][$aff_rep->revenue_tracker_id][$s1][$s2][$s3][$s4][$s5][2][$date] = 0;
                        $campaign_catalog[$cmp_type][$aff_rep->revenue_tracker_id][$s1][$s2][$s3][$s4][$s5][0][$date] = 0;
                    }

                    $campaign_catalog[$cmp_type][$aff_rep->revenue_tracker_id][$s1][$s2][$s3][$s4][$s5][1][$date] += $aff_rep->lead_count; //success count
                    $campaign_catalog[$cmp_type][$aff_rep->revenue_tracker_id][$s1][$s2][$s3][$s4][$s5][2][$date] += $aff_rep->reject_count; //reject count
                    $campaign_catalog[$cmp_type][$aff_rep->revenue_tracker_id][$s1][$s2][$s3][$s4][$s5][0][$date] += $aff_rep->failed_count; //failed count
                }
            }

            //get revenue of linkouts and exitpages
            $cake_revenues = \App\CakeRevenue::pageOptInRateReport($inputs['offer_ids'], $inputs)->get();
            $cake_catalog = [];
            foreach ($cake_revenues as $cr) {
                $date = $isMultiDate ? $inputs['date_from'] : $cr->created_at;
                $s1 = $inputs['sib_s1'] == 'true' ? $cr->s1 : '';
                $s2 = $inputs['sib_s2'] == 'true' ? $cr->s2 : '';
                $s3 = $inputs['sib_s3'] == 'true' ? $cr->s3 : '';
                $s4 = $inputs['sib_s4'] == 'true' ? $cr->s4 : '';
                $s5 = $cr->s5;
                $ct = $inputs['offer_types'][$cr->offer_id]; //campaign_type
                if (! isset($cake_catalog[$cr->revenue_tracker_id][$s1][$s2][$s3][$s4][$s5][$ct][$date])) {
                    $cake_catalog[$cr->revenue_tracker_id][$s1][$s2][$s3][$s4][$s5][$ct][$date] = 0;
                }
                $cake_catalog[$cr->revenue_tracker_id][$s1][$s2][$s3][$s4][$s5][$ct][$date] += $cr->revenue;
            }

            // return $external_catalog;
            $stats_catalog = [];
            foreach ($statistics as $stat) {
                /*
                    [revenue_tracker][s1][s2][s3][s4][s5] = [
                        [company]
                        [affiliate_id]
                        [date] => [
                            [the_date] => [
                                [campaign_type_id] => [
                                    [
                                        [success]
                                        [rejects]
                                            or
                                        [revenue]
                                        ----
                                        [views]
                                    ]
                                    //if external
                                    [campaign_id] => [
                                        [revenue]
                                        [views]
                                    ]
                                ]
                            ]
                        ]
                    ]

                    ex [revenue_tracker][s1][s2][s3][s4][s5][date]['2017-11-15'][1][sucess]
                */

                $s1 = $inputs['sib_s1'] == 'true' ? $stat->s1 : '';
                $s2 = $inputs['sib_s2'] == 'true' ? $stat->s2 : '';
                $s3 = $inputs['sib_s3'] == 'true' ? $stat->s3 : '';
                $s4 = $inputs['sib_s4'] == 'true' ? $stat->s4 : '';
                $s5 = $stat->s5;

                //initialize
                if (! isset($stats_catalog[$stat->revenue_tracker_id][$s1][$s2][$s3][$s4][$s5])) {
                    $stats_catalog[$stat->revenue_tracker_id][$s1][$s2][$s3][$s4][$s5] = [
                        'company' => $stat->company,
                        'affiliate' => $stat->affiliate_id,
                        'date' => [],
                    ];
                }

                $date = $isMultiDate ? $inputs['date_from'] : $stat->created_at;

                //get total leads and rate of benchmarks per campaign type
                foreach ($campaign_type_active as $type) {
                    if ($pvs_campaign_types[$type] != '') {
                        if ($type == 4) {
                            foreach ($external_cols as $ext_id => $ext_col) {
                                $stats_catalog[$stat->revenue_tracker_id][$s1][$s2][$s3][$s4][$s5]['date'][$date][$type][$ext_id]['views'] = $stat->$ext_col;
                                $rev = isset($external_catalog[$stat->revenue_tracker_id][$s1][$s2][$s3][$s4][$s5][$ext_id][$date]) ? $external_catalog[$stat->revenue_tracker_id][$s1][$s2][$s3][$s4][$s5][$ext_id][$date] : 0;
                                $stats_catalog[$stat->revenue_tracker_id][$s1][$s2][$s3][$s4][$s5]['date'][$date][$type][$ext_id]['revenue'] = sprintf('%.2f', $rev);
                            }
                        } else {
                            $ct = $pvs_campaign_types[$type];
                            $views = $stat->$ct; //get stat value

                            //initialize
                            if (! isset($stats_catalog[$stat->revenue_tracker_id][$s1][$s2][$s3][$s4][$s5]['campaign_type'][$type])) {
                                $stats_catalog[$stat->revenue_tracker_id][$s1][$s2][$s3][$s4][$s5]['date'][$date][$type]['views'] = $views;
                                if (in_array($type, $no_leads)) {
                                    $rev = isset($cake_catalog[$stat->revenue_tracker_id][$s1][$s2][$s3][$s4][$s5][$type][$date]) ? $cake_catalog[$stat->revenue_tracker_id][$s1][$s2][$s3][$s4][$s5][$type][$date] : 0;
                                    $stats_catalog[$stat->revenue_tracker_id][$s1][$s2][$s3][$s4][$s5]['date'][$date][$type]['revenue'] = sprintf('%.2f', $rev);
                                } else {
                                    $stats_catalog[$stat->revenue_tracker_id][$s1][$s2][$s3][$s4][$s5]['date'][$date][$type]['success'] = 0;
                                    $stats_catalog[$stat->revenue_tracker_id][$s1][$s2][$s3][$s4][$s5]['date'][$date][$type]['rejected'] = 0;
                                    $stats_catalog[$stat->revenue_tracker_id][$s1][$s2][$s3][$s4][$s5]['date'][$date][$type]['failed'] = 0;
                                }
                            }

                            if (isset($benchmarks[$type])) {
                                if (in_array($type, $no_leads)) {
                                    //different
                                } else {
                                    $stats_catalog[$stat->revenue_tracker_id][$s1][$s2][$s3][$s4][$s5]['date'][$date][$type]['success'] = isset($campaign_catalog[$type][$stat->revenue_tracker_id][$s1][$s2][$s3][$s4][$s5][1][$date]) ? $campaign_catalog[$type][$stat->revenue_tracker_id][$s1][$s2][$s3][$s4][$s5][1][$date] : 0;
                                    $stats_catalog[$stat->revenue_tracker_id][$s1][$s2][$s3][$s4][$s5]['date'][$date][$type]['rejected'] = isset($campaign_catalog[$type][$stat->revenue_tracker_id][$s1][$s2][$s3][$s4][$s5][2][$date]) ? $campaign_catalog[$type][$stat->revenue_tracker_id][$s1][$s2][$s3][$s4][$s5][2][$date] : 0;
                                    $stats_catalog[$stat->revenue_tracker_id][$s1][$s2][$s3][$s4][$s5]['date'][$date][$type]['failed'] = isset($campaign_catalog[$type][$stat->revenue_tracker_id][$s1][$s2][$s3][$s4][$s5][0][$date]) ? $campaign_catalog[$type][$stat->revenue_tracker_id][$s1][$s2][$s3][$s4][$s5][0][$date] : 0;
                                }
                            }
                        }
                    }
                }
            }

            $external_key = array_search(4, $campaign_type_active);
            if ($external_key) {
                array_splice($campaign_type_active, $external_key + 1, 0, ['external_combine']);
            }

            foreach ($stats_catalog as $rt_id => $rtData) {
                foreach ($rtData as $s1 => $s1Data) {
                    foreach ($s1Data as $s2 => $s2Data) {
                        foreach ($s2Data as $s3 => $s3Data) {
                            foreach ($s3Data as $s4 => $s4Data) {
                                foreach ($s4Data as $s5 => $sData) {
                                    foreach ($sData['date'] as $date => $dData) {
                                        //get publisher
                                        if ($sData['affiliate'] == $rt_id) {
                                            $publisher = $rt_id;
                                        } else {
                                            $publisher = $rt_id.' ['.$sData['affiliate'].']';
                                        }

                                        if ($isMultiDate) {
                                            $created_at = '';
                                        } else {
                                            $created_at = $date;
                                        }

                                        $data = [
                                            $created_at,
                                            $sData['company'],
                                            $publisher,
                                            $s1,
                                            $s2,
                                            $s3,
                                            $s4,
                                            $s5,
                                        ];

                                        foreach ($campaign_type_active as $type) {
                                            if (in_array($type, $activeTypes)) {
                                                if (isset($dData[$type])) {
                                                    if ($type == 4) {
                                                        foreach ($external_campaigns as $eid => $exn) {
                                                            $views = $dData[$type][$eid]['views'];
                                                            $rate = 0;
                                                            $revenue = $dData[$type][$eid]['revenue'];
                                                            if ($revenue != 0 && $views != 0) {
                                                                $rate = round(($revenue / $views) * 100);
                                                            }
                                                            $data[] = $revenue;
                                                            $data[] = $views;
                                                            $data[] = $rate.'%';
                                                        }
                                                    } else {
                                                        $views = $dData[$type]['views'];
                                                        $rate = 0;

                                                        if (in_array($type, $no_leads)) {
                                                            $revenue = $dData[$type]['revenue'];
                                                            if ($revenue != 0 && $views != 0) {
                                                                $rate = round(($revenue / $views) * 100);
                                                            }

                                                            $data[] = $revenue;
                                                            $data[] = $views;
                                                            $data[] = $rate.'%';
                                                        } else {
                                                            $success = $dData[$type]['success'];
                                                            $rejected = $dData[$type]['rejected'];
                                                            $failed = $dData[$type]['failed'];
                                                            $total = $success + $rejected + $failed;
                                                            if ($total != 0 && $views != 0) {
                                                                $rate = round(($total / $views) * 100);
                                                            }
                                                            $data[] = $success;
                                                            $data[] = $rejected;
                                                            $data[] = $failed;
                                                            $data[] = $views;
                                                            $data[] = $rate.'%';
                                                        }
                                                    }
                                                } elseif ($type == 'external_combine') {
                                                    $ex_revs = 0;
                                                    $ex_views = 0;
                                                    $ex_rate = 0;
                                                    foreach ($benchmarks[4] as $ex_id) {
                                                        if ($ex_id != 'all') {
                                                            $ex_views += $dData[4][$ex_id]['views'];
                                                            $ex_revs += $dData[4][$ex_id]['revenue'];
                                                        }
                                                    }

                                                    if ($ex_views != 0 && $ex_revs != 0) {
                                                        $ex_rate = round(($ex_revs / $ex_views) * 100);
                                                    }
                                                    // Log::info($rt_id.' - '.$ex_revs.' - '.$ex_views);

                                                    $data[] = $ex_revs;
                                                    $data[] = $ex_views;
                                                    $data[] = $ex_rate.'%';
                                                }
                                            }
                                        }
                                        $statsData[] = $data;
                                    }
                                }
                            }
                        }
                    }
                }
            }
            $no_leads[] = 'external_combine';
        }

        $title = 'POIR_'.$inputs['date_from'];
        if ($inputs['date_from'] != $inputs['date_to']) {
            $title .= '_'.$inputs['date_to'];
        }
        Excel::store($title, function ($excel) use ($title, $statsData, $inputs, $campaign_types, $activeTypes, $campaigns, $benchmarks, $no_leads, $external_campaigns) {
            $excel->sheet($title, function ($sheet) use ($statsData, $inputs, $campaign_types, $activeTypes, $campaigns, $benchmarks, $no_leads, $external_campaigns) {

                $sheet->loadView('admin.pageoptinrate_excel', compact('activeTypes', 'campaign_types', 'campaigns', 'benchmarks', 'statsData', 'no_leads', 'external_campaigns'));
            });
        },$title.'.xls','downloads');

        $file_path = storage_path('downloads').'/'.$title.'.xls';

        if (file_exists($file_path)) {
            return response()->download($file_path, $title.'.xls', [
                'Content-Length: '.filesize($file_path),
            ]);
        } else {
            exit('Requested file does not exist on our server!');
        }
    }

    public function clicksVsRegsStats(Request $request): View
    {
        $affiliates = Affiliate::select('id', DB::raw('CONCAT(id, " - ", company) AS id_company'))->where('status', 1)->orderBy('id_company', 'asc')->pluck('id_company', 'id')->toArray();

        return view('admin.clicksvsregstats', compact('affiliates'));
    }

    public function getClicksVsRegsStats(Request $request)
    {
        $inputs = $request->all();
        // $searchValue = $inputs['search']['value'];

        $columns = [
            'clicks_vs_registration_statistics.created_at',
            'clicks_vs_registration_statistics.revenue_tracker_id',
            'clicks_vs_registration_statistics.affiliate_id',
            'clicks_vs_registration_statistics.s1',
            'clicks_vs_registration_statistics.s2',
            'clicks_vs_registration_statistics.s3',
            'clicks_vs_registration_statistics.s4',
            'clicks_vs_registration_statistics.s5',
            'affiliates.company',
            'clicks_vs_registration_statistics.registration_count',
            'clicks_vs_registration_statistics.clicks',
            'clicks_vs_registration_statistics.percentage',
        ];

        //Date From and To
        if (isset($inputs['date_range']) && $inputs['date_range'] != '') {
            //Date Range
            switch ($inputs['date_range']) {
                case 'yesterday':
                    $inputs['date_from'] = Carbon::yesterday()->toDateString();
                    $inputs['date_to'] = Carbon::yesterday()->toDateString();
                    break;
                case 'week':
                    $inputs['date_from'] = Carbon::now()->startOfWeek()->toDateString();
                    $inputs['date_to'] = Carbon::now()->endOfWeek()->toDateString();
                    break;
                case 'month':
                    $inputs['date_from'] = Carbon::now()->startOfMonth()->toDateString();
                    $inputs['date_to'] = Carbon::now()->endOfMonth()->toDateString();
                    break;
                case 'last_month':
                    $inputs['date_from'] = Carbon::now()->subMonth()->startOfMonth()->toDateString();
                    $inputs['date_to'] = Carbon::now()->subMonth()->endOfMonth()->toDateString();
                    break;
            }
        } else {
            //get the date if no date_from and date_to
            $inputs['date_from'] = $inputs['date_from'] != '' ? $inputs['date_from'] : Carbon::yesterday()->toDateString();
            $inputs['date_to'] = $inputs['date_to'] != '' ? $inputs['date_to'] : $inputs['date_from'];
        }

        $allStats = ClicksVsRegistrationStatistics::clicksRegistrationStats($columns, $inputs);
        $stats = $allStats->distinct()->get();

        $affs = $stats->map(function ($st) {
            return $st->affiliate_id;
        });
        $affiliates = Affiliate::whereIn('id', $affs)->pluck('company', 'id');

        // $allStatCount = ClicksVsRegistrationStatistics::clicksRegistrationStats($columns, $inputs)->get()->count();
        // $totalFiltered = $allStatCount;

        // if($inputs['length'] != -1)
        // {
        //     $stats = $allStats->skip($inputs['start'])->take($inputs['length'])->get();
        // }
        // else
        // {
        //     //GET ALL STATS
        //     $stats = $allStats->distinct()->get();
        // }

        // $statData = [];
        // $totalRegistrationCount = 0;
        // $totalCakeClicks = 0;

        // foreach($stats as $stat)
        // {
        //     //correctly compute the percentage
        //     $percentage = '0.00 %';
        //     if($stat->registration_count > 0 && $stat->clicks > 0)
        //     {
        //         $percentage = doubleval($stat->registration_count / $stat->clicks) * 100.0;
        //         $percentage = number_format(doubleval($percentage),2).'%';
        //     }

        //     //$percentage = doubleval($stat->percentage * 100.00);
        //     $createdAt = '';
        //     $s1 = '';
        //     $s2 = '';
        //     $s3 = '';
        //     $s4 = '';
        //     $s5 = '';
        //     if(isset($inputs['group_by']) && $inputs['group_by'] == 'created_at')
        //     {
        //         $createdAt = $stat->created_at;
        //         $s1 = $stat->s1;
        //         $s2 = $stat->s2;
        //         $s3 = $stat->s3;
        //         $s4 = $stat->s4;
        //         $s5 = $stat->s5;
        //     }else if(isset($inputs['group_by']) && $inputs['group_by'] == 'custom') {
        //         $createdAt = $stat->created_at;
        //     }else if(isset($inputs['group_by']) && $inputs['group_by'] == 'per_sub_id') {
        //         $s1 = $stat->s1;
        //         $s2 = $stat->s2;
        //         $s3 = $stat->s3;
        //         $s4 = $stat->s4;
        //         $s5 = $stat->s5;
        //     }

        //     $data = [
        //         'date' => $createdAt,
        //         'revenue_tracker_id' => $stat->revenue_tracker_id,
        //         'affiliate_id' => $stat->affiliate_id,
        //         's1' => $s1,
        //         's2' => $s2,
        //         's3' => $s3,
        //         's4' => $s4,
        //         's5' => $s5,
        //         'affiliate_name' => $stat->company,
        //         'registration_count' => $stat->registration_count,
        //         'clicks' => $stat->clicks,
        //         'percentage' => $percentage
        //     ];

        //     $totalRegistrationCount = $totalRegistrationCount + $stat->registration_count;
        //     $totalCakeClicks = $totalCakeClicks + $stat->clicks;

        //     array_push($statData, $data);
        // }

        // $totalPercentage = 0;

        // if($totalRegistrationCount > 0 && $totalCakeClicks > 0){
        //     $totalPercentage = doubleval($totalRegistrationCount / $totalCakeClicks) * 100.0;
        // }

        // $totalPercentageText = $totalPercentage > 0 ? number_format($totalPercentage, 2).' %' : '0.00 %';

        // if(!empty($searchValue) || $searchValue!='')
        // {
        //     $totalFiltered = $allStatCount;
        // }

        $responseData = [
            'records' => $stats,
            'affiliates' => $affiliates,
            // 'draw'            => intval($inputs['draw']),   // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            // 'recordsTotal'    => $allStatCount,  // total number of records
            // 'recordsFiltered' => $totalFiltered, // total number of records after searching, if there is no searching then totalFiltered = totalData
            // 'data'            => $statData,   // total data array
            'affiliate_id' => $inputs['affiliate_id'],
            // 'date_from'       => $inputs['date_from'],
            // 'date_to'         => $inputs['date_to'],
            // 'date_range'      => $inputs['date_range'],
            // 'group_by'        => $inputs['group_by'],
            // // 'order_column'    => $inputs['order'][0]['column'],
            // // 'order_dir'       => $inputs['order'][0]['dir'],
            // 'totalRegistrationCount' => $allStatCount > 0 ? $totalRegistrationCount : '',
            // 'totalClicks' => $allStatCount > 0 ? $totalCakeClicks : '',
            // 'totalPercentage' => $totalPercentageText
        ];

        if (isset($inputs['order'])) {
            $responseData['order_column'] = $inputs['order'][0]['column'];
            $responseData['order_dir'] = $inputs['order'][0]['dir'];
        }

        return response()->json($responseData, 200);
    }

    public function downLoadClicksVsRegistrationReport(Request $request)
    {
        $inputs = $request->all();
        // Log:info($inputs);

        $columns = [
            'clicks_vs_registration_statistics.created_at',
            'affiliates.company',
            'clicks_vs_registration_statistics.affiliate_id',
            'clicks_vs_registration_statistics.revenue_tracker_id',
            'clicks_vs_registration_statistics.s1',
            'clicks_vs_registration_statistics.s2',
            'clicks_vs_registration_statistics.s3',
            'clicks_vs_registration_statistics.s4',
            'clicks_vs_registration_statistics.s5',
            'clicks_vs_registration_statistics.registration_count',
            'clicks_vs_registration_statistics.clicks',
            'clicks_vs_registration_statistics.percentage',
        ];

        $dateRange = '';

        if (! empty($inputs['date_from']) && ! empty($inputs['date_to'])) {
            $dateRange = $inputs['date_from'].' - '.$inputs['date_to'];
        }

        $groupBy = '';
        if (isset($inputs['group_by'])) {
            $groupBy = $inputs['group_by'];
        }

        $reportTitle = 'ClicksVsRegistration_'.$inputs['date_from'];
        $sheetTitle = 'Stats_'.$dateRange;

        //Date From and To
        if (isset($inputs['date_range']) && $inputs['date_range'] != '') {
            //Date Range
            switch ($inputs['date_range']) {
                case 'yesterday':
                    $inputs['date_from'] = Carbon::yesterday()->toDateString();
                    $inputs['date_to'] = Carbon::yesterday()->toDateString();
                    break;
                case 'week':
                    $inputs['date_from'] = Carbon::now()->startOfWeek()->toDateString();
                    $inputs['date_to'] = Carbon::now()->endOfWeek()->toDateString();
                    break;
                case 'month':
                    $inputs['date_from'] = Carbon::now()->startOfMonth()->toDateString();
                    $inputs['date_to'] = Carbon::now()->endOfMonth()->toDateString();
                    break;
                case 'last_month':
                    $inputs['date_from'] = Carbon::now()->subMonth()->startOfMonth()->toDateString();
                    $inputs['date_to'] = Carbon::now()->subMonth()->endOfMonth()->toDateString();
                    break;
            }
        } else {
            //get the date if no date_from and date_to
            $inputs['date_from'] = $inputs['date_from'] != '' ? $inputs['date_from'] : Carbon::yesterday()->toDateString();
            $inputs['date_to'] = $inputs['date_to'] != '' ? $inputs['date_to'] : $inputs['date_from'];
        }

        // Log::info('date_from: '.$inputs['date_from']);
        // Log::info('date_to: '.$inputs['date_to']);

        $latestClicksVsRegistrationStats = ClicksVsRegistrationStatistics::clicksRegistrationStats($columns, $inputs)->get();

        $affs = $latestClicksVsRegistrationStats->map(function ($st) {
            return $st->affiliate_id;
        });
        $affiliates = Affiliate::whereIn('id', $affs)->pluck('company', 'id');

        Excel::store(function ($excel) use ($sheetTitle, $latestClicksVsRegistrationStats, $groupBy, $affiliates, $inputs) {
            $excel->sheet($sheetTitle, function ($sheet) use ($latestClicksVsRegistrationStats, $groupBy, $affiliates, $inputs) {
                $rowNumber = 1;

                //Set auto size for sheet
                $sheet->setAutoSize(true);

                //set up the title header row
                $sheet->appendRow([
                    'Date',
                    'Affiliate Name',
                    'Affiliate ID',
                    'Revenue Tracker ID',
                    'S1',
                    'S2',
                    'S3',
                    'S4',
                    'S5',
                    'User Registration',
                    'Cake Clicks (All Clicks)',
                    'Percentage',
                ]);

                //style the headers
                $sheet->cells("A$rowNumber:L$rowNumber", function ($cells) {
                    // Set font
                    $cells->setFont([
                        'size' => '12',
                        'bold' => true,
                    ]);

                    $cells->setAlignment('center');
                    $cells->setValignment('center');
                    $cells->setBackground('#000000');
                    $cells->setFontColor('#ffffff');
                });

                foreach ($latestClicksVsRegistrationStats as $stat) {
                    $rowNumber++;

                    $date = '';
                    $s1 = '';
                    $s2 = '';
                    $s3 = '';
                    $s4 = '';
                    $s5 = '';
                    if ($groupBy == 'created_at') {
                        $date = $stat->created_at;
                        $s1 = $inputs['sib_s1'] == 'true' ? $stat->s1 : '';
                        $s2 = $inputs['sib_s2'] == 'true' ? $stat->s2 : '';
                        $s3 = $inputs['sib_s3'] == 'true' ? $stat->s3 : '';
                        $s4 = $inputs['sib_s4'] == 'true' ? $stat->s4 : '';
                        $s5 = $stat->s5;
                    } elseif ($groupBy == 'custom') {
                        $date = $stat->created_at;
                    } elseif ($groupBy == 'per_sub_id') {
                        $s1 = $inputs['sib_s1'] == 'true' ? $stat->s1 : '';
                        $s2 = $inputs['sib_s2'] == 'true' ? $stat->s2 : '';
                        $s3 = $inputs['sib_s3'] == 'true' ? $stat->s3 : '';
                        $s4 = $inputs['sib_s4'] == 'true' ? $stat->s4 : '';
                        $s5 = $stat->s5;
                    }

                    //set up the title header row
                    $sheet->appendRow([
                        $date,
                        $affiliates[$stat->affiliate_id],
                        $stat->affiliate_id,
                        $stat->revenue_tracker_id,
                        $s1,
                        $s2,
                        $s3,
                        $s4,
                        $s5,
                        $stat->registration_count,
                        $stat->clicks,
                        "=(J$rowNumber/K$rowNumber)",
                    ]);
                }

                $lastRowNumber = $rowNumber;

                $rowNumber++;

                //add extra row for totals
                $sheet->appendRow(['', '', '', '', '', '', '']);

                $rowNumber++;

                $sheet->appendRow(['', '', '', '', '', '', '', '', 'Total', "=SUM(J2:J$lastRowNumber)", "=SUM(K2:K$lastRowNumber)", "=(J$rowNumber/K$rowNumber)"]);

                //style the headers
                $sheet->cells("A$rowNumber:L$rowNumber", function ($cells) {
                    // Set font
                    $cells->setFont([
                        'size' => '12',
                        'bold' => true,
                    ]);

                    $cells->setAlignment('right');
                    $cells->setValignment('center');
                });

                $sheet->setColumnFormat([
                    'L2:L'.$rowNumber => '00.00%',
                ]);

            });
        },$reportTitle.'.xls','downloads');

        $file_path = storage_path('downloads').'/'.$reportTitle.'.xls';

        if (file_exists($file_path)) {
            return response()->download($file_path, $reportTitle.'.xls', [
                'Content-Length: '.filesize($file_path),
            ]);
        } else {
            exit("Requested file $file_path  does not exist on our server!");
        }
    }

    public function pageViewStats(Request $request): View
    {
        $inputs = $request->all();
        $affiliates = Affiliate::select('id', DB::raw('CONCAT(id, " - ", company) AS id_company'))->where('status', 1)->orderBy('id_company', 'asc')->pluck('id_company', 'id')->toArray();

        return view('admin.pageViewStats', compact('affiliates', 'inputs'));
    }

    public function getPageViewStats(Request $request)
    {
        $inputs = $request->all();
        $searchValue = $inputs['search']['value'];
        // Log::info($inputs);
        $columns = [ //for ordering
            'page_view_statistics.created_at',
            'page_view_statistics.affiliate_id',
            'page_view_statistics.affiliate_id',
            'page_view_statistics.revenue_tracker_id',
            'page_view_statistics.s1',
            'page_view_statistics.s2',
            'page_view_statistics.s3',
            'page_view_statistics.s4',
            'page_view_statistics.s5',
            'page_view_statistics.lp',
            'page_view_statistics.rp',
            'page_view_statistics.to1',
            'page_view_statistics.to2',
            'page_view_statistics.mo1',
            'page_view_statistics.mo2',
            'page_view_statistics.mo3',
            'page_view_statistics.mo4',
            'page_view_statistics.lfc1',
            'page_view_statistics.lfc2',
            'page_view_statistics.tbr1',
            'page_view_statistics.pd',
            'page_view_statistics.tbr2',
            'page_view_statistics.iff',
            'page_view_statistics.rex',
            'page_view_statistics.ads',
            'page_view_statistics.cpawall',
            'page_view_statistics.exitpage',
            '(page_view_statistics.cpawall/page_view_statistics.lp)',
        ];

        //Date From and To
        if (isset($inputs['date_range']) && $inputs['date_range'] != '') {
            //Date Range
            switch ($inputs['date_range']) {
                case 'yesterday':
                    $inputs['date_from'] = Carbon::yesterday()->toDateString();
                    $inputs['date_to'] = Carbon::yesterday()->toDateString();
                    break;
                case 'week':
                    $inputs['date_from'] = Carbon::now()->startOfWeek()->toDateString();
                    $inputs['date_to'] = Carbon::now()->endOfWeek()->toDateString();
                    break;
                case 'month':
                    $inputs['date_from'] = Carbon::now()->startOfMonth()->toDateString();
                    $inputs['date_to'] = Carbon::now()->endOfMonth()->toDateString();
                    break;
                case 'last_month':
                    $inputs['date_from'] = Carbon::now()->subMonth()->startOfMonth()->toDateString();
                    $inputs['date_to'] = Carbon::now()->subMonth()->endOfMonth()->toDateString();
                    break;
            }
        } else {
            //get the date if no date_from and date_to
            $inputs['date_from'] = $inputs['date_from'] != '' ? $inputs['date_from'] : Carbon::yesterday()->toDateString();
            $inputs['date_to'] = $inputs['date_to'] != '' ? $inputs['date_to'] : $inputs['date_from'];
        }

        $avg_date_from = Carbon::parse($inputs['date_from'])->subDay(31)->toDateString();
        $avg_date_to = Carbon::parse($inputs['date_from'])->subDay()->toDateString();
        $avg_perc_name = 'pvs30dAvg-'.$avg_date_from.'-'.$avg_date_to;
        if (Cache::has($avg_perc_name)) {
            $average_percentage = Cache::get($avg_perc_name);
        } else {
            $pvs = PageViewStatistics::whereBetween('created_at', [$avg_date_from, $avg_date_to])
                ->select(DB::RAW('AVG(cpawall/lp) as average'))->first();
            $average_percentage = $pvs->average;
            $expiresAt = Carbon::now()->addDay();
            Cache::put($avg_perc_name, $average_percentage, $expiresAt);
        }

        //DB::enableQueryLog();
        $allStats = PageViewStatistics::getStats($columns, $inputs);

        $allStatCount = PageViewStatistics::getStats($columns, $inputs)->get()->count();
        $totalFiltered = $allStatCount;

        if ($inputs['length'] != -1) {
            $stats = $allStats->skip($inputs['start'])->take($inputs['length'])->get();
        } else {
            //GET ALL STATS
            $stats = $allStats->distinct()->get();
        }
        //Log::info(DB::getQueryLog());

        $affs = $stats->map(function ($st) {
            return $st->affiliate_id;
        });
        $affiliates = Affiliate::whereIn('id', $affs)->pluck('company', 'id');

        $statData = [];

        $totalLP = 0;
        $totalRP = 0;
        $totalTO1 = 0;
        $totalTO2 = 0;
        $totalMO1 = 0;
        $totalMO2 = 0;
        $totalMO3 = 0;
        $totalMO4 = 0;
        $totalLFC1 = 0;
        $totalLFC2 = 0;
        $totalTBR1 = 0;
        $totalPD = 0;
        $totalTBR2 = 0;
        $totalIFF = 0;
        $totalREX = 0;
        $totalADS = 0;
        $totalCPAWALL = 0;
        $totalEXITPAGE = 0;

        foreach ($stats as $stat) {
            //$percentage = doubleval($stat->percentage * 100.00);
            $createdAt = '';
            $s1 = '';
            $s2 = '';
            $s3 = '';
            $s4 = '';
            $s5 = '';
            if (isset($inputs['group_by']) && $inputs['group_by'] == 'created_at') {
                $createdAt = $stat->created_at;
                $s1 = $inputs['sib_s1'] == 'true' ? $stat->s1 : '';
                $s2 = $inputs['sib_s2'] == 'true' ? $stat->s2 : '';
                $s3 = $inputs['sib_s3'] == 'true' ? $stat->s3 : '';
                $s4 = $inputs['sib_s4'] == 'true' ? $stat->s4 : '';
                $s5 = $stat->s5;
            } elseif (isset($inputs['group_by']) && $inputs['group_by'] == 'custom') {
                $createdAt = $stat->created_at;
            }

            $data = [
                'date' => $createdAt,
                'affiliate_name' => isset($affiliates[$stat->affiliate_id]) ? $affiliates[$stat->affiliate_id] : '',
                'affiliate_id' => $stat->affiliate_id,
                'revenue_tracker_id' => $stat->revenue_tracker_id,
                's1' => $s1,
                's2' => $s2,
                's3' => $s3,
                's4' => $s4,
                's5' => $s5,
                'lp' => $stat->lp,
                'rp' => $stat->rp,
                'to1' => $stat->to1,
                'to2' => $stat->to2,
                'mo1' => $stat->mo1,
                'mo2' => $stat->mo2,
                'mo3' => $stat->mo3,
                'mo4' => $stat->mo4,
                'lfc1' => $stat->lfc1,
                'lfc2' => $stat->lfc2,
                'tbr1' => $stat->tbr1,
                'pd' => $stat->pd,
                'tbr2' => $stat->tbr2,
                'iff' => $stat->iff,
                'rex' => $stat->rex,
                'ads' => $stat->ads,
                'cpawall' => $stat->cpawall,
                'exitpage' => $stat->exitpage,
                'percentage' => ($stat->cpawall > 0 && $stat->lp > 0) ? $stat->cpawall / $stat->lp : 0,
            ];

            $totalLP = $totalLP + $stat->lp;
            $totalRP = $totalRP + $stat->rp;
            $totalTO1 = $totalTO1 + $stat->to1;
            $totalTO2 = $totalTO2 + $stat->to2;
            $totalMO1 = $totalMO1 + $stat->mo1;
            $totalMO2 = $totalMO2 + $stat->mo2;
            $totalMO3 = $totalMO3 + $stat->mo3;
            $totalMO4 = $totalMO4 + $stat->mo4;
            $totalLFC1 = $totalLFC1 + $stat->lfc1;
            $totalLFC2 = $totalLFC2 + $stat->lfc2;
            $totalTBR1 = $totalTBR1 + $stat->tbr1;
            $totalPD = $totalPD + $stat->pd;
            $totalTBR2 = $totalTBR2 + $stat->tbr2;
            $totalIFF = $totalIFF + $stat->iff;
            $totalREX = $totalREX + $stat->rex;
            $totalADS = $totalADS + $stat->ads;
            $totalCPAWALL = $totalCPAWALL + $stat->cpawall;
            $totalEXITPAGE = $totalEXITPAGE + $stat->exitpage;

            array_push($statData, $data);
        }

        if (! empty($searchValue) || $searchValue != '') {
            $totalFiltered = $allStatCount;
        }

        $responseData = [
            'draw' => intval($inputs['draw']),   // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            'recordsTotal' => $allStatCount,   // total number of records
            'recordsFiltered' => $totalFiltered,  // total number of records after searching, if there is no searching then totalFiltered = totalData
            'data' => $statData,   // total data array
            'affiliate_id' => $inputs['affiliate_id'],
            'date_from' => $inputs['date_from'],
            'date_to' => $inputs['date_to'],
            'group_by' => $inputs['group_by'],
            'date_range' => $inputs['date_range'],
            // 'order_column'    => $inputs['order'][0]['column'],
            // 'order_dir'       => $inputs['order'][0]['dir'],
            'total_lp' => $allStatCount > 0 ? $totalLP : '',
            'total_rp' => $allStatCount > 0 ? $totalRP : '',
            'total_to1' => $allStatCount > 0 ? $totalTO1 : '',
            'total_to2' => $allStatCount > 0 ? $totalTO2 : '',
            'total_mo1' => $allStatCount > 0 ? $totalMO1 : '',
            'total_mo2' => $allStatCount > 0 ? $totalMO2 : '',
            'total_mo3' => $allStatCount > 0 ? $totalMO3 : '',
            'total_mo4' => $allStatCount > 0 ? $totalMO4 : '',
            'total_lfc1' => $allStatCount > 0 ? $totalLFC1 : '',
            'total_lfc2' => $allStatCount > 0 ? $totalLFC2 : '',
            'total_tbr1' => $allStatCount > 0 ? $totalTBR1 : '',
            'total_pd' => $allStatCount > 0 ? $totalPD : '',
            'total_tbr2' => $allStatCount > 0 ? $totalTBR2 : '',
            'total_iff' => $allStatCount > 0 ? $totalIFF : '',
            'total_rex' => $allStatCount > 0 ? $totalREX : '',
            'total_ads' => $allStatCount > 0 ? $totalADS : '',
            'total_cpawall' => $allStatCount > 0 ? $totalCPAWALL : '',
            'total_exitpage' => $allStatCount > 0 ? $totalEXITPAGE : '',
            'thirty_day_average' => $average_percentage == '' ? 0 : $average_percentage,
        ];

        if (isset($inputs['order'])) {
            $responseData['order_column'] = $inputs['order'][0]['column'];
            $responseData['order_dir'] = $inputs['order'][0]['dir'];
        }

        return response()->json($responseData, 200);
    }

    public function downLoadPageViewStatisticsReport(Request $request)
    {
        $columns = [ //for ordering
            'page_view_statistics.created_at',
            'affiliates.company',
            'page_view_statistics.affiliate_id',
            'page_view_statistics.revenue_tracker_id',
            'page_view_statistics.s1',
            'page_view_statistics.s2',
            'page_view_statistics.s3',
            'page_view_statistics.s4',
            'page_view_statistics.s5',
            'page_view_statistics.lp',
            'page_view_statistics.rp',
            'page_view_statistics.to1',
            'page_view_statistics.to2',
            'page_view_statistics.mo1',
            'page_view_statistics.mo2',
            'page_view_statistics.mo3',
            'page_view_statistics.mo4',
            'page_view_statistics.lfc1',
            'page_view_statistics.lfc2',
            'page_view_statistics.tbr1',
            'page_view_statistics.pd',
            'page_view_statistics.tbr2',
            'page_view_statistics.iff',
            'page_view_statistics.rex',
            'page_view_statistics.ads',
            'page_view_statistics.cpawall',
            'page_view_statistics.exitpage',
        ];

        $inputs = $request->all();

        $dateRange = '';

        if (! empty($inputs['date_from']) && ! empty($inputs['date_to'])) {
            $dateRange = $inputs['date_from'].' - '.$inputs['date_to'];
        }

        $groupBy = '';
        if (isset($inputs['group_by'])) {
            $groupBy = $inputs['group_by'];
        }

        $reportTitle = 'PageViewStats_'.$inputs['date_from'];
        $sheetTitle = 'Stats_'.$dateRange;

        //Date From and To
        if (isset($inputs['date_range']) && $inputs['date_range'] != '') {
            //Date Range
            switch ($inputs['date_range']) {
                case 'yesterday':
                    $inputs['date_from'] = Carbon::yesterday()->toDateString();
                    $inputs['date_to'] = Carbon::yesterday()->toDateString();
                    break;
                case 'week':
                    $inputs['date_from'] = Carbon::now()->startOfWeek()->toDateString();
                    $inputs['date_to'] = Carbon::now()->endOfWeek()->toDateString();
                    break;
                case 'month':
                    $inputs['date_from'] = Carbon::now()->startOfMonth()->toDateString();
                    $inputs['date_to'] = Carbon::now()->endOfMonth()->toDateString();
                    break;
                case 'last_month':
                    $inputs['date_from'] = Carbon::now()->subMonth()->startOfMonth()->toDateString();
                    $inputs['date_to'] = Carbon::now()->subMonth()->endOfMonth()->toDateString();
                    break;
            }
        } else {
            //get the date if no date_from and date_to
            $inputs['date_from'] = $inputs['date_from'] != '' ? $inputs['date_from'] : Carbon::yesterday()->toDateString();
            $inputs['date_to'] = $inputs['date_to'] != '' ? $inputs['date_to'] : $inputs['date_from'];
        }

        $avg_date_from = Carbon::parse($inputs['date_from'])->subDay(31)->toDateString();
        $avg_date_to = Carbon::parse($inputs['date_from'])->subDay()->toDateString();
        $avg_perc_name = 'pvs30dAvg-'.$avg_date_from.'-'.$avg_date_to;
        if (Cache::has($avg_perc_name)) {
            $average_percentage = Cache::get($avg_perc_name);
        } else {
            $pvs = PageViewStatistics::whereBetween('created_at', [$avg_date_from, $avg_date_to])
                ->select(DB::RAW('AVG(cpawall/lp) as average'))->first();
            $average_percentage = $pvs->average;
            $expiresAt = Carbon::now()->addDay();
            Cache::put($avg_perc_name, $average_percentage, $expiresAt);
        }

        $average_ceiling = $average_percentage * .95;

        $latestPageViewStats = PageViewStatistics::getStats($columns, $inputs)->get();

        $affs = $latestPageViewStats->map(function ($st) {
            return $st->affiliate_id;
        });
        $affiliates = Affiliate::whereIn('id', $affs)->pluck('company', 'id');

        Excel::store(function ($excel) use ($sheetTitle, $latestPageViewStats, $groupBy, $average_percentage, $average_ceiling, $inputs, $affiliates) {
            $excel->sheet($sheetTitle, function ($sheet) use ($latestPageViewStats, $groupBy, $average_percentage, $average_ceiling, $inputs, $affiliates) {
                $rowNumber = 1;

                //Set auto size for sheet
                $sheet->setAutoSize(true);

                //set up the title header row
                $sheet->appendRow([
                    'Date',
                    'Affiliate Name',
                    'Affiliate ID',
                    'Revenue Tracker ID',
                    'S1',
                    'S2',
                    'S3',
                    'S4',
                    'S5',
                    'lp',
                    'rp',
                    'to1',
                    'to2',
                    'mo1',
                    'mo2',
                    'mo3',
                    'mo4',
                    'lfc1',
                    'lfc2',
                    'tbr1',
                    'pd',
                    'tbr2',
                    'iff',
                    'rex',
                    'ads',
                    'cpawall',
                    'exitpage',
                    '%',
                ]);

                //style the headers
                $sheet->cells("A$rowNumber:AB$rowNumber", function ($cells) {
                    // Set font
                    $cells->setFont([
                        'size' => '12',
                        'bold' => true,
                    ]);

                    $cells->setAlignment('center');
                    $cells->setValignment('center');
                    $cells->setBackground('#000000');
                    $cells->setFontColor('#ffffff');
                });

                foreach ($latestPageViewStats as $stat) {
                    $rowNumber++;

                    $date = '';
                    $s1 = '';
                    $s2 = '';
                    $s3 = '';
                    $s4 = '';
                    $s5 = '';
                    if ($groupBy == 'created_at') {
                        $date = $stat->created_at;
                        $s1 = $inputs['sib_s1'] == 'true' ? $stat->s1 : '';
                        $s2 = $inputs['sib_s2'] == 'true' ? $stat->s2 : '';
                        $s3 = $inputs['sib_s3'] == 'true' ? $stat->s3 : '';
                        $s4 = $inputs['sib_s4'] == 'true' ? $stat->s4 : '';
                        $s5 = $stat->s5;
                    } elseif ($groupBy == 'custom') {
                        $date = $stat->created_at;
                    }

                    $percentage = ($stat->cpawall > 0 && $stat->lp > 0) ? $stat->cpawall / $stat->lp : 0;

                    //set up the title header row
                    $sheet->appendRow([
                        $date,
                        isset($affiliates[$stat->affiliate_id]) ? $affiliates[$stat->affiliate_id] : '',
                        $stat->affiliate_id,
                        $stat->revenue_tracker_id,
                        $s1,
                        $s2,
                        $s3,
                        $s4,
                        $s5,
                        $stat->lp,
                        $stat->rp,
                        $stat->to1,
                        $stat->to2,
                        $stat->mo1,
                        $stat->mo2,
                        $stat->mo3,
                        $stat->mo4,
                        $stat->lfc1,
                        $stat->lfc2,
                        $stat->tbr1,
                        $stat->pd,
                        $stat->tbr2,
                        $stat->iff,
                        $stat->rex,
                        $stat->ads,
                        $stat->cpawall,
                        $stat->exitpage,
                        $percentage,
                    ]);
                    // $danger = '#f2dede';
                    // $warning = '#fcf8e3';

                    if ($percentage >= $average_ceiling && $percentage < $average_percentage) {
                        $sheet->cell('AB'.$rowNumber, function ($cell) {
                            $cell->setBackground('#fff200');
                        });
                    } elseif ($percentage < $average_ceiling && $percentage < $average_percentage) {
                        $sheet->cell('AB'.$rowNumber, function ($cell) {
                            $cell->setBackground('#ff0011');
                        });
                    }
                }

                if ($average_percentage == '') {
                    $average_percentage = 0;
                }

                $lastRowNumber = $rowNumber;

                $rowNumber++;

                //add extra row for totals
                $sheet->appendRow(['', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '']);

                $rowNumber++;

                $sheet->appendRow([
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    'Total',
                    "=SUM(J2:J$lastRowNumber)",
                    "=SUM(K2:K$lastRowNumber)",
                    "=SUM(L2:L$lastRowNumber)",
                    "=SUM(M2:M$lastRowNumber)",
                    "=SUM(N2:N$lastRowNumber)",
                    "=SUM(O2:O$lastRowNumber)",
                    "=SUM(P2:P$lastRowNumber)",
                    "=SUM(Q2:Q$lastRowNumber)",
                    "=SUM(R2:R$lastRowNumber)",
                    "=SUM(S2:S$lastRowNumber)",
                    "=SUM(T2:T$lastRowNumber)",
                    "=SUM(U2:U$lastRowNumber)",
                    "=SUM(V2:V$lastRowNumber)",
                    "=SUM(W2:W$lastRowNumber)",
                    "=SUM(X2:X$lastRowNumber)",
                    "=SUM(Y2:Y$lastRowNumber)",
                    "=SUM(Z2:Z$lastRowNumber)",
                    "=SUM(AA2:AA$lastRowNumber)",
                    $average_percentage,
                ]);

                //style the headers
                $sheet->cells("C$rowNumber:AB$rowNumber", function ($cells) {
                    // Set font
                    $cells->setFont([
                        'size' => '12',
                        'bold' => true,
                    ]);

                    $cells->setAlignment('right');
                    $cells->setValignment('center');
                });
            });
        },$reportTitle.'.xls','downloads');

        $file_path = storage_path('downloads').'/'.$reportTitle.'.xls';

        if (file_exists($file_path)) {
            return response()->download($file_path, $reportTitle.'.xls', [
                'Content-Length: '.filesize($file_path),
            ]);
        } else {
            exit("Requested file $file_path  does not exist on our server!");
        }
    }

    /**
     * Search leads page
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function searchLeads(Request $request): View
    {
        // $leads = [];
        // $inputs = [];
        // return view('admin.searchleads',compact('leads','inputs'));

        $inputs = $request->all();

        if (! isset($inputs['show_inactive_campaign']) && isset($inputs['campaign_id']) && $inputs['campaign_id'] == '') {
            $inputs['campaign_ids'] = Campaign::where('status', '!=', 0)->pluck('id')->toArray();
        }
        $leads = [];
        // Log::info($inputs);

        // DB::enableQueryLog();
        if (count($inputs) > 0) {
            if ((isset($inputs['table']) && $inputs['table'] == 'leads')) {
                $query = Lead::searchLeads($inputs)
                    ->join('campaigns', 'leads.campaign_id', '=', 'campaigns.id')
                    ->select(DB::RAW('leads.*, campaigns.name, TIMEDIFF(leads.updated_at,leads.created_at) as time_interval'));

                if (array_key_exists('rejected_type', $inputs)) {

                    $leads = $query->filterByRejection($inputs['rejected_type'])->get();
                    // Add filter when status is rejected
                    // $leads = RejectedLeadsHandler::searchLeadByRejection ($query, $inputs['rejected_type']);
                } else {
                    $leads = $query->get();
                }
            } else {
                $leads = LeadArchive::searchLeads($inputs)
                    ->join('campaigns', 'leads_archive.campaign_id', '=', 'campaigns.id')
                    ->select(DB::RAW('leads_archive.*, campaigns.name, TIMEDIFF(leads_archive.updated_at,leads_archive.created_at) as time_interval'))
                    ->get();
            }
        }
        // Log::info(DB::getQueryLog());

        session()->put('searched_leads_input', $inputs); // Save into sessions in Search Leads

        if ($leads && count($leads) > 0) {
            session()->flash('has_search_leads', true);
        }

        return view('admin.searchleads', compact('leads', 'inputs'));
    }

    public function getLeadRejectionRateSettings()
    {
        $allSettings = Setting::whereIn('code', ['high_critical_rejection_rate', 'high_rejection_keywords'])->get();
        $settings = [];
        foreach ($allSettings as $set) {
            $settings[$set->code] = $set;
        }

        return [
            'names' => [
                'd' => 'Duplicate',
                'p' => 'Pre-Pop',
                'f' => 'Filter',
                'a' => 'Acceptable',
            ],
            'rates' => json_decode($settings['high_critical_rejection_rate']->string_value, true),
            'keywords' => json_decode($settings['high_rejection_keywords']->description, true),
        ];
    }

    public function updateLeadRejectionRateSettings(Request $request)
    {

        $userActionLogs = [];
        $inputs = $request->all();
        $rate = Setting::where('code', 'high_critical_rejection_rate');

        $new_rates = [$inputs['min_high_reject_rate'], $inputs['max_high_reject_rate']];
        $new_rates_str = json_encode($new_rates);

        $rej_rate = Setting::where('code', 'high_critical_rejection_rate')->first();
        $entry = $this->logger->createUserActionLogEntry('min_high_reject_rate', $rej_rate->string_value, $new_rates_str);
        if ($entry != null) {
            array_push($userActionLogs, $entry);
        }
        $rej_rate->string_value = $new_rates_str;
        $rej_rate->save();

        $keywords = json_encode($inputs['high_rejection_keywords']);
        $reject_keywords = Setting::where('code', 'high_rejection_keywords')->first();
        $entry = $this->logger->createUserActionLogEntry('high_rejection_keywords', $reject_keywords->description, $keywords);
        if ($entry != null) {
            array_push($userActionLogs, $entry);
        }
        $reject_keywords->description = $keywords;
        $reject_keywords->save();

        event(new UserActionEvent($userActionLogs));

        return $request->all();
    }

    public function getSearchLeads(Request $request): JsonResponse
    {
        $inputs = $request->all();
        // Log::info($inputs);
        $param = $inputs['search']['value'];
        $start = $inputs['start'];
        $length = $inputs['length'];

        $columns = [
            //datatable column index  => database column name
            0 => 'id',
            1 => 'campaigns.name',
            2 => 'creative_id',
            3 => 'affiliate_id',
            4 => 'lead_email',
            5 => 's1',
            6 => 's2',
            7 => 's3',
            8 => 's4',
            9 => 's5',
            10 => 'received',
            11 => 'payout',
            12 => $inputs['table'].'.created_at',
            13 => $inputs['table'].'.updated_at',
            14 => 'time_interval',
            15 => 'lead_status',
        ];

        $leadStatuses = config('constants.LEAD_STATUS');

        if ($inputs['table'] == 'leads') {
            $leads = Lead::getSearchLeads($inputs, $start, $length, $columns[$inputs['order'][0]['column']], $inputs['order'][0]['dir'])
                ->join('campaigns', 'leads.campaign_id', '=', 'campaigns.id')
                ->select(DB::RAW('leads.*, campaigns.name, TIMEDIFF(leads.updated_at,leads.created_at) as time_interval'))
                ->get();
            $totalFiltered = Lead::getSearchLeads($inputs, null, null, null, null)->count();
            $totalLeads = Lead::count();
        } else {
            $leads = LeadArchive::getSearchLeads($inputs, $start, $length, $columns[$inputs['order'][0]['column']], $inputs['order'][0]['dir'])
                ->join('campaigns', 'leads_archive.campaign_id', '=', 'campaigns.id')
                ->select(DB::RAW('leads_archive.*, campaigns.name, TIMEDIFF(leads_archive.updated_at,leads_archive.created_at) as time_interval'))
                ->get();
            $totalFiltered = LeadArchive::getSearchLeads($inputs, null, null, null, null)->count();
            $totalLeads = LeadArchive::count();
        }

        session()->put('searched_leads_input', $inputs);

        // Log::info($totalFiltered);
        // Log::info($leads[0]);
        $leadData = [];
        foreach ($leads as $lead) {
            $id = $lead->id;
            $leadIdCol = '<input class="checkbox" id="lead-'.$id.'" data-lead_id="'.$id.'" name="lead-'.$id.'" type="checkbox" value="0"><br>'.$id;
            $actionCol = '<button class="btn btn-default show-details" data-lead_id="'.$id.'" data-name="'.$lead->name.'" type="button">Show</button>';

            $row = [
                $leadIdCol,
                $lead->name,
                $lead->creative_id,
                $lead->affiliate_id,
                $lead->lead_email,
                $lead->s1,
                $lead->s2,
                $lead->s3,
                $lead->s4,
                $lead->s5,
                $lead->received,
                $lead->payout,
                Carbon::parse($lead->created_at)->toDateTimeString(),
                Carbon::parse($lead->updated_at)->toDateTimeString(),
                $lead->time_interval,
                $leadStatuses[$lead->lead_status],
                $actionCol,
            ];

            array_push($leadData, $row);
        }

        $responseData = [
            'draw' => intval($inputs['draw']),   // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            'recordsTotal' => $totalLeads,  // total number of records
            'recordsFiltered' => $totalFiltered, // total number of records after searching, if there is no searching then totalFiltered = totalData
            'data' => $leadData,   // total data array
        ];
        // Log::info(DB::getQueryLog());
        return response()->json($responseData, 200);
    }

    /**
     * for revenueStats
     */
    public function revenueStatistics(Request $request): View
    {
        return view('admin.revenueStats');
    }

    public function getRevenueStatistics(Request $request)
    {
        // DB::enableQueryLog();
        // DB::connection('secondary')->enableQueryLog();
        $inputs = $request->all();
        $leadData = [];
        $allLeads = null;
        $campaign_types = config('constants.CAMPAIGN_TYPES');

        if (! isset($inputs['show_inactive_campaign']) && $inputs['campaign_id'] == '') {
            $inputs['campaign_ids'] = Campaign::where('status', '!=', 0)->pluck('id')->toArray();
        }

        // \Log::info($inputs);

        if (isset($inputs['table'])) {
            if ($inputs['table'] == 'leads') {
                $allLeads = Lead::getRevenueStats($inputs); //->get();
            } else {
                $allLeads = LeadArchive::getRevenueStats($inputs); //->get();
            }
        } else {
            $allLeads = Lead::getRevenueStats($inputs); //->get();
        }

        session()->put('revenue_leads_input', $inputs);
        // session()->put('revenue_leads',$allLeads->get());

        /* FOR COUNT */
        // $leads_count = Lead::getTotalRevenueStats($inputs);
        $leads_count = count($allLeads->get());
        // session()->put('revenue_leads_count',$leads_count);
        // Log::info(Lead::getRevenueStatsTotal($inputs)->count());
        // $leads_count = $allLeads->get()->count();
        // Log::info($leads_count);
        $leads = $allLeads->skip($inputs['start'])->take($inputs['length'])->get();
        // Log::info($leads);
        // Log::info(DB::getQueryLog());
        // Log::info(DB::getQueryLog());
        // Log::info(DB::connection('secondary')->getQueryLog());
        $leadStatus = config('constants.LEAD_STATUS');

        $aff_ids = $leads->map(function ($st) {
            return $st->affiliate_id;
        })->toArray();
        $aff_ids = array_unique($aff_ids);
        $rev_trackers = AffiliateRevenueTracker::whereIn('revenue_tracker_id', $aff_ids)->pluck('affiliate_id', 'revenue_tracker_id')->toArray();
        $diff = array_diff($aff_ids, array_keys($rev_trackers));
        foreach ($diff as $d) {
            $rev_trackers[$d] = $d;
        }
        $affiliates = Affiliate::whereIn('id', $rev_trackers)->pluck('company', 'id')->toArray();
        // Log::info($rev_trackers);
        // Log::info($affiliates);

        $totalLeadCount = 0;
        $totalCost = 0;
        $totalRevenue = 0;
        $totalProfit = 0;
        foreach ($leads as $lead) {
            $company = '';
            switch ($request->input('group_by_column')) {
                case 'campaign':
                    $campaign_id = $lead->campaign_name;
                    $campaign_type = $campaign_types[$lead->campaign_type];
                    $creative_id = '';
                    $affiliate_id = '';
                    $s1 = '';
                    $s2 = '';
                    $s3 = '';
                    $s4 = '';
                    $s5 = '';
                    break;
                case 'creative_id':
                    $campaign_id = '';
                    $campaign_type = '';
                    $creative_id = $lead->creative_id;
                    $affiliate_id = '';
                    $s1 = '';
                    $s2 = '';
                    $s3 = '';
                    $s4 = '';
                    $s5 = '';
                    break;
                case 'affiliate':
                    $campaign_id = '';
                    $campaign_type = '';
                    $creative_id = '';
                    $affiliate_id = $lead->affiliate_id;
                    $s1 = '';
                    $s2 = '';
                    $s3 = '';
                    $s4 = '';
                    $s5 = '';
                    break;
                case 's1':
                    $campaign_id = '';
                    $campaign_type = '';
                    $creative_id = '';
                    $affiliate_id = '';
                    $s1 = $lead->s1;
                    $s2 = '';
                    $s3 = '';
                    $s4 = '';
                    $s5 = '';
                    break;
                case 's2':
                    $campaign_id = '';
                    $campaign_type = '';
                    $creative_id = '';
                    $affiliate_id = '';
                    $s1 = '';
                    $s2 = $lead->s2;
                    $s3 = '';
                    $s4 = '';
                    $s5 = '';
                    break;
                case 's3':
                    $campaign_id = '';
                    $campaign_type = '';
                    $creative_id = '';
                    $affiliate_id = '';
                    $s1 = '';
                    $s2 = '';
                    $s3 = $lead->s3;
                    $s4 = '';
                    $s5 = '';
                    break;
                case 's4':
                    $campaign_id = '';
                    $campaign_type = '';
                    $creative_id = '';
                    $affiliate_id = '';
                    $s1 = '';
                    $s2 = '';
                    $s3 = '';
                    $s4 = $lead->s4;
                    $s5 = '';
                    break;
                case 's5':
                    $campaign_id = '';
                    $campaign_type = '';
                    $creative_id = '';
                    $affiliate_id = '';
                    $s1 = $lead->s1;
                    $s2 = '';
                    $s3 = '';
                    $s4 = '';
                    $s5 = $lead->s5;
                    break;
                default:
                    $campaign_id = $lead->campaign_name;
                    $campaign_type = $campaign_types[$lead->campaign_type];
                    $creative_id = $lead->creative_id;
                    $affiliate_id = $lead->affiliate_id;
                    $s1 = $inputs['sib_s1'] == 'true' ? $lead->s1 : '';
                    $s2 = $inputs['sib_s2'] == 'true' ? $lead->s2 : '';
                    $s3 = $inputs['sib_s3'] == 'true' ? $lead->s3 : '';
                    $s4 = $inputs['sib_s4'] == 'true' ? $lead->s4 : '';
                    $s5 = $lead->s5;
                    break;
            }

            if ($affiliate_id != '') {
                if (isset($rev_trackers[$affiliate_id])) {
                    $company = isset($affiliates[$rev_trackers[$affiliate_id]]) ? $affiliates[$rev_trackers[$affiliate_id]] : '';
                }
            }

            $data = [
                $lead->lead_date,
                $campaign_id,
                $campaign_type,
                $creative_id,
                $affiliate_id,
                $company,
                $lead->campaign_rate,
                $lead->advertiser_name,
                $s1,
                $s2,
                $s3,
                $s4,
                $s5,
                $leadStatus[$lead->lead_status],
                $lead->lead_count,
                $lead->lead_status == 1 ? sprintf('%.2f', $lead->cost) : '0.00',
                $lead->lead_status == 1 ? sprintf('%.2f', $lead->revenue) : '0.00',
                $lead->lead_status == 1 ? sprintf('%.2f', $lead->profit) : '0.00',
            ];

            array_push($leadData, $data);

            $totalLeadCount += $lead->lead_count;
            $totalCost += $lead->lead_status == 1 ? $lead->cost : 0;
            $totalRevenue += $lead->lead_status == 1 ? $lead->revenue : 0;
            $totalProfit += $lead->lead_status == 1 ? $lead->profit : 0;

        }

        $totalFiltered = count($leads);

        $responseData = [
            'draw' => intval($inputs['draw']),   // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            'recordsTotal' => $leads_count,  // total number of records
            'recordsFiltered' => $leads_count, // total number of records after searching, if there is no searching then totalFiltered = totalData
            'data' => $leadData,   // total data array
            'totalLeadCount' => $totalLeadCount,
            'totalCost' => sprintf('%.2f', $totalCost),
            'totalRevenue' => sprintf('%.2f', $totalRevenue),
            'totalProfit' => sprintf('%.2f', $totalProfit),
        ];
        // Log::info(DB::getQueryLog());
        // Log::info(DB::connection('secondary')->getQueryLog());
        return response()->json($responseData, 200);
    }

    /**
     * Function of getting the details
     */
    public function getLeadDetails($lead_id): JsonResponse
    {
        $data = [];
        $data['leadDataADV'] = null;
        $data['leadDataCSV'] = null;
        $data['leadMessage'] = null;
        $data['leadSentResult'] = null;

        $leadDataADV = LeadDataAdv::select('id', 'value')->where('id', '=', $lead_id)->first();
        $leadDataCSV = LeadDataCsv::select('id', 'value')->where('id', '=', $lead_id)->first();
        $leadMessage = LeadMessage::select('id', 'value')->where('id', '=', $lead_id)->first();
        $leadSentResult = LeadSentResult::select('id', 'value')->where('id', '=', $lead_id)->first();

        if ($leadDataADV !== null) {
            $data['leadDataADV'] = $leadDataADV;
        }

        if ($leadDataCSV !== null) {
            $data['leadDataCSV'] = $leadDataCSV;
        }

        if ($leadMessage !== null) {
            $data['leadMessage'] = $leadMessage;
        }

        if ($leadSentResult !== null) {
            $data['leadSentResult'] = $leadSentResult;
        }

        return response()->json($data);
    }

    /**
     * Updating of lead details
     */
    public function updateLeadDetails(Request $request): JsonResponse
    {
        $inputs = $request->all();

        $leadDataADV = LeadDataAdv::find($inputs['id']);
        $leadDataCSV = LeadDataCSV::find($inputs['id']);
        $leadMessage = LeadMessage::find($inputs['id']);
        $leadSentResult = LeadSentResult::find($inputs['id']);

        $leadDataCSV->value = $inputs['lead_csv'];
        $leadDataCSV->save();

        $leadMessage->value = $inputs['message'];
        $leadMessage->save();

        $leadDataADV->value = $inputs['advertiser_data'];
        $leadDataADV->save();

        $leadSentResult->value = $inputs['sent_result'];
        $leadSentResult->save();

        return response()->json(['status' => 200, 'id' => $inputs['id']]);
    }

    /**
     * Downloading of searched leads
     */
    public function downloadSearchedLeads(): BinaryFileResponse
    {
        // DB::enableQueryLog();
        $inputs = session()->get('searched_leads_input');
        $leads = [];
        // Log::info($inputs);
        if (count($inputs) > 0) {
            if ((isset($inputs['table']) && $inputs['table'] == 'leads')) {
                // $leads = Lead::searchLeads($inputs)->with('leadMessage','leadDataADV','leadDataCSV','leadSentResult')->get();

                $leads = Lead::searchLeads($inputs)
                    ->join('campaigns', 'leads.campaign_id', '=', 'campaigns.id')
                    ->leftJoin('lead_data_advs', 'leads.id', '=', 'lead_data_advs.id')
                    ->leftJoin('lead_data_csvs', 'leads.id', '=', 'lead_data_csvs.id')
                    ->leftJoin('lead_messages', 'leads.id', '=', 'lead_messages.id')
                    ->leftJoin('lead_sent_results', 'leads.id', '=', 'lead_sent_results.id')
                    ->select(DB::RAW('leads.*, campaigns.name, TIMEDIFF(leads.updated_at,leads.created_at) as time_interval, lead_data_advs.value as advs_val, lead_data_csvs.value as csvs_val, lead_messages.value as msg_val, lead_sent_results.value as sntr_val'))->get();

            } else {
                // $leads = LeadArchive::searchLeads($inputs)->with('leadMessage','leadDataADV','leadDataCSV','leadSentResult')->get();

                $leads = LeadArchive::searchLeads($inputs)
                    ->join('campaigns', 'leads_archive.campaign_id', '=', 'campaigns.id')
                    ->leftJoin('lead_data_advs_archive', 'leads_archive.id', '=', 'lead_data_advs_archive.id')
                    ->leftJoin('lead_data_csvs_archive', 'leads_archive.id', '=', 'lead_data_csvs_archive.id')
                    ->leftJoin('lead_messages_archive', 'leads_archive.id', '=', 'lead_messages_archive.id')
                    ->leftJoin('lead_sent_results_archive', 'leads_archive.id', '=', 'lead_sent_results_archive.id')
                    ->select(DB::RAW('leads_archive.*, campaigns.name, TIMEDIFF(leads_archive.updated_at,leads_archive.created_at) as time_interval, lead_data_advs_archive.value as advs_val, lead_data_csvs_archive.value as csvs_val, lead_messages_archive.value as msg_val, lead_sent_results_archive.value as sntr_val'))
                    ->get();
            }
        }
        // Log::info(DB::getQueryLog());
        // return $leads;
        // $columns = [
        //     0 => 'id',
        //     1 => 'campaigns.name',
        //     2 => 'creative_id',
        //     3 => 'affiliate_id',
        //     4 => 'lead_email',
        //     5 => 's1',
        //     6 => 's2',
        //     7 => 's3',
        //     8 => 's4',
        //     9 => 's5',
        //     5 => 'received',
        //     6 => 'payout',
        //     7 => $inputs['table'].'.created_at',
        //     8 => $inputs['table'].'.updated_at',
        //     9 => 'time_interval',
        //     10 => 'lead_status'
        // ];

        // if($inputs['table'] == 'leads') {
        //     $leads = Lead::getSearchLeads($inputs,null,null,$columns[$inputs['order'][0]['column']],$inputs['order'][0]['dir'])
        //         ->join('campaigns','leads.campaign_id','=','campaigns.id')
        //         ->select(DB::RAW("leads.*, campaigns.name, TIMEDIFF(leads.updated_at,leads.created_at) as time_interval"))
        //         ->get();
        // }else {
        //     $leads = LeadArchive::getSearchLeads($inputs,null,null,$columns[$inputs['order'][0]['column']],$inputs['order'][0]['dir'])
        //         ->join('campaigns','leads_archive.campaign_id','=','campaigns.id')
        //         ->select(DB::RAW("leads_archive.*, campaigns.name, TIMEDIFF(leads_archive.updated_at,leads_archive.created_at) as time_interval"))
        //         ->get();
        // }

        $title = 'SearchedLeads_'.Carbon::now()->toDateString();
        $fn = storage_path('downloads');

        Excel::store(function ($excel) use ($title, $leads) {
            $excel->sheet($title, function ($sheet) use ($leads) {

                // Set auto size for sheet
                $sheet->setAutoSize(true);

                $headers = ['Lead ID', 'Campaign', 'Creative', 'Affiliate', 'Lead Email', 's1', 's2', 's3', 's4', 's5', 'Received', 'Payout', 'Lead Date', 'Lead Updated', 'Time Interval', 'Status', 'Full Name', 'Lead CSV', 'Lead Message', 'Advertiser Data', 'Sent Result'];

                //set up the title header row
                $sheet->appendRow($headers);

                //style the headers
                $sheet->cells('A1:U1', function ($cells) {

                    // Set font
                    $cells->setFont([
                        'size' => '12',
                        'bold' => true,
                    ]);

                    $cells->setAlignment('center');
                    $cells->setValignment('center');
                });

                $leadStatus = config('constants.LEAD_STATUS');

                foreach ($leads as $lead) {
                    $campaign = $lead->campaign;

                    //extract the full name
                    $fullName = '';
                    if ($lead->csvs_val != '') {
                        $csvData = \GuzzleHttp\json_decode($lead->csvs_val);
                        $firstName = '';
                        $lastName = '';

                        if (isset($csvData->first_name)) {
                            $firstName = $csvData->first_name;
                        }

                        if (isset($csvData->last_name)) {
                            $lastName = $csvData->last_name;
                        }

                        $fullName = "$firstName $lastName";
                    }

                    $sheet->appendRow([
                        $lead->id,
                        $campaign->name.' ('.$campaign->id.')',
                        $lead->creative_id,
                        $lead->affiliate_id,
                        $lead->lead_email,
                        $lead->s1,
                        $lead->s2,
                        $lead->s3,
                        $lead->s4,
                        $lead->s5,
                        $lead->received,
                        $lead->payout,
                        $lead->created_at,
                        $lead->updated_at,
                        $lead->time_interval,
                        $leadStatus[$lead->lead_status],
                        $fullName,
                        $lead->csvs_val,
                        $lead->msg_val,
                        $lead->advs_val,
                        $lead->sntr_val,
                    ]);
                }
            });
        },$title.'.xls','downloads');

        $file_path = storage_path('downloads').'/'.$title.'.xls';

        if (file_exists($file_path)) {
            return response()->download($file_path, $title.'.xls', [
                'Content-Length: '.filesize($file_path),
            ]);
        } else {
            exit('Requested file does not exist on our server!');
        }
    }

    /**
     * For downloading revenue report
     */
    public function downloadRevenueReport(): BinaryFileResponse
    {
        // DB::enableQueryLog();
        // $leads = session()->get('revenue_leads');
        $inputs = session()->get('revenue_leads_input');
        // $leads = Lead::getRevenueStats($inputs)->get();

        if (isset($inputs['table'])) {
            if ($inputs['table'] == 'leads') {
                $leads = Lead::getRevenueStats($inputs)->get();
            } else {
                $leads = LeadArchive::getRevenueStats($inputs)->get();
            }
        } else {
            $leads = Lead::getRevenueStats($inputs)->get();
        }

        $aff_ids = $leads->map(function ($st) {
            return $st->affiliate_id;
        })->toArray();
        $aff_ids = array_unique($aff_ids);
        $rev_trackers = AffiliateRevenueTracker::whereIn('revenue_tracker_id', $aff_ids)->pluck('affiliate_id', 'revenue_tracker_id')->toArray();
        $diff = array_diff($aff_ids, array_keys($rev_trackers));
        foreach ($diff as $d) {
            $rev_trackers[$d] = $d;
        }
        $affiliates = Affiliate::whereIn('id', $rev_trackers)->pluck('company', 'id')->toArray();

        // session()->put('revenue_leads',$allLeads->get());

        $title = 'RevenueLeads_'.Carbon::now()->toDateString();

        Excel::store(function ($excel) use ($title, $leads, $inputs, $affiliates, $rev_trackers) {
            $excel->sheet($title, function ($sheet) use ($leads, $inputs, $affiliates, $rev_trackers) {

                // Set auto size for sheet
                $sheet->setAutoSize(true);

                $headers = ['Lead Date', 'Campaign', 'Campaign Type', 'Creative ID', 'Affiliate', 'Company', 'Rate', 'Advertiser', 's1', 's2', 's3', 's4', 's5', 'Status', 'Lead Count', 'Cost', 'Revenue', 'Profit'];

                //set up the title header row
                $sheet->appendRow($headers);

                //style the headers
                $sheet->cells('A1:Q1', function ($cells) {

                    // Set font
                    $cells->setFont([
                        'size' => '12',
                        'bold' => true,
                    ]);

                    $cells->setAlignment('center');
                    $cells->setValignment('center');
                });

                $leadStatus = config('constants.LEAD_STATUS');
                $campaign_types = config('constants.CAMPAIGN_TYPES');

                $totalLeadCount = 0;
                $totalCost = 0;
                $totalRevenue = 0;
                $totalProfit = 0;

                foreach ($leads as $lead) {
                    $company = '';
                    if (isset($inputs['group_by_column'])) {
                        switch ($inputs['group_by_column']) {
                            case 'campaign':
                                $campaign_id = $lead->campaign_name.'('.$lead->campaign_id.')';
                                $campaign_type = $campaign_types[$lead->campaign_type];
                                $creative_id = '';
                                $affiliate_id = '';
                                $s1 = '';
                                $s2 = '';
                                $s3 = '';
                                $s4 = '';
                                $s5 = '';
                                break;
                            case 'creative_id':
                                $campaign_id = '';
                                $campaign_type = '';
                                $creative_id = $lead->creative_id;
                                $affiliate_id = '';
                                $s1 = '';
                                $s2 = '';
                                $s3 = '';
                                $s4 = '';
                                $s5 = '';
                                break;
                            case 'affiliate':
                                $campaign_id = '';
                                $campaign_type = '';
                                $creative_id = '';
                                $affiliate_id = $lead->affiliate_id;
                                $s1 = '';
                                $s2 = '';
                                $s3 = '';
                                $s4 = '';
                                $s5 = '';
                                break;
                            case 's1':
                                $campaign_id = '';
                                $campaign_type = '';
                                $creative_id = '';
                                $affiliate_id = '';
                                $s1 = $lead->s1;
                                $s2 = '';
                                $s3 = '';
                                $s4 = '';
                                $s5 = '';
                                break;
                            case 's2':
                                $campaign_id = '';
                                $campaign_type = '';
                                $affiliate_id = '';
                                $s1 = '';
                                $s2 = $lead->s2;
                                $s3 = '';
                                $s4 = '';
                                $s5 = '';
                                break;
                            case 's3':
                                $campaign_id = '';
                                $campaign_type = '';
                                $creative_id = '';
                                $affiliate_id = '';
                                $s1 = '';
                                $s2 = '';
                                $s3 = $lead->s3;
                                $s4 = '';
                                $s5 = '';
                                break;
                            case 's4':
                                $campaign_id = '';
                                $campaign_type = '';
                                $creative_id = '';
                                $affiliate_id = '';
                                $s1 = '';
                                $s2 = '';
                                $s3 = '';
                                $s4 = $lead->s4;
                                $s5 = '';
                                break;
                            case 's5':
                                $campaign_id = '';
                                $campaign_type = '';
                                $creative_id = '';
                                $affiliate_id = '';
                                $s1 = $lead->s1;
                                $s2 = '';
                                $s3 = '';
                                $s4 = '';
                                $s5 = $lead->s5;
                                break;
                            default:
                                $campaign_id = $lead->campaign_name.'('.$lead->campaign_id.')';
                                $campaign_type = $campaign_types[$lead->campaign_type];
                                $creative_id = $lead->creative_id;
                                $affiliate_id = $lead->affiliate_id;
                                $s1 = $inputs['sib_s1'] == 'true' ? $lead->s1 : '';
                                $s2 = $inputs['sib_s2'] == 'true' ? $lead->s2 : '';
                                $s3 = $inputs['sib_s3'] == 'true' ? $lead->s3 : '';
                                $s4 = $inputs['sib_s4'] == 'true' ? $lead->s4 : '';
                                $s5 = $lead->s5;
                                break;
                        }
                    } else {
                        $campaign_id = $lead->campaign_name.'('.$lead->campaign_id.')';
                        $campaign_type = $campaign_types[$lead->campaign_type];
                        $creative_id = $lead->creative_id;
                        $affiliate_id = $lead->affiliate_id;
                        $s1 = $inputs['sib_s1'] == 'true' ? $lead->s1 : '';
                        $s2 = $inputs['sib_s2'] == 'true' ? $lead->s2 : '';
                        $s3 = $inputs['sib_s3'] == 'true' ? $lead->s3 : '';
                        $s4 = $inputs['sib_s4'] == 'true' ? $lead->s4 : '';
                        $s5 = $lead->s5;
                    }

                    if ($affiliate_id != '') {
                        if (isset($rev_trackers[$affiliate_id])) {
                            $company = isset($affiliates[$rev_trackers[$affiliate_id]]) ? $affiliates[$rev_trackers[$affiliate_id]] : '';
                        }
                    }

                    $sheet->appendRow([
                        $lead->lead_date,
                        $campaign_id,
                        $campaign_type,
                        $creative_id,
                        $affiliate_id,
                        $company,
                        $lead->campaign_rate,
                        $lead->advertiser_name,
                        $s1,
                        $s2,
                        $s3,
                        $s4,
                        $s5,
                        $leadStatus[$lead->lead_status],
                        $lead->lead_count,
                        $lead->lead_status == 1 ? $lead->cost : 0,
                        $lead->lead_status == 1 ? $lead->revenue : 0,
                        $lead->lead_status == 1 ? $lead->revenue - $lead->cost : 0,
                    ]);

                    $totalLeadCount += $lead->lead_count;
                    $totalCost += $lead->lead_status == 1 ? $lead->cost : 0;
                    $totalRevenue += $lead->lead_status == 1 ? $lead->revenue : 0;
                    $totalProfit += $lead->lead_status == 1 ? $lead->revenue - $lead->cost : 0;
                }

                $sheet->appendRow([
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    $totalLeadCount,
                    $totalCost,
                    $totalRevenue,
                    $totalProfit,
                ]);
            });
        },$title.'.xls','downloads');

        $file_path = storage_path('downloads').'/'.$title.'.xls';

        // Log::info(DB::getQueryLog());

        if (file_exists($file_path)) {
            return response()->download($file_path, $title.'.xls', [
                'Content-Length: '.filesize($file_path),
            ]);
        } else {
            exit('Requested file does not exist on our server!');
        }
    }

    /**
     * Revenue Tracker Page
     */
    public function revenueTrackers(): View
    {
        $affiliates = Affiliate::select('id', DB::raw('CONCAT(id, " - ", company) AS id_company'))->where('status', 1)->orderBy('id_company', 'asc')->pluck('id_company', 'id')->toArray();
        $path_types = config('constants.PATH_TYPES');
        $campaignStatuses = config('constants.CAMPAIGN_STATUS');

        $campaignOrderDirection = config('constants.CAMPAIGN_ORDER_DIRECTION');
        $campaignTypes = config('constants.CAMPAIGN_TYPES');
        $campaignOrdering = [];
        $mixeCoregTypes = config('constants.MIXED_COREG_TYPE_FOR_ORDERING');
        $campaigns = Campaign::select('id', 'name', 'campaign_type', 'status')->orderBy('priority', 'asc')->get();
        $default_order = [];
        $default_mixed_coreg_campaign_order = [];
        $exit_page_campaigns = [];
        foreach ($campaigns as $c) {
            $campaignOrdering[$c->campaign_type][] = [
                'id' => $c->id,
                'name' => $c->name,
            ];

            $default_order[$c->campaign_type][] = $c->id;
            $campaign_names[$c->id] = $c->name;
            $campaign_statuses[$c->id] = $campaignStatuses[$c->status];

            if (in_array($c->campaign_type, array_keys($mixeCoregTypes))) {
                $default_mixed_coreg_campaign_order[] = $c->id;
            }

            if ($c->campaign_type == 6) {
                $exit_page_campaigns[$c->id] = $c->name;
            }
        }

        return view('admin.revenueTrackers', compact('affiliates', 'path_types', 'campaignOrderDirection', 'campaignTypes', 'campaignOrdering', 'campaign_names', 'default_order', 'default_mixed_coreg_campaign_order', 'campaign_statuses', 'exit_page_campaigns'));
    }

    /**
     * Gallery Page
     */
    public function gallery(): View
    {
        //$files =  Storage::disk('public')->get('images\gallery\logo-globaltestmarket-1.png');
        //$files =  Storage::disk('public')->has('images\gallery\logo-globaltestmarket-1.png');
        $images = Storage::disk('public')->files('images/gallery');
        //$images = File::allFiles('images\gallery');
     
        $col_num = 4;
        $counter = 0;
        $row = 0;
        $gallery = array();
        foreach ($images as $image) {
            $gallery[$row][] = $image;
            //echo "x";
            $counter++;
            if ($counter % $col_num == 0 && $counter != 0) {
                $row++;
            }
        }

        foreach ($gallery as $key => $row) {
            if (count($row) < $col_num) {
                $n = $col_num - count($row);
                for ($x = 0; $x < $n; $x++) {
                    $gallery[$key][] = '';
                }
            }
        }
        
        return view('admin.gallery', compact('gallery'));
    }

    /**
     * Settings page
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function settings()
    {
        $allSettings = Setting::all();
        $settings = [];
        foreach ($allSettings as $set) {
            $settings[$set->code] = $set;
        }

        $path_order = json_decode($settings['stack_path_campaign_type_order']->string_value, true);
        $rejection_rates = json_decode($settings['high_critical_rejection_rate']->string_value, true);

        $campaign_types = config('constants.CAMPAIGN_TYPES');

        $campaign_type_limit = json_decode($settings['campaign_type_path_limit']->description, true);

        $statuses = config('constants.UNI_STATUS');

        $c = Campaign::select('campaign_type', 'id', 'name')->get();
        $campaigns = [];
        foreach ($c as $campaign) {
            $campaigns[$campaign->campaign_type][$campaign->id] = $campaign->name;
        }

        $high_rejection_type_names = [
            'd' => 'Duplicate',
            'p' => 'Pre-Pop',
            'f' => 'Filter',
            'a' => 'Acceptable',
        ];
        // return $settings;
        // Log::info(json_decode($settings['campaign_type_benchmarks']));
        return view('admin.settings', compact('settings', 'campaign_types', 'path_order', 'statuses', 'rejection_rates', 'campaign_type_limit', 'campaigns', 'high_rejection_type_names'));
    }

    /**
     * Update Settings
     *
     * @return array
     */
    public function updateSettings(AdminSettingRequest $request)
    {
        // \Log::info($request->all());
        $return_data = [];
        $inputs = $request->all();
        $userActionLogs = [];
        $user = auth()->user();

        if ($inputs['send_pending_lead_cron_expiration_update'] == 1) {
            //send pending leads cron data
            $sendingPendingLeadCron = Setting::find($inputs['send_pending_lead_cron_expiration_id']);
            $entry = $this->logger->createUserActionLogEntry('send_pending_lead_cron_expiration', $sendingPendingLeadCron->integer_value, $inputs['send_pending_lead_cron_expiration']);

            if ($entry != null) {
                array_push($userActionLogs, $entry);
            }
            $sendingPendingLeadCron->integer_value = $inputs['send_pending_lead_cron_expiration'];
            $sendingPendingLeadCron->save();
        }

        // $numCampaignPerStack = Setting::find($inputs['number_of_campaign_per_stack_id']);
        // $numCampaignPerStack->integer_value = $inputs['number_of_campaign_per_stack'];
        // $numCampaignPerStack->save();

        if ($inputs['stack_page_order'] != '') {
            $stackPageOrder = Setting::find($inputs['stack_page_order_id']);
            $entry = $this->logger->createUserActionLogEntry('stack_page_order', $stackPageOrder->string_value, $inputs['stack_page_order']);
            if ($entry != null) {
                array_push($userActionLogs, $entry);
            }
            $stackPageOrder->string_value = $inputs['stack_page_order'];
            $stackPageOrder->save();
        }

        if ($inputs['campaign_filter_process_status_update'] == 1) {
            $campaignFilterProcessStatus = Setting::find($inputs['campaign_filter_process_status_id']);
            $entry = $this->logger->createUserActionLogEntry('campaign_filter_process_status', $campaignFilterProcessStatus->integer_value, $inputs['campaign_filter_process_status']);
            if ($entry != null) {
                array_push($userActionLogs, $entry);
            }
            $campaignFilterProcessStatus->integer_value = $inputs['campaign_filter_process_status'];
            $campaignFilterProcessStatus->save();
        }

        if ($inputs['leads_archiving_age_in_days_update'] == 1) {
            $leadsArchivingAge = Setting::find($inputs['leads_archiving_age_in_days_id']);
            $entry = $this->logger->createUserActionLogEntry('leads_archiving_age_in_days', $leadsArchivingAge->integer_value, $inputs['leads_archiving_age_in_days']);
            if ($entry != null) {
                array_push($userActionLogs, $entry);
            }
            $leadsArchivingAge->integer_value = $inputs['leads_archiving_age_in_days'];
            $leadsArchivingAge->save();
        }

        /* Lead Rejection Rate */
        if ($inputs['update_rejection_rate'] == 1) {

            $new_rates = [$inputs['min_high_reject_rate'], $inputs['max_high_reject_rate']];
            $new_rates_str = json_encode($new_rates);

            $rej_rate = Setting::find($inputs['high_critical_rejection_rate_id']);
            $entry = $this->logger->createUserActionLogEntry('min_high_reject_rate', $rej_rate->string_value, $new_rates_str);
            if ($entry != null) {
                array_push($userActionLogs, $entry);
            }
            $rej_rate->string_value = $new_rates_str;
            $rej_rate->save();

            $return_data['new_rates'] = $new_rates;
        }

        if ($inputs['num_leads_to_process_for_send_pending_leads_update'] == 1) {
            /* num_leads_to_process_for_send_pending_leads */
            $sendPendingNumLeads = Setting::find($inputs['num_leads_to_process_for_send_pending_leads_id']);
            $entry = $this->logger->createUserActionLogEntry('num_leads_to_process_for_send_pending_leads', $sendPendingNumLeads->integer_value, $inputs['num_leads_to_process_for_send_pending_leads']);
            if ($entry != null) {
                array_push($userActionLogs, $entry);
            }
            $sendPendingNumLeads->integer_value = $inputs['num_leads_to_process_for_send_pending_leads'];
            $sendPendingNumLeads->save();
        }

        $campaignReorderingStatus = Setting::find($inputs['campaign_reordering_status_id']);

        // Log::info('input reordering status: '.$inputs['campaign_reordering_status']);
        // Log::info('db reordering status: '.$campaignReorderingStatus->integer_value);

        if ($inputs['campaign_reordering_status'] == 1 && ($inputs['campaign_reordering_status'] != $campaignReorderingStatus->integer_value)) {
            //this means that the reordering was activated again so reset the reference date
            $campaignTypeViewReferenceDate = Setting::where('code', '=', 'campaign_type_view_reference_date')->first();

            if (isset($campaignTypeViewReferenceDate)) {
                $campaignTypeViewReferenceDate->date_value = Carbon::now()->toDateString();
                $campaignTypeViewReferenceDate->save();
            }

        }

        $entry = $this->logger->createUserActionLogEntry('campaign_reordering_status', $campaignReorderingStatus->integer_value, $inputs['campaign_reordering_status']);
        if ($entry != null) {
            array_push($userActionLogs, $entry);
        }
        $campaignReorderingStatus->integer_value = $inputs['campaign_reordering_status'];
        $campaignReorderingStatus->save();

        if ($inputs['campaign_type_view_count_update'] == 1) {
            $campaignTypeViewCount = Setting::find($inputs['campaign_type_view_count_id']);
            $entry = $this->logger->createUserActionLogEntry('campaign_type_view_count', $campaignTypeViewCount->integer_value, $inputs['campaign_type_view_count']);
            if ($entry != null) {
                array_push($userActionLogs, $entry);
            }
            $campaignTypeViewCount->integer_value = $inputs['campaign_type_view_count'];
            $campaignTypeViewCount->save();
        }

        /* user_nos_before_not_displaying_campaign */
        if ($inputs['user_nos_before_not_displaying_campaign_update'] == 1) {
            $campaignNoTracking = Setting::find($inputs['user_nos_before_not_displaying_campaign_id']);
            $entry = $this->logger->createUserActionLogEntry('user_nos_before_not_displaying_campaign', $campaignNoTracking->integer_value, $inputs['user_nos_before_not_displaying_campaign']);
            if ($entry != null) {
                array_push($userActionLogs, $entry);
            }
            $campaignNoTracking->integer_value = $inputs['user_nos_before_not_displaying_campaign'];
            $campaignNoTracking->save();
        }

        /* campaign_type_path_limit */
        $campaign_type_path_limit = $inputs['page_limit'];
        foreach ($inputs['page_limit'] as $type => $limit) {
            if ($limit == '' || $limit == null || ! is_numeric($limit) || $limit < 1) {
                // Log::info($type);
                unset($campaign_type_path_limit[$type]);
            }
        }

        $campaign_type_path_limit = count($campaign_type_path_limit) > 0 ? json_encode($campaign_type_path_limit) : null;

        $campaignTypePathLimit = Setting::find($inputs['campaign_type_path_limit_id']);
        $entry = $this->logger->createUserActionLogEntry('page_limit', $campaignTypePathLimit->description, $campaign_type_path_limit);
        if ($entry != null) {
            array_push($userActionLogs, $entry);
        }
        $campaignTypePathLimit->description = $campaign_type_path_limit;
        $campaignTypePathLimit->save();

        $mixedCoregCampaignReorderingStatus = Setting::find($inputs['mixed_coreg_campaign_reordering_status_id']);

        if ($inputs['mixed_coreg_campaign_reordering_status'] == 1 && ($inputs['mixed_coreg_campaign_reordering_status'] != $mixedCoregCampaignReorderingStatus->integer_value)) {
            //this means that the reordering was activated again so reset the reference date
            $mixedCoregCampaignReorderingReferenceDate = Setting::where('code', '=', 'mixed_coreg_campaign_reordering_reference_date')->first();

            if (isset($mixedCoregCampaignReorderingReferenceDate)) {
                $mixedCoregCampaignReorderingReferenceDate->date_value = Carbon::now()->toDateString();
                $mixedCoregCampaignReorderingReferenceDate->save();
            }

        }

        $entry = $this->logger->createUserActionLogEntry('mixed_coreg_campaign_reordering_status', $mixedCoregCampaignReorderingStatus->integer_value, $inputs['mixed_coreg_campaign_reordering_status']);
        if ($entry != null) {
            array_push($userActionLogs, $entry);
        }
        $mixedCoregCampaignReorderingStatus->integer_value = $inputs['mixed_coreg_campaign_reordering_status'];
        $mixedCoregCampaignReorderingStatus->save();

        if ($inputs['cake_conversions_archiving_age_in_days_update'] == 1) {
            $cakeConversionsArchivingAge = Setting::find($inputs['cake_conversions_archiving_age_in_days_id']);
            $entry = $this->logger->createUserActionLogEntry('cake_conversions_archiving_age_in_days', $cakeConversionsArchivingAge->integer_value, $inputs['cake_conversions_archiving_age_in_days']);
            if ($entry != null) {
                array_push($userActionLogs, $entry);
            }
            $cakeConversionsArchivingAge->integer_value = $inputs['cake_conversions_archiving_age_in_days'];
            $cakeConversionsArchivingAge->save();
        }

        /* campaign type benchmark */
        $campaignTypeBenchmark = Setting::find($inputs['campaign_type_benchmarks_id']);
        $entry = $this->logger->createUserActionLogEntry('campaign_type_benchmark', $campaignTypeBenchmark->description, json_encode($inputs['campaign_type_benchmark']));
        if ($entry != null) {
            array_push($userActionLogs, $entry);
        }
        $campaignTypeBenchmark->description = json_encode($inputs['campaign_type_benchmark']);
        $campaignTypeBenchmark->save();

        if ($inputs['recipient_change'] == 1) {
            //check if there are changes in email recipients
            /* Default Admin Email */
            $adminDefaultEmail = Setting::find($inputs['default_admin_email_id']);
            $entry = $this->logger->createUserActionLogEntry('default_admin_email', $adminDefaultEmail->string_value, $inputs['default_admin_email']);
            if ($entry != null) {
                array_push($userActionLogs, $entry);
            }
            $adminDefaultEmail->string_value = $inputs['default_admin_email'];
            $adminDefaultEmail->save();

            /* Report Recipient */
            $reportRcpnt = Setting::find($inputs['report_recipient_id']);
            $entry = $this->logger->createUserActionLogEntry('report_recipient', $reportRcpnt->description, $inputs['report_recipient']);
            if ($entry != null) {
                array_push($userActionLogs, $entry);
            }
            $reportRcpnt->description = $inputs['report_recipient'];
            $reportRcpnt->save();

            /* QA Recipient */
            $qaRcpnt = Setting::find($inputs['qa_recipient_id']);
            $entry = $this->logger->createUserActionLogEntry('qa_recipient', $qaRcpnt->description, $inputs['qa_recipient']);
            if ($entry != null) {
                array_push($userActionLogs, $entry);
            }
            $qaRcpnt->description = $inputs['qa_recipient'];
            $qaRcpnt->save();

            /* Error Recipient */
            $erRcpnt = Setting::find($inputs['error_email_recipient_id']);
            $entry = $this->logger->createUserActionLogEntry('error_email_recipient', $erRcpnt->description, $inputs['error_email_recipient']);
            if ($entry != null) {
                array_push($userActionLogs, $entry);
            }
            $erRcpnt->description = $inputs['error_email_recipient'];
            $erRcpnt->save();

            /* JS Midpath Recipient */
            $erRcpnt = Setting::find($inputs['js_midpath_email_report_id']);
            $entry = $this->logger->createUserActionLogEntry('js_midpath_email_report', $erRcpnt->description, $inputs['js_midpath_email_report']);
            if ($entry != null) {
                array_push($userActionLogs, $entry);
            }
            $erRcpnt->description = $inputs['js_midpath_email_report'];
            $erRcpnt->save();
        }

        /* PIXELS */
        //Header Pixel
        if ($inputs['header_pixel_status'] == 1) {
            $pixel_header = Setting::find($inputs['header_pixel_id']);
            $entry = $this->logger->createUserActionLogEntry('header_pixel', $pixel_header->description, $inputs['header_pixel']);
            if ($entry != null) {
                array_push($userActionLogs, $entry);
            }
            $pixel_header->description = $inputs['header_pixel'];
            $pixel_header->save();
        }

        //Body Pixel
        if ($inputs['body_pixel_status'] == 1) {
            $pixel_body = Setting::find($inputs['body_pixel_id']);
            $entry = $this->logger->createUserActionLogEntry('body_pixel', $pixel_body->description, $inputs['body_pixel']);
            if ($entry != null) {
                array_push($userActionLogs, $entry);
            }
            $pixel_body->description = $inputs['body_pixel'];
            $pixel_body->save();
        }

        //Footer Pixel
        if ($inputs['footer_pixel_status'] == 1) {
            $pixel_footer = Setting::find($inputs['footer_pixel_id']);
            $entry = $this->logger->createUserActionLogEntry('footer_pixel', $pixel_footer->description, $inputs['footer_pixel']);
            if ($entry != null) {
                array_push($userActionLogs, $entry);
            }
            $pixel_footer->description = $inputs['footer_pixel'];
            $pixel_footer->save();
        }

        if ($inputs['high_rejection_keywords_update'] == 1) {
            $keywords = json_encode($inputs['high_rejection_keywords']);
            $reject_keywords = Setting::find($inputs['high_rejection_keywords_id']);
            $entry = $this->logger->createUserActionLogEntry('high_rejection_keywords', $reject_keywords->description, $keywords);
            if ($entry != null) {
                array_push($userActionLogs, $entry);
            }
            $reject_keywords->description = $keywords;
            $reject_keywords->save();
        }

        if ($inputs['cplchecker_excluded_campaigns_update'] == 1) {
            $keywords = $inputs['cplchecker_excluded_campaigns'];
            $cplchecker_excluded_campaigns = Setting::find($inputs['cplchecker_excluded_campaigns_id']);
            $entry = $this->logger->createUserActionLogEntry('cplchecker_excluded_campaigns', $cplchecker_excluded_campaigns->description, $keywords);
            if ($entry != null) {
                array_push($userActionLogs, $entry);
            }
            $cplchecker_excluded_campaigns->description = $keywords;
            $cplchecker_excluded_campaigns->save();
        }

        if ($inputs['nocpl_recipient_update'] == 1) {
            $keywords = $inputs['nocpl_recipient'];
            $nocpl_recipient = Setting::find($inputs['nocpl_recipient_id']);
            $entry = $this->logger->createUserActionLogEntry('nocpl_recipient', $nocpl_recipient->description, $keywords);
            if ($entry != null) {
                array_push($userActionLogs, $entry);
            }
            $nocpl_recipient->description = $keywords;
            $nocpl_recipient->save();
        }

        if ($inputs['optoutreport_recipient_update'] == 1) {
            $keywords = $inputs['optoutreport_recipient'];
            $optoutreport_recipient = Setting::find($inputs['optoutreport_recipient_id']);
            $entry = $this->logger->createUserActionLogEntry('optoutreport_recipient', $optoutreport_recipient->description, $keywords);
            if ($entry != null) {
                array_push($userActionLogs, $entry);
            }
            $optoutreport_recipient->description = $keywords;
            $optoutreport_recipient->save();
        }

        if ($inputs['ccpaadminemail_recipient_update'] == 1) {
            $keywords = $inputs['ccpaadminemail_recipient'];
            $ccpaadminemail_recipient = Setting::find($inputs['ccpaadminemail_recipient_id']);
            $entry = $this->logger->createUserActionLogEntry('ccpaadminemail_recipient', $ccpaadminemail_recipient->description, $keywords);
            if ($entry != null) {
                array_push($userActionLogs, $entry);
            }
            $ccpaadminemail_recipient->description = $keywords;
            $ccpaadminemail_recipient->save();
        }

        if ($inputs['optoutexternal_recipient_update'] == 1) {
            $keywords = $inputs['optoutexternal_recipient'];
            $optoutexternal_recipient = Setting::find($inputs['optoutexternal_recipient_id']);
            $entry = $this->logger->createUserActionLogEntry('optoutexternal_recipient', $optoutexternal_recipient->description, $keywords);
            if ($entry != null) {
                array_push($userActionLogs, $entry);
            }
            $optoutexternal_recipient->description = $keywords;
            $optoutexternal_recipient->save();
        }

        if ($inputs['optoutemail_replyto_update'] == 1) {
            $keywords = $inputs['optoutemail_replyto'];
            $optoutemail_replyto = Setting::find($inputs['optoutemail_replyto_id']);
            $entry = $this->logger->createUserActionLogEntry('optoutemail_replyto', $optoutemail_replyto->description, $keywords);
            if ($entry != null) {
                array_push($userActionLogs, $entry);
            }
            $optoutemail_replyto->description = $keywords;
            $optoutemail_replyto->save();
        }

        if ($inputs['admired_tcpa_status'] == 1) {
            $keywords = $inputs['admired_tcpa'];
            $admired_tcpa = Setting::find($inputs['admired_tcpa_id']);
            $entry = $this->logger->createUserActionLogEntry('admired_tcpa', $admired_tcpa->description, $keywords);
            if ($entry != null) {
                array_push($userActionLogs, $entry);
            }
            $admired_tcpa->description = $keywords;
            $admired_tcpa->save();
        }

        if ($inputs['pfr_tcpa_status'] == 1) {
            $keywords = $inputs['pfr_tcpa'];
            $pfr_tcpa = Setting::find($inputs['pfr_tcpa_id']);
            $entry = $this->logger->createUserActionLogEntry('pfr_tcpa', $pfr_tcpa->description, $keywords);
            if ($entry != null) {
                array_push($userActionLogs, $entry);
            }
            $pfr_tcpa->description = $keywords;
            $pfr_tcpa->save();
        }

        if ($inputs['epicdemand_tcpa_status'] == 1) {
            $keywords = $inputs['epicdemand_tcpa'];
            $epicdemand_tcpa = Setting::find($inputs['epicdemand_tcpa_id']);
            $entry = $this->logger->createUserActionLogEntry('epicdemand_tcpa', $epicdemand_tcpa->description, $keywords);
            if ($entry != null) {
                array_push($userActionLogs, $entry);
            }
            $epicdemand_tcpa->description = $keywords;
            $epicdemand_tcpa->save();
        }

        if ($inputs['clinical_trial_tcpa_status'] == 1) {
            $keywords = $inputs['clinical_trial_tcpa'];
            $clinical_trial_tcpa = Setting::find($inputs['clinical_trial_tcpa_id']);
            $entry = $this->logger->createUserActionLogEntry('clinical_trial_tcpa', $clinical_trial_tcpa->description, $keywords);
            if ($entry != null) {
                array_push($userActionLogs, $entry);
            }
            $clinical_trial_tcpa->description = $keywords;
            $clinical_trial_tcpa->save();
        }

        if ($inputs['publisher_percentage_revenue_update'] == 1) {
            $keywords = $inputs['publisher_percentage_revenue'];
            $publisher_percentage_revenue = Setting::find($inputs['publisher_percentage_revenue_id']);
            $entry = $this->logger->createUserActionLogEntry('publisher_percentage_revenue', $publisher_percentage_revenue->description, $keywords);
            if ($entry != null) {
                array_push($userActionLogs, $entry);
            }
            $publisher_percentage_revenue->description = $keywords;
            $publisher_percentage_revenue->save();
        }

        if ($inputs['data_feed_excluded_affiliates_update'] == 1) {
            $keywords = $inputs['data_feed_excluded_affiliates'];
            $data_feed_excluded_affiliates = Setting::find($inputs['data_feed_excluded_affiliates_id']);
            $entry = $this->logger->createUserActionLogEntry('data_feed_excluded_affiliates', $data_feed_excluded_affiliates->description, $keywords);
            if ($entry != null) {
                array_push($userActionLogs, $entry);
            }
            $data_feed_excluded_affiliates->description = $keywords;
            $data_feed_excluded_affiliates->save();
        }

        if ($inputs['excessive_affiliate_subids_update'] == 1) {
            $keywords = $inputs['excessive_affiliate_subids'];
            $excessive_affiliate_subids = Setting::find($inputs['excessive_affiliate_subids_id']);
            $entry = $this->logger->createUserActionLogEntry('excessive_affiliate_subids', $excessive_affiliate_subids->description, $keywords);
            if ($entry != null) {
                array_push($userActionLogs, $entry);
            }
            $excessive_affiliate_subids->description = $keywords;
            $excessive_affiliate_subids->save();
        }

        if ($inputs['full_rejection_alert_status_update'] == 1) {
            $full_rejection_alert_status = Setting::find($inputs['full_rejection_alert_status_id']);
            $entry = $this->logger->createUserActionLogEntry('full_rejection_alert_status', $full_rejection_alert_status->integer_value, $inputs['full_rejection_alert_status']);
            if ($entry != null) {
                array_push($userActionLogs, $entry);
            }
            $full_rejection_alert_status->integer_value = $inputs['full_rejection_alert_status'];
            $full_rejection_alert_status->save();

            $full_rejection_alert_min_leads = Setting::find($inputs['full_rejection_alert_min_leads_id']);
            $entry = $this->logger->createUserActionLogEntry('full_rejection_alert_min_leads', $full_rejection_alert_min_leads->description, $inputs['full_rejection_alert_min_leads']);
            if ($entry != null) {
                array_push($userActionLogs, $entry);
            }
            $full_rejection_alert_min_leads->description = $inputs['full_rejection_alert_min_leads'];
            $full_rejection_alert_min_leads->save();

            $full_rejection_alert_check_days = Setting::find($inputs['full_rejection_alert_check_days_id']);
            $entry = $this->logger->createUserActionLogEntry('full_rejection_alert_check_days', $full_rejection_alert_check_days->description, $inputs['full_rejection_alert_check_days']);
            if ($entry != null) {
                array_push($userActionLogs, $entry);
            }
            $full_rejection_alert_check_days->description = $inputs['full_rejection_alert_check_days'];
            $full_rejection_alert_check_days->save();

            $fraes = isset($inputs['full_rejection_advertiser_email_status']) ? 1 : 0;
            $full_rejection_advertiser_email_status = Setting::find($inputs['full_rejection_advertiser_email_status_id']);
            $entry = $this->logger->createUserActionLogEntry('full_rejection_advertiser_email_status', $full_rejection_advertiser_email_status->integer_value, $fraes);
            if ($entry != null) {
                array_push($userActionLogs, $entry);
            }
            $full_rejection_advertiser_email_status->integer_value = $fraes;
            $full_rejection_advertiser_email_status->save();

            $full_rejection_excluded_campaigns = Setting::find($inputs['full_rejection_excluded_campaigns_id']);
            $entry = $this->logger->createUserActionLogEntry('full_rejection_excluded_campaigns', $full_rejection_excluded_campaigns->description, $inputs['full_rejection_excluded_campaigns']);
            if ($entry != null) {
                array_push($userActionLogs, $entry);
            }
            $full_rejection_excluded_campaigns->description = $inputs['full_rejection_excluded_campaigns'];
            $full_rejection_excluded_campaigns->save();

            $frdcs = isset($inputs['full_rejection_deactivate_campaign_status']) ? 1 : 0;
            $full_rejection_deactivate_campaign_status = Setting::find($inputs['full_rejection_deactivate_campaign_status_id']);
            $entry = $this->logger->createUserActionLogEntry('full_rejection_deactivate_campaign_status', $full_rejection_deactivate_campaign_status->integer_value, $frdcs);
            if ($entry != null) {
                array_push($userActionLogs, $entry);
            }
            $full_rejection_deactivate_campaign_status->integer_value = $frdcs;
            $full_rejection_deactivate_campaign_status->save();
        }

        if ($inputs['click_log_tracing_update'] == 1) {
            // $click_log_num_days = Setting::find($inputs['click_log_num_days_id']);
            // $entry = $this->logger->createUserActionLogEntry('click_log_num_days', $click_log_num_days->integer_value, $inputs['click_log_num_days']);
            // if($entry != null){
            //     array_push($userActionLogs, $entry);
            // }
            // $click_log_num_days->integer_value = $inputs['click_log_num_days'];
            // $click_log_num_days->save();
            $val = isset($inputs['clic_logs_apply_all_affiliates']) ? 1 : 0;
            $clic_logs_apply_all_affiliates = Setting::find($inputs['clic_logs_apply_all_affiliates_id']);
            $entry = $this->logger->createUserActionLogEntry('clic_logs_apply_all_affiliates', $clic_logs_apply_all_affiliates->integer_value, $val);
            if ($entry != null) {
                array_push($userActionLogs, $entry);
            }
            $clic_logs_apply_all_affiliates->integer_value = $val;
            $clic_logs_apply_all_affiliates->save();
        }

        event(new UserActionEvent($userActionLogs));

        return $return_data;
        // session()->flash('flash_message_alert_class','alert-success');
        // session()->flash('flash_message','Settings Successfully Updated!');
        // return redirect('admin/settings');
    }

    /**
     * Preview Campaign Contents
     */
    public function preview_content(Request $request): View
    {
        $content = $request->input('content');
        $type = $request->input('type');
        Session::flash('content', $this->convert_content($content));

        if ($type == 'falling-money') {
            return view('preview.falling_money');
        }
    }

    /**
     * Content conversion
     *
     * @return mixed
     */
    private function convert_content($html)
    {
        if (strpos($html, '[VALUE_REV_TRACKER]') !== false) {
            $html = str_replace('[VALUE_REV_TRACKER]', '', $html);
        }
        if (strpos($html, '[VALUE_AFFILIATE_ID]') !== false) {
            $html = str_replace('[VALUE_AFFILIATE_ID]', '', $html);
        }
        if (strpos($html, '[VALUE_PUB_TIME]') !== false) {
            $html = str_replace('[VALUE_PUB_TIME]', '', $html);
        }
        if (strpos($html, '[VALUE_DOBMONTH]') !== false) {
            $html = str_replace('[VALUE_DOBMONTH]', '', $html);
        }
        if (strpos($html, '[VALUE_DOBDAY]') !== false) {
            $html = str_replace('[VALUE_DOBDAY]', '', $html);
        }
        if (strpos($html, '[VALUE_DOBYEAR]') !== false) {
            $html = str_replace('[VALUE_DOBYEAR]', '', $html);
        }
        if (strpos($html, '[VALUE_EMAIL]') !== false) {
            $html = str_replace('[VALUE_EMAIL]', '', $html);
        }
        if (strpos($html, '[VALUE_FIRST_NAME]') !== false) {
            $html = str_replace('[VALUE_FIRST_NAME]', '', $html);
        }
        if (strpos($html, '[VALUE_LAST_NAME]') !== false) {
            $html = str_replace('[VALUE_LAST_NAME]', '', $html);
        }
        if (strpos($html, '[VALUE_ZIP]') !== false) {
            $html = str_replace('[VALUE_ZIP]', '', $html);
        }
        if (strpos($html, '[VALUE_CITY]') !== false) {
            $html = str_replace('[VALUE_CITY]', '', $html);
        }
        if (strpos($html, '[VALUE_STATE]') !== false) {
            $html = str_replace('[VALUE_STATE]', '', $html);
        }
        if (strpos($html, '[VALUE_GENDER]') !== false) {
            $html = str_replace('[VALUE_GENDER]', '', $html);
        }
        if (strpos($html, '[VALUE_GENDER_FULL]') !== false) {
            $html = str_replace('[VALUE_GENDER_FULL]', '', $html);
        }
        if (strpos($html, '[VALUE_BIRTHDATE]') !== false) {
            $html = str_replace('[VALUE_BIRTHDATE]', '', $html);
        }
        if (strpos($html, '[VALUE_IP]') !== false) {
            $html = str_replace('[VALUE_IP]', '', $html);
        }
        if (strpos($html, '[VALUE_ADDRESS1]') !== false) {
            $html = str_replace('[VALUE_ADDRESS1]', '', $html);
        }
        if (strpos($html, '[VALUE_PHONE]') !== false) {
            $html = str_replace('[VALUE_PHONE]', '', $html);
        }
        if (strpos($html, '[VALUE_PHONE1]') !== false) {
            $html = str_replace('[VALUE_PHONE1]', '', $html);
        }
        if (strpos($html, '[VALUE_PHONE2]') !== false) {
            $html = str_replace('[VALUE_PHONE2]', '', $html);
        }
        if (strpos($html, '[VALUE_PHONE3]') !== false) {
            $html = str_replace('[VALUE_PHONE3]', '', $html);
        }
        if (strpos($html, '[VALUE_TITLE]') !== false) {
            $html = str_replace('[VALUE_TITLE]', '', $html);
        }
        if (strpos($html, '[VALUE_TODAY]') !== false) {
            $html = str_replace('[VALUE_TODAY]', '', $html);
        }
        if (strpos($html, '[VALUE_DATE_TIME]') !== false) {
            $html = str_replace('[VALUE_DATE_TIME]', '', $html);
        }

        if (strpos($html, '[VALUE_TODAY_MONTH]') !== false) {
            $html = str_replace('[VALUE_TODAY_MONTH]', '', $html);
        }
        if (strpos($html, '[VALUE_TODAY_DAY]') !== false) {
            $html = str_replace('[VALUE_TODAY_DAY]', '', $html);
        }
        if (strpos($html, '[VALUE_TODAY_YEAR]') !== false) {
            $html = str_replace('[VALUE_TODAY_YEAR]', '', $html);
        }
        if (strpos($html, '[VALUE_TODAY_HOUR]') !== false) {
            $html = str_replace('[VALUE_TODAY_HOUR]', '', $html);
        }
        if (strpos($html, '[VALUE_TODAY_MIN]') !== false) {
            $html = str_replace('[VALUE_TODAY_MIN]', '', $html);
        }
        if (strpos($html, '[VALUE_TODAY_SEC]') !== false) {
            $html = str_replace('[VALUE_TODAY_SEC]', '', $html);
        }
        if (strpos($html, '[VALUE_AGE]') !== false) {
            $html = str_replace('[VALUE_AGE]', '', $html);
        }
        if (strpos($html, '[VALUE_ETHNICITY]') !== false) {
            $html = str_replace('[VALUE_ETHNICITY]', '', $html);
        }

        /*$html = eval('?>'.$html.'<?php;');*/

        return $html;
    }

    /**
     * Short codes page
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function shortcodes(): View
    {
        return view('campaign.content_shortcode_info');
    }

    /**
     * Short codes page
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function eiqHttpRequestDoc(): View
    {
        return view('campaign.eiq_http_request_info');
    }

    /**
     * Get Received Statistics By Affiliate, Get Total Received Revenue Statistics & Total Survey Takers Per Revenue Tracker / Affiliate
     */
    public function getDashboardGraphsStatisticsProcessor(Request $request): JsonResponse
    {
        Validator::extend('date_greater_equal', function ($attribute, $value, $parameters) {
            $max = Carbon::parse($value);
            $min = Carbon::parse(Input::get($parameters[0]));

            return isset($min) and $max->gte($min);
        });

        // DB::enableQueryLog();
        $this->validate($request, [
            'from_date' => 'required_with:to_date|date',
            'to_date' => 'required_with:from_date|date|date_greater_equal:from_date',
        ]);

        $from_date = $request->input('from_date');
        $to_date = $request->input('to_date');

        if ($from_date == '' || $to_date == '') {
            $start_date = Carbon::now()->subDay(7)->startOfDay();
            $end_date = Carbon::now()->subDay()->endOfDay();
        } else {
            $start_date = Carbon::parse($from_date)->startOfDay();
            $end_date = Carbon::parse($to_date)->endOfDay();
        }

        /* Get Received Statistics By Affiliate */
        $selectSQL = "SELECT leads.affiliate_id,CONCAT(affiliates.company,'(',leads.affiliate_id,')') AS affiliate, SUM(leads.received) as Received, leads.created_at FROM leads INNER JOIN affiliates ON leads.affiliate_id=affiliates.id WHERE leads.lead_status=1 AND leads.created_at BETWEEN '$start_date' AND '$end_date' GROUP BY leads.affiliate_id, date(leads.created_at) ORDER BY leads.created_at, affiliate";
        $affiliates = DB::select($selectSQL);

        $stats = [];
        $aff_ids = [];
        foreach ($affiliates as $affiliate) {
            $the_date = Carbon::parse($affiliate->created_at)->toDateString();
            $stats[$the_date][] = $affiliate;
            if (! in_array($affiliate->affiliate_id, $aff_ids)) {
                $aff_ids[] = $affiliate->affiliate_id;
            }
        }

        $c = 0;
        $affiliate_names = [];
        $statistics = [];

        foreach ($stats as $date => $affiliates) {
            $total_received = 0;
            $statistics[$c]['date'] = Carbon::parse($date)->format('M d');
            $statistics[$c]['created_at'] = $date;
            foreach ($affiliates as $affiliate) {
                $statistics[$c][$affiliate->affiliate_id] = $affiliate->Received;
                if (! in_array($affiliate->affiliate, $affiliate_names)) {
                    $affiliate_names[] = $affiliate->affiliate;
                }
                $total_received += $affiliate->Received;
            }
            $statistics[$c]['total_revenue'] = $total_received;
            $c++;
        }

        $the_data['daily_revenue_affiliate']['data'] = $statistics;
        $the_data['daily_revenue_affiliate']['ykeys'] = $aff_ids;
        $the_data['daily_revenue_affiliate']['labels'] = $affiliate_names;

        /* Get Total Received Revenue Statistics */
        $selectSQL = "SELECT DATE_FORMAT(created_at,'%b %d') as date, SUM(received) as received, Count(*) as total FROM leads WHERE lead_status=1 AND created_at BETWEEN '$start_date' AND '$end_date' GROUP BY date(created_at) ORDER BY created_at";
        $revenue = DB::select($selectSQL);
        $the_data['daily_total_revenue'] = $revenue;

        /* Total Survey Takers Per Revenue Tracker / Affiliate */
        $selectSQL = "SELECT lu.revenue_tracker_id, CONCAT(a.company,'(',lu.revenue_tracker_id,')') AS affiliate, DATE(lu.created_at) as date, COUNT(*) as total FROM lead_users lu INNER JOIN affiliates a ON lu.revenue_tracker_id = a.id WHERE lu.created_at BETWEEN '$start_date' AND '$end_date' GROUP BY lu.revenue_tracker_id, DATE(lu.created_at) ORDER BY date ASC, total DESC";
        $affiliates = DB::select($selectSQL);
        $stats = [];
        $aff_ids = [];
        foreach ($affiliates as $affiliate) {
            $the_date = Carbon::parse($affiliate->date)->toDateString();
            $stats[$the_date][] = $affiliate;
            if (! in_array($affiliate->revenue_tracker_id, $aff_ids)) {
                $aff_ids[] = $affiliate->revenue_tracker_id;
            }
        }

        $c = 0;
        $affiliate_names = [];
        $statistics = [];

        foreach ($stats as $date => $affiliates) {
            $total_registrations = 0;
            $statistics[$c]['date'] = Carbon::parse($date)->format('M d');
            $statistics[$c]['created_at'] = $date;
            foreach ($affiliates as $affiliate) {
                $statistics[$c][$affiliate->revenue_tracker_id] = $affiliate->total;
                if (! in_array($affiliate->affiliate, $affiliate_names)) {
                    $affiliate_names[] = $affiliate->affiliate;
                }
                $total_registrations += $affiliate->total;
            }
            $statistics[$c]['total_registrations'] = $total_registrations;
            $c++;
        }

        $the_data['daily_affilate_registrations']['value'] = $affiliates;
        $the_data['daily_affilate_registrations']['data'] = $statistics;
        $the_data['daily_affilate_registrations']['ykeys'] = $aff_ids;
        $the_data['daily_affilate_registrations']['labels'] = $affiliate_names;

        // Log::info(DB::getQueryLog());
        return response()->json($the_data);
    }

    /* DAILY TOTAL REVENUE */
    public function getReceivedRevenueStatisticsDashboard(Request $request): JsonResponse
    {
        $from_date = $request->input('from_date');
        $to_date = $request->input('to_date');

        if ($from_date == '' || $to_date == '') {
            $start_date = Carbon::now()->subDay(7)->startOfDay();
            $end_date = Carbon::now()->subDay()->endOfDay();
        } else {
            $start_date = Carbon::parse($from_date)->startOfDay();
            $end_date = Carbon::parse($to_date)->endOfDay();
        }

        /* Get Total Received Revenue Statistics */
        $selectSQL = "SELECT DATE_FORMAT(created_at,'%b %d') as date, SUM(received) as received, Count(*) as total FROM leads WHERE lead_status=1 AND created_at BETWEEN '$start_date' AND '$end_date' GROUP BY date(created_at) ORDER BY created_at";
        $revenue = DB::select($selectSQL);
        $the_data['daily_total_revenue'] = $revenue;

        // Log::info(DB::getQueryLog());
        return response()->json($the_data);
    }

    /* DAILY AFFILIATE REVENUE */
    public function getAffiliateRevenueStatisticsDashboard(Request $request): JsonResponse
    {
        $from_date = $request->input('from_date');
        $to_date = $request->input('to_date');

        if ($from_date == '' || $to_date == '') {
            $start_date = Carbon::now()->subDay(7)->startOfDay();
            $end_date = Carbon::now()->subDay()->endOfDay();
        } else {
            $start_date = Carbon::parse($from_date)->startOfDay();
            $end_date = Carbon::parse($to_date)->endOfDay();
        }

        /* Get Received Statistics By Affiliate */
        $selectSQL = "SELECT leads.affiliate_id,CONCAT(affiliates.company,'(',leads.affiliate_id,')') AS affiliate, SUM(leads.received) as Received, leads.created_at FROM leads INNER JOIN affiliates ON leads.affiliate_id=affiliates.id WHERE leads.lead_status=1 AND leads.created_at BETWEEN '$start_date' AND '$end_date' GROUP BY leads.affiliate_id, date(leads.created_at) ORDER BY leads.created_at, affiliate";
        $affiliates = DB::select($selectSQL);

        $stats = [];
        $aff_ids = [];
        foreach ($affiliates as $affiliate) {
            $the_date = Carbon::parse($affiliate->created_at)->toDateString();
            $stats[$the_date][] = $affiliate;
            if (! in_array($affiliate->affiliate_id, $aff_ids)) {
                $aff_ids[] = $affiliate->affiliate_id;
            }
        }

        $c = 0;
        $affiliate_names = [];
        $statistics = [];

        foreach ($stats as $date => $affiliates) {
            $total_received = 0;
            $statistics[$c]['date'] = Carbon::parse($date)->format('M d');
            $statistics[$c]['created_at'] = $date;
            foreach ($affiliates as $affiliate) {
                $statistics[$c][$affiliate->affiliate_id] = $affiliate->Received;
                if (! in_array($affiliate->affiliate, $affiliate_names)) {
                    $affiliate_names[] = $affiliate->affiliate;
                }
                $total_received += $affiliate->Received;
            }
            $statistics[$c]['total_revenue'] = $total_received;
            $c++;
        }

        $the_data['daily_revenue_affiliate']['data'] = $statistics;
        $the_data['daily_revenue_affiliate']['ykeys'] = $aff_ids;
        $the_data['daily_revenue_affiliate']['labels'] = $affiliate_names;

        // Log::info(DB::getQueryLog());
        return response()->json($the_data);
    }

    /* DAILY TOTAL AFFILIATE REGISTRATIONS */
    public function getAffiliateSurveyTakersDashboard(Request $request): JsonResponse
    {
        $from_date = $request->input('from_date');
        $to_date = $request->input('to_date');

        if ($from_date == '' || $to_date == '') {
            $start_date = Carbon::now()->subDay(7)->startOfDay();
            $end_date = Carbon::now()->subDay()->endOfDay();
        } else {
            $start_date = Carbon::parse($from_date)->startOfDay();
            $end_date = Carbon::parse($to_date)->endOfDay();
        }

        /* Total Survey Takers Per Revenue Tracker / Affiliate */
        $selectSQL = "SELECT lu.revenue_tracker_id, CONCAT(a.company,'(',lu.revenue_tracker_id,')') AS affiliate, DATE(lu.created_at) as date, COUNT(*) as total FROM lead_users lu INNER JOIN affiliates a ON lu.revenue_tracker_id = a.id WHERE lu.created_at BETWEEN '$start_date' AND '$end_date' GROUP BY lu.revenue_tracker_id, DATE(lu.created_at) ORDER BY date ASC, total DESC";
        $affiliates = DB::select($selectSQL);
        $stats = [];
        $aff_ids = [];
        foreach ($affiliates as $affiliate) {
            $the_date = Carbon::parse($affiliate->date)->toDateString();
            $stats[$the_date][] = $affiliate;
            if (! in_array($affiliate->revenue_tracker_id, $aff_ids)) {
                $aff_ids[] = $affiliate->revenue_tracker_id;
            }
        }

        $c = 0;
        $affiliate_names = [];
        $statistics = [];

        foreach ($stats as $date => $affiliates) {
            $total_registrations = 0;
            $statistics[$c]['date'] = Carbon::parse($date)->format('M d');
            $statistics[$c]['created_at'] = $date;
            foreach ($affiliates as $affiliate) {
                $statistics[$c][$affiliate->revenue_tracker_id] = $affiliate->total;
                if (! in_array($affiliate->affiliate, $affiliate_names)) {
                    $affiliate_names[] = $affiliate->affiliate;
                }
                $total_registrations += $affiliate->total;
            }
            $statistics[$c]['total_registrations'] = $total_registrations;
            $c++;
        }

        $the_data['daily_affilate_registrations']['value'] = $affiliates;
        $the_data['daily_affilate_registrations']['data'] = $statistics;
        $the_data['daily_affilate_registrations']['ykeys'] = $aff_ids;
        $the_data['daily_affilate_registrations']['labels'] = $affiliate_names;

        // Log::info(DB::getQueryLog());
        return response()->json($the_data);
    }

    /**
     * Get Received Statistics By Affiliate, Get Total Received Revenue Statistics & Total Survey Takers Per Revenue Tracker / Affiliate
     */
    public function downloadAffiliateReport(Request $request): JsonResponse
    {
        // return $request->all();

        $type = $request->input('type');
        $from_date = $request->input('report_from_date');
        $to_date = $request->input('report_to_date');

        if ($from_date == '' || $to_date == '') {
            $start_date = Carbon::now()->subDay(7)->startOfDay();
            $end_date = Carbon::now()->subDay()->endOfDay();
        } else {
            $start_date = Carbon::parse($from_date)->startOfDay();
            $end_date = Carbon::parse($to_date)->endOfDay();
        }

        // Log::info($start_date.':::'.$end_date);

        if ($type == 'revenue') {
            // $selectSQL = "SELECT leads.affiliate_id as revenue_tracker_id, affiliate_revenue_trackers.affiliate_id as affiliate_id, SUM(leads.received) as total FROM leads
            //     INNER JOIN affiliate_revenue_trackers ON leads.affiliate_id = affiliate_revenue_trackers.revenue_tracker_id
            //     WHERE leads.lead_status=1 AND leads.created_at BETWEEN '$start_date' AND '$end_date' GROUP BY revenue_tracker_id ORDER BY affiliate_id, revenue_tracker_id";
            $selectSQL = "SELECT leads.affiliate_id as revenue_tracker_id, SUM(leads.received) as total,
            (SELECT affiliate_id FROM affiliate_revenue_trackers WHERE leads.affiliate_id = affiliate_revenue_trackers.revenue_tracker_id LIMIT 1) AS affiliate_id
            FROM leads
            WHERE leads.lead_status=1 AND leads.created_at BETWEEN '$start_date' AND '$end_date'GROUP BY revenue_tracker_id, affiliate_id ORDER BY affiliate_id, revenue_tracker_id";
            //"GROUP BY revenue_tracker_id, date(leads.created_at) ORDER BY affiliate_id, revenue_tracker_id";
        } else {
            $selectSQL = "SELECT revenue_tracker_id, affiliate_id, COUNT(*) as total FROM lead_users WHERE created_at BETWEEN '$start_date' AND '$end_date' GROUP BY affiliate_id, revenue_tracker_id ORDER BY affiliate_id, revenue_tracker_id";
        }
        $affiliates = DB::select($selectSQL);
        // Log::info($affiliates);
        // return $affiliates;

        $start_date_title = Carbon::parse($start_date)->format('y-m-d');
        $end_date_title = Carbon::parse($end_date)->format('y-m-d');
        $pre_header = 'Total '.ucwords($type).' for '.$start_date_title;
        if ($start_date_title != $end_date_title) {
            $pre_header .= ' to '.$end_date_title;
        }

        $typeTitle = $type == 'revenue' ? 'Rev' : 'Reg';
        // $title = 'AffiliateStatistics_'.Carbon::now()->toDateString();
        // $title = 'AffiliateStatistics_'.strtotime("now");
        $title = 'A'.$typeTitle.'S_'.Carbon::parse($start_date)->format('ymd');
        if ($start_date != $end_date) {
            $title .= '-'.Carbon::parse($end_date)->format('ymd');
        }

        Excel::store(function ($excel) use ($title, $pre_header, $affiliates, $type) {
            $excel->sheet($title, function ($sheet) use ($pre_header, $affiliates, $type) {

                // Set auto size for sheet
                $sheet->setAutoSize(true);

                $sheet->appendRow([$pre_header]);
                $sheet->mergeCells('A1:C1');

                $headers = ['Affiliate ID', 'Revenue Tracker ID', 'Total'];

                //set up the title header row
                $sheet->appendRow($headers);

                $sheet->row(2, function ($row) {
                    $row->setFont([
                        'size' => '12',
                        'bold' => true,
                    ]);
                    $row->setAlignment('center');
                    $row->setValignment('center');
                });

                //style the headers
                $sheet->cells('A1:C1', function ($cells) {
                    // Set font
                    $cells->setFont([
                        'size' => '12',
                        'bold' => true,
                    ]);

                    $cells->setAlignment('center');
                    $cells->setValignment('center');
                });

                $total_total = 0;
                foreach ($affiliates as $affiliate) {
                    if ($type == 'revenue') {
                        $total = sprintf('%0.2f', $affiliate->total);
                        if ($affiliate->affiliate_id == '' || is_null($affiliate->affiliate_id)) {
                            $affiliate_id = $affiliate->revenue_tracker_id;
                            $revenue_tracker = '';
                        } else {
                            $affiliate_id = $affiliate->affiliate_id;
                            $revenue_tracker = $affiliate->revenue_tracker_id;
                        }
                    } else {
                        $affiliate_id = $affiliate->affiliate_id;
                        $revenue_tracker = $affiliate->revenue_tracker_id;
                        $total = $affiliate->total;
                    }
                    $sheet->appendRow([
                        $affiliate_id,
                        $revenue_tracker,
                        $total,
                    ]);
                    $total_total += $total;
                }

                if ($type == 'revenue') {
                    $total_total = sprintf('%0.2f', $total_total);
                } else {
                    $total_total = $total_total;
                }

                $sheet->appendRow([
                    '',
                    '',
                    $total_total,
                ]);
            });
        },$title.'.xls','downloads');

        $file_path = storage_path('downloads').'/'.$title.'.xls';

        if (file_exists($file_path)) {
            return response()->download($file_path, $title.'.xls', [
                'Content-Length: '.filesize($file_path),
            ]);
        } else {
            exit('Requested file does not exist on our server!');
        }
    }

    /**
     * Get Top Campaigns By Leads
     */
    public function getTopCampaignsByLeads(Request $request): JsonResponse
    {
        $date = $request->input('date');
        if ($date == '') {
            $the_date = Carbon::now()->subDay()->toDateString();
        } else {
            $the_date = Carbon::parse($date)->toDateString();
        }

        $campaigns = Lead::select('campaign_id', DB::raw('COUNT(*) as total'))
            ->whereRaw('date(created_at) = "'.$the_date.'"')
            ->groupBy('campaign_id')
            ->orderBy('total', 'desc')
            ->take(10)
            ->pluck('campaign_id')
            ->toArray();

        if (! is_array($campaigns)) {
            $campaigns = [];
        }
        $leads = Lead::join('campaigns', 'leads.campaign_id', '=', 'campaigns.id')
            ->select('leads.campaign_id', 'campaigns.name', 'leads.lead_status', DB::RAW('COUNT(*) as total'))
            ->whereIn('leads.campaign_id', $campaigns)
            ->whereRaw('date(leads.created_at) = "'.$the_date.'"')
            ->groupBy('leads.campaign_id', 'leads.lead_status');

        if (! empty($campaigns)) {
            $leads = $leads->orderByRaw('FIELD(leads.campaign_id, '.implode(',', $campaigns).')');
        }

        $leads = $leads->orderBy('leads.lead_status', 'ASC')
            ->get()->toArray();

        $stats = [];
        $camp_checker = [];
        $count = -1;
        foreach ($leads as $lead) {
            if (! in_array($lead['name'], $camp_checker)) {
                $count++;
                $camp_checker[] = $lead['name'];
                $stats[$count]['lead'] = $lead['name'];
            }
            $stats[$count][$lead['lead_status']] = $lead['total'];
        }

        return response()->json($stats);
    }

    public function apply_to_run(): View
    {
        return view('admin.applyToRun');
    }

    public function categories(): View
    {
        $statuses = config('constants.UNI_STATUS');
        $categories = Category::all()->toArray();

        return view('admin.categories', compact('statuses', 'categories'));
    }

    //Banned Leads && Banned Attempts
    public function banned_leads(): View
    {
        return view('admin.banned.leads');
    }

    public function banned_attempts(): View
    {
        return view('admin.banned.attempts');
    }

    /**
     * Duplicate leads page
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function duplicateLeads(Request $request)
    {
        $inputs = $request->all();
        $leads = [];

        if (count($inputs) > 0) {
            if ($inputs['table'] == 1) {
                //Leads
                $leads = Lead::searchLeads($inputs)
                    ->select('*', DB::raw('COUNT(*) as count'), DB::raw('date(created_at) as date_created'))
                    ->groupBy('lead_email', 'campaign_id', 'affiliate_id', 'lead_status', DB::raw('date(created_at)'))
                    ->get();

            } else {
                //Lead Duplicates
                $leads = LeadDuplicate::searchLeads($inputs)
                    ->select('*', DB::raw('COUNT(*) as count'), DB::raw('date(created_at) as date_created'))
                    ->groupBy('lead_email', 'campaign_id', 'affiliate_id', 'lead_status', DB::raw('date(created_at)'))
                    ->get();
            }

        }
        // Log::info($leads);

        // session()->put('duplicate_leads',$leads);
        $affiliates = Cache::remember('affiliates', 22 * 60 * 60, function () {
            return Bus::dispatch(new GetInternalAffiliatesCompanyIDPair());
        });
        $affiliates = ['' => 'ALL'] + $affiliates;

        session()->put('duplicate_leads_input', $inputs); // Save input in session if user decides to download report

        if ($leads && count($leads) > 0) {
            session()->flash('has_duplicate_leads', true);
        }

        return view('admin.duplicateLeads', compact('leads', 'inputs', 'affiliates'));
    }

    /**
     * Downloading of searched duplicate leads
     */
    public function downloadSearchedDuplicateLeads(): BinaryFileResponse
    {
        $inputs = session()->get('duplicate_leads_input');
        $leads = [];
        if (count($inputs) > 0) {
            if ($inputs['table'] == 1) { //Leads
                $leads = Lead::searchLeads($inputs)
                    ->select('*', DB::raw('COUNT(*) as count'), DB::raw('date(created_at) as date_created'))
                    ->groupBy('lead_email', 'campaign_id', 'affiliate_id', 'lead_status', DB::raw('date(created_at)'))
                    ->get();
            } else { //Lead Duplicates
                $leads = LeadDuplicate::searchLeads($inputs)
                    ->select('*', DB::raw('COUNT(*) as count'), DB::raw('date(created_at) as date_created'))
                    ->groupBy('lead_email', 'campaign_id', 'affiliate_id', 'lead_status', DB::raw('date(created_at)'))
                    ->get();
            }
        }

        $title = 'DuplicateLeads_'.Carbon::now()->toDateString();

        Excel::store(function ($excel) use ($title, $leads) {
            $excel->sheet($title, function ($sheet) use ($leads) {

                // Set auto size for sheet
                $sheet->setAutoSize(true);

                $headers = ['Lead Email', 'Campaign', 'Affiliate', 'Status', 'Payout/Lead', 'Count', 'Date Created'];

                //set up the title header row
                $sheet->appendRow($headers);

                //style the headers
                $sheet->cells('A1:F1', function ($cells) {

                    // Set font
                    $cells->setFont([
                        'size' => '12',
                        'bold' => true,
                    ]);

                    $cells->setAlignment('center');
                    $cells->setValignment('center');
                });

                $leadStatus = config('constants.LEAD_STATUS');

                foreach ($leads as $lead) {
                    $campaign = $lead->campaign;

                    $date1 = new \DateTime($lead->created_at);
                    $date2 = new \DateTime($lead->updated_at);
                    $interval = $date1->diff($date2);

                    $sheet->appendRow([
                        $lead->lead_email,
                        $campaign->name.'('.$campaign->id.')',
                        $lead->affiliate_id,
                        $leadStatus[$lead->lead_status],
                        $lead->lead_status == 1 ? $lead->payout : 0,
                        $lead->count,
                        $lead->date_created,
                    ]);
                }
            });
        },$title.'.xls','downloads');

        $file_path = storage_path('downloads').'/'.$title.'.xls';

        if (file_exists($file_path)) {
            return response()->download($file_path, $title.'.xls', [
                'Content-Length: '.filesize($file_path),
            ]);
        } else {
            exit('Requested file does not exist on our server!');
        }
    }

    public function cronJob(): View
    {
        // $crons = Cron::all();
        // return view('admin.cronjob', compact('crons'));
        return view('admin.cronjob');
    }

    public function cronHistory(): View
    {
        return view('admin.cronhistory');
    }

    public function updateCronJob(Request $request)
    {
        $leads = $request->input('leads');

        return $crons = Cron::whereIn('status', $leads)->orWhere('status', 1)->get()->toArray();
    }

    public function affiliateReports(): View
    {
        return view('admin.affiliatereport');
    }

    public function cake_conversions(): View
    {
        return view('admin.cakeconversions');
    }

    public function coregReports(): View
    {
        return view('admin.coregreport');
    }

    public function prepopStatistics(): View
    {
        return view('admin.prepopstatistics');
    }

    public function surveyPaths(): View
    {
        $paths = Path::all()->toArray();

        return view('admin.surveypaths', compact('paths'));
    }

    public function generateCreativeReport($inputs)
    {
        $reports = [];
        $summary = [];
        $date_today = Carbon::now()->toDateString();

        // \Log::info($inputs);

        if ((isset($inputs['lead_date_from']) && $inputs['lead_date_from'] != '' && $inputs['lead_date_from'] != $date_today)
            || (isset($inputs['lead_date_to']) && $inputs['lead_date_to'] && $inputs['lead_date_to'] != $date_today)) {

            $reports = \App\CreativeReport::getReport($inputs)->get()->toArray();
            foreach ($reports as $report) {
                if ($report['revenue'] != 0 && $report['views'] != 0) {
                    $report['revView'] = $report['revenue'] / $report['views'];
                } else {
                    $report['revView'] = 0;
                }
                $summary[] = $report;
            }

        } elseif (count($inputs) > 0) {
            $inputs['lead_date_today'] = $date_today;
            $leads = Lead::creativeRevenueReport($inputs)
                ->select(DB::RAW('campaign_id, path_id, creative_id, lead_status, received, COUNT(*) as lead_count, affiliate_id'))
                ->get();
            $views = \App\CampaignView::creativeRevenueReport($inputs)
                ->select(DB::RAW('campaign_id, path_id, creative_id, COUNT(*) as views, affiliate_id'))
                ->get();

            //AFFILIATE ID - CAMPAIGN ID - CREATIVE ID
            foreach ($leads as $lead) {
                if (! isset($reports[$lead->affiliate_id][$lead->campaign_id][$lead->creative_id])) {
                    $reports[$lead->affiliate_id][$lead->campaign_id][$lead->creative_id]['lead_count'] = 0;
                    $reports[$lead->affiliate_id][$lead->campaign_id][$lead->creative_id]['revenue'] = 0;
                    $reports[$lead->affiliate_id][$lead->campaign_id][$lead->creative_id]['views'] = 0;
                    $reports[$lead->affiliate_id][$lead->campaign_id][$lead->creative_id]['revView'] = 0;
                }
                $reports[$lead->affiliate_id][$lead->campaign_id][$lead->creative_id]['lead_count'] += $lead->lead_count;
                if ($lead->lead_status == 1) {
                    $reports[$lead->affiliate_id][$lead->campaign_id][$lead->creative_id]['revenue'] += $lead->received * $lead->lead_count;
                }
            }

            foreach ($views as $view) {
                if (! isset($reports[$view->affiliate_id][$view->campaign_id][$view->creative_id])) {
                    $reports[$view->affiliate_id][$view->campaign_id][$view->creative_id]['lead_count'] = 0;
                    $reports[$view->affiliate_id][$view->campaign_id][$view->creative_id]['revenue'] = 0;
                    $reports[$view->affiliate_id][$view->campaign_id][$view->creative_id]['views'] = 0;
                    $reports[$view->affiliate_id][$view->campaign_id][$view->creative_id]['revView'] = 0;
                }
                $reports[$view->affiliate_id][$view->campaign_id][$view->creative_id]['views'] += $view->views;

                if ($reports[$view->affiliate_id][$view->campaign_id][$view->creative_id]['revenue'] != 0 && $reports[$view->affiliate_id][$view->campaign_id][$view->creative_id]['views'] != 0) {
                    $reports[$view->affiliate_id][$view->campaign_id][$view->creative_id]['revView'] = $reports[$view->affiliate_id][$view->campaign_id][$view->creative_id]['revenue'] / $reports[$view->affiliate_id][$view->campaign_id][$view->creative_id]['views'];
                }
            }

            foreach ($reports as $affiliate_id => $campaign_details) {
                foreach ($campaign_details as $campaign_id => $creative_details) {
                    foreach ($creative_details as $creative_id => $details) {
                        $details['campaign_id'] = $campaign_id;
                        $details['creative_id'] = $creative_id;
                        $details['affiliate_id'] = $affiliate_id;
                        $summary[] = $details;
                    }
                }
            }
        }

        return $summary;
    }

    public function creativeReports(Request $request): View
    {
        $inputs = $request->all();
        // \Log::info($inputs);
        //$paths = \App\Path::pluck('name','id');
        $affiliates = AffiliateRevenueTracker::pluck('affiliate_id', 'revenue_tracker_id')->toArray();
        $date_today = Carbon::now()->toDateString();
        $campaignList = Campaign::orderBy('name')->pluck('name', 'id')->toArray();
        // $campaigns[''] = 'ALL';

        foreach ($campaignList as $key => $campaign) {
            $campaigns[$key] = $campaign.' ['.$key.']';
        }

        if (! isset($inputs['show_inactive_campaign']) && isset($inputs['campaign_id']) && $inputs['campaign_id'] == '') {
            $inputs['campaign_ids'] = Campaign::where('status', '!=', 0)->pluck('id')->toArray();
        }

        $summary = $this->generateCreativeReport($inputs);
        session()->put('searched_creatives_input', $inputs);

        return view('admin.creativeStats', compact('summary', 'inputs', 'affiliates', 'campaigns'));
    }

    /**
     * Downloading of searched duplicate leads
     */
    public function downloadCreativeRevenueReports(): BinaryFileResponse
    {
        $inputs = session()->get('searched_creatives_input');
        $summary = $this->generateCreativeReport($inputs);
        // $paths = \App\Path::pluck('name','id');
        $campaigns = Campaign::orderBy('name')->pluck('name', 'id')->toArray();
        $affiliates = \App\AffiliateRevenueTracker::pluck('affiliate_id', 'revenue_tracker_id')->toArray();

        $date = Carbon::now()->toDateString();
        if (isset($inputs['lead_date_from'], $inputs['lead_date_to']) && $inputs['lead_date_from'] != '' && $inputs['lead_date_to'] != '') {
            $date = $inputs['lead_date_from'];
            if ($inputs['lead_date_from'] != $inputs['lead_date_to']) {
                $date .= '_to_'.$inputs['lead_date_to'];
            }
        }
        $title = 'CRR_'.$date;

        Excel::store(function ($excel) use ($title, $summary, $campaigns, $affiliates) {
            $excel->sheet($title, function ($sheet) use ($summary, $campaigns, $affiliates) {

                // Set auto size for sheet
                $sheet->setAutoSize(true);
                $headers = ['Affiliate [Revenue Tracker]', 'Creative ID', 'Views', 'Lead Count', 'Revenue', 'Revenue/View'];

                //set up the title header row
                $sheet->appendRow($headers);

                //style the headers
                $sheet->cells('A1:F1', function ($cells) {

                    // Set font
                    $cells->setFont([
                        'size' => '12',
                        'bold' => true,
                    ]);

                    $cells->setAlignment('center');
                    $cells->setValignment('center');
                });

                foreach ($summary as $data) {
                    $creative = $data['creative_id'];
                    if ($data['creative_id'] == null) {
                        $creative = 'No Creative ID';
                    }

                    if (isset($affiliates[$data['affiliate_id']])) {
                        $affiliate = $affiliates[$data['affiliate_id']].' ['.$data['affiliate_id'].']';
                    } else {
                        $affiliate = $data['affiliate_id'];
                    }

                    $sheet->appendRow([
                        $affiliate,
                        $campaigns[$data['campaign_id']].' ['.$data['campaign_id'].'] - '.$creative,
                        $data['views'],
                        $data['lead_count'],
                        sprintf('%.2f', $data['revenue']),
                        sprintf('%.2f', $data['revView']),
                    ]);
                }
            });
        },$title.'.xls','downloads');

        $file_path = storage_path('downloads').'/'.$title.'.xls';

        if (file_exists($file_path)) {
            return response()->download($file_path, $title.'.xls', [
                'Content-Length: '.filesize($file_path),
            ]);
        } else {
            exit('Requested file does not exist on our server!');
        }
    }

    /**
     * User actions history page.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function userActionHistory(): View
    {
        // users
        $users = User::select('id', DB::raw('CONCAT(first_name, " ", middle_name, " ", last_name, " (",id,") ") AS full_name'))
            ->orderBy('full_name', 'asc')
            ->pluck('full_name', 'id')
            ->toArray();

        $sectionsConfig = config('constants.USER_ACTION_SECTIONS');
        $flippedSectionsConfig = array_flip($sectionsConfig);

        // Grouped array for sections
        $finalSections = [
            $sectionsConfig['contacts'] => ucwords(str_replace('_', ' ', $flippedSectionsConfig[$sectionsConfig['contacts']])),
            $sectionsConfig['affiliates'] => ucwords(str_replace('_', ' ', $flippedSectionsConfig[$sectionsConfig['affiliates']])),
            ucwords(str_replace('_', ' ', $flippedSectionsConfig[$sectionsConfig['campaigns']])) => [
                $sectionsConfig['campaigns'] => 'Campaign',
                $sectionsConfig['campaigns'].' '.$sectionsConfig['campaign_info'] => ucwords(str_replace('_', ' ', $flippedSectionsConfig[$sectionsConfig['campaign_info']])),
                $sectionsConfig['campaigns'].' '.$sectionsConfig['campaign_filters'] => ucwords(str_replace('_', ' ', $flippedSectionsConfig[$sectionsConfig['campaign_filters']])),
                $sectionsConfig['campaigns'].' '.$sectionsConfig['campaign_affiliates'] => ucwords(str_replace('_', ' ', $flippedSectionsConfig[$sectionsConfig['campaign_affiliates']])),
                $sectionsConfig['campaigns'].' '.$sectionsConfig['campaign_payouts'] => ucwords(str_replace('_', ' ', $flippedSectionsConfig[$sectionsConfig['campaign_payouts']])),
                $sectionsConfig['campaigns'].' '.$sectionsConfig['campaign_config'] => ucwords(str_replace('_', ' ', $flippedSectionsConfig[$sectionsConfig['campaign_config']])),
                $sectionsConfig['campaigns'].' '.$sectionsConfig['campaign_long_content'] => ucwords(str_replace('_', ' ', $flippedSectionsConfig[$sectionsConfig['campaign_long_content']])),
                $sectionsConfig['campaigns'].' '.$sectionsConfig['campaign_stack'] => ucwords(str_replace('_', ' ', $flippedSectionsConfig[$sectionsConfig['campaign_stack']])),
                $sectionsConfig['campaigns'].' '.$sectionsConfig['campaign_creatives'] => ucwords(str_replace('_', ' ', $flippedSectionsConfig[$sectionsConfig['campaign_creatives']])),
                $sectionsConfig['campaigns'].' '.$sectionsConfig['campaign_high_paying_content'] => ucwords(str_replace('_', ' ', $flippedSectionsConfig[$sectionsConfig['campaign_high_paying_content']])),
                $sectionsConfig['campaigns'].' '.$sectionsConfig['campaign_posting_instructions'] => ucwords(str_replace('_', ' ', $flippedSectionsConfig[$sectionsConfig['campaign_posting_instructions']])),
            ],
            $sectionsConfig['advertisers'] => ucwords(str_replace('_', ' ', $flippedSectionsConfig[$sectionsConfig['advertisers']])),
            $sectionsConfig['filter_types'] => ucwords(str_replace('_', ' ', $flippedSectionsConfig[$sectionsConfig['filter_types']])),
            ucwords(str_replace('_', ' ', $flippedSectionsConfig[$sectionsConfig['affiliate_reports']])) => [
                $sectionsConfig['affiliate_reports'] => 'Affiliate Report',
                $sectionsConfig['affiliate_reports'].' '.$sectionsConfig['affiliate_reports_internal'] => ucwords(str_replace('_', ' ', $flippedSectionsConfig[$sectionsConfig['affiliate_reports_internal']])),
                $sectionsConfig['affiliate_reports'].' '.$sectionsConfig['affiliate_reports_h_and_p'] => ucwords(str_replace('_', ' ', $flippedSectionsConfig[$sectionsConfig['affiliate_reports_h_and_p']])),
                $sectionsConfig['affiliate_reports'].' '.$sectionsConfig['affiliate_reports_internal_iframe'] => ucwords(str_replace('_', ' ', $flippedSectionsConfig[$sectionsConfig['affiliate_reports_internal_iframe']])),
            ],
            ucwords(str_replace('_', ' ', $flippedSectionsConfig[$sectionsConfig['revenue_trackers']])) => [
                $sectionsConfig['revenue_trackers'] => 'Revenue Tracker',
                $sectionsConfig['revenue_trackers'].' '.$sectionsConfig['revenue_tracker_set_exit_page'] => ucwords(str_replace('_', ' ', $flippedSectionsConfig[$sectionsConfig['revenue_tracker_set_exit_page']])),
                $sectionsConfig['revenue_trackers'].' '.$sectionsConfig['revenue_tracker_campaign_order'] => ucwords(str_replace('_', ' ', $flippedSectionsConfig[$sectionsConfig['revenue_tracker_campaign_order']])),
                $sectionsConfig['revenue_trackers'].' '.$sectionsConfig['revenue_tracker_mixed_coreg_campaign_order'] => ucwords(str_replace('_', ' ', $flippedSectionsConfig[$sectionsConfig['revenue_tracker_mixed_coreg_campaign_order']])),
            ],
            $sectionsConfig['categories'] => ucwords(str_replace('_', ' ', $flippedSectionsConfig[$sectionsConfig['categories']])),
            $sectionsConfig['settings'] => ucwords(str_replace('_', ' ', $flippedSectionsConfig[$sectionsConfig['settings']])),
            $sectionsConfig['sign-in'] => ucwords(str_replace('_', ' ', $flippedSectionsConfig[$sectionsConfig['sign-in']])),
        ];

        $severities = array_flip(config('constants.USER_ACTION_SEVERITY'));
        $finalSeverities = [];
        foreach ($severities as $key => $value) {
            $finalSeverities[$key] = ucwords($value);
        }

        return view('admin.useractionlogs', compact('users', 'finalSections', 'finalSeverities'));
    }

    public function userActionHistoryReport(Request $request): JsonResponse
    {
        $inputs = $request->all();

        //        $userID = $inputs['user_id'];
        //        $sectionID = $inputs['section_id'];
        //        $subSectionID = $inputs['sub_section_id'];
        //        $severity = $inputs['change_severity'];
        //        $dateFrom = $inputs['date_from'];
        //        $dateTo = $inputs['date_to'];

        $columns = [
            // datatable column index  => database column name
            0 => 'id',
            1 => 'full_name',
            2 => '',
            3 => '',
            4 => 'reference_id',
            5 => 'summary',
            6 => 'change_severity',
            7 => 'created_at',
            8 => '',
        ];

        //$paramSearch = $inputs['search']['value'];
        $start = $inputs['start'];
        $length = $inputs['length'];

        $userActionLogsBaseQueryCount = UserActionLog::searchHistoryLogsWithUser($inputs);
        $orderCol = $columns[$inputs['order'][0]['column']];
        $orderDir = $inputs['order'][0]['dir'];

        // Use separate ordering for this
        if (! empty($orderCol)) {
            $inputs['order_column'] = $orderCol;
            $inputs['order_direction'] = $orderDir;
        }

        $userActionLogsBaseQuery = UserActionLog::searchHistoryLogsWithUser($inputs);
        $allRecords = $userActionLogsBaseQueryCount->count();

        $totalRecords = $allRecords;
        $totalFiltered = $totalRecords;

        $userActionLogsWithLimit = $userActionLogsBaseQuery;

        if ($length > 1) {
            $userActionLogsWithLimit->skip($start)->take($length);
        }

        $userActionLogData = $userActionLogsWithLimit->get();
        $historyData = [];
        $sectionsConfig = config('constants.USER_ACTION_SECTIONS');
        $flippedSectionsConfig = array_flip($sectionsConfig);
        $severitiesFlipped = array_flip(config('constants.USER_ACTION_SEVERITY'));

        foreach ($userActionLogData as $actionLog) {
            // represent null ids as blank
            $section = '';
            if (! is_null($actionLog->section_id)) {
                $section = ucwords(str_replace('_', ' ', $flippedSectionsConfig[$actionLog->section_id]));
            }

            $subSection = '';
            if (! is_null($actionLog->sub_section_id)) {
                $subSection = ucwords(str_replace('_', ' ', $flippedSectionsConfig[$actionLog->sub_section_id]));
            }

            $refID = '';
            if (! is_null($actionLog->reference_id)) {
                $refID = $actionLog->reference_id;
            }

            $severity = '';
            if (! is_null($actionLog->change_severity)) {
                $severity = ucwords(str_replace('_', ' ', $severitiesFlipped[$actionLog->change_severity]));
            }

            $oldValue = '';
            $newValue = '';

            if (! is_null($actionLog->old_value)) {
                $oldValue = htmlentities($actionLog->old_value);
            }

            if (! is_null($actionLog->new_value)) {
                $newValue = htmlentities($actionLog->new_value);
            }

            $details = '<button class="btn-actions btn btn-info show-details" title="Details" data-id="'.$actionLog->id.'" data-section="'.$section.'" data-subsection="'.$subSection.'" data-refid="'.$refID.'" data-fullname="'.$actionLog->full_name.'" data-severity="'.$severity.'" data-summary="'.htmlentities($actionLog->summary).'" data-oldvalue="'.$oldValue.'" data-newvalue="'.$newValue.'" data-date="'.htmlentities($actionLog->created_at->toDateTimeString()).'"><span class="glyphicon glyphicon-info-sign"></span></button>';

            $data = [
                'id' => $actionLog->id,
                'user' => $actionLog->full_name,
                'section' => $section,
                'sub_section' => $subSection,
                'reference_id' => $refID,
                'summary' => $actionLog->summary,
                'severity' => $severity,
                'datetime' => $actionLog->created_at->toDateTimeString(),
                'details' => $details,
            ];

            array_push($historyData, $data);
        }

        $responseData = [
            'draw' => intval($inputs['draw']),   // for every request/draw by client side , they send a number as a parameter, when they receive a response/data they first check the draw number, so we are sending same number in draw.
            'recordsTotal' => $totalRecords,  // total number of records
            'recordsFiltered' => $totalFiltered, // total number of records after searching, if there is no searching then totalFiltered = totalData
            'data' => $historyData,   // total data array
        ];

        return response()->json($responseData, 200);
    }

    /**
     * Will return active campaigns server side
     */
    public function activeCampaignsServerSide(Request $request): JsonResponse
    {
        $inputs = $request->all();

        $start = $inputs['start'];
        $length = $inputs['length'];

        $totalRecords = Campaign::activeCampaigns()->count();
        $campaigns = Campaign::activeCampaigns()->skip($start)->take($length)->get();
        $campaignsData = [];

        foreach ($campaigns as $campaign) {
            $data = [
                'id' => $campaign->id,
                'name' => $campaign->name,
                'cap_type' => $campaign->cap_type,
                'cap' => $campaign->cap,
            ];

            array_push($campaignsData, $data);
        }

        $responseData = [
            'draw' => intval($inputs['draw']),   // for every request/draw by client side , they send a number as a parameter, when they receive a response/data they first check the draw number, so we are sending same number in draw.
            'recordsTotal' => $totalRecords,  // total number of records
            'recordsFiltered' => $totalRecords, // total number of records after searching, if there is no searching then totalFiltered = totalData
            'data' => $campaignsData,   // total data array
        ];

        return response()->json($responseData, 200);
    }

    public function campaignRevenueViewChangeServerSide(Request $request): JsonResponse
    {
        $inputs = $request->all();
        // $predefinedDateRange = $inputs['predefined_date_range'];
        // $term = $inputs['term'];
        // $affiliateIDs = $inputs['affiliate_ids_most_changes'];
        // Log::info($inputs['predefined_date_range']);
        // Log::info($inputs['term']);

        // get all campaigns involved with affiliate IDs
        // $campaignIDs = AffiliateReport::getCampaignsByAffiliateIDs($affiliateIDs, $inputs)->pluck('campaign_id')->toArray();

        // $whereInCampaignID = '';
        // $ids = '';

        // foreach ($campaignIDs as $id)
        // {
        //     $ids = "$ids$id,";
        // }

        // $ids = rtrim($ids,",");

        // if(!empty($ids))
        // {
        // This means there is specified list of IDs
        // $whereInCampaignID = "WHERE campaign_id IN ($ids)";
        // }

        $date = [];

        // $start = $en->startOfWeek(Carbon::TUESDAY);

        // get all the date ranges
        // get the dates
        switch ($inputs['predefined_date_range']) {
            case 'yesterday' :
                $date['previous_from'] = Carbon::yesterday()->startOfDay()->toDateString();
                $date['previous_to'] = Carbon::yesterday()->endOfDay()->toDateString();
                $date['before_from'] = Carbon::yesterday()->subDay()->startOfDay()->toDateString();
                $date['before_to'] = Carbon::yesterday()->subDay()->endOfDay()->toDateString();
                break;

            case 'last_week' :
                // Set the first day of the week to sunday
                Carbon::setWeekStartsAt(Carbon::SUNDAY);
                Carbon::setWeekEndsAt(Carbon::SATURDAY); // and it does not need neither to precede the start

                $date['previous_from'] = Carbon::now()->subWeek()->startOfWeek()->toDateString();
                $date['previous_to'] = Carbon::now()->subWeek()->endOfWeek()->toDateString();
                $date['before_from'] = Carbon::now()->subWeek()->subWeek()->startOfWeek()->toDateString();
                $date['before_to'] = Carbon::now()->subWeek()->subWeek()->endOfWeek()->toDateString();

                Carbon::setWeekStartsAt(Carbon::MONDAY);
                Carbon::setWeekEndsAt(Carbon::SUNDAY); // and it does not need neither to precede the start
                break;

            case 'last_month':
                $date['previous_from'] = Carbon::now()->subMonth()->startOfMonth()->toDateString();
                $date['previous_to'] = Carbon::now()->subMonth()->endOfMonth()->toDateString();
                $date['before_from'] = Carbon::now()->subMonth()->subMonth()->startOfMonth()->toDateString();
                $date['before_to'] = Carbon::now()->subMonth()->subMonth()->endOfMonth()->toDateString();
                break;

            default:
                // default is yesterday
                // $date['from'] = Carbon::yesterday()->startOfDay();
                // $date['to'] =  Carbon::yesterday()->endOfDay();
                $date['previous_from'] = Carbon::yesterday()->startOfDay()->toDateString();
                $date['previous_to'] = Carbon::yesterday()->endOfDay()->toDateString();
                $date['before_from'] = Carbon::yesterday()->subDay()->startOfDay()->toDateString();
                $date['before_to'] = Carbon::yesterday()->subDay()->endOfDay()->toDateString();
                break;
        }

        $affiliateIDs = '';

        // Log::info(count($inputs['affiliate_ids_most_changes']));
        // Log::info($inputs['affiliate_ids_most_changes']);

        if (count($inputs['affiliate_ids_most_changes']) > 0 && is_array($inputs['affiliate_ids_most_changes'])) {
            foreach ($inputs['affiliate_ids_most_changes'] as $affiliateID) {
                $affiliateIDs = "$affiliateIDs$affiliateID,";
            }

            $affiliateIDs = rtrim($affiliateIDs, ',');
        }

        $whereInAffiliateID = '';

        if (! empty($affiliateIDs)) {
            // This means there is specified list of IDs
            $whereInAffiliateID = "AND revenue_tracker_id IN ($affiliateIDs)";
        }

        $selectPart = 'SELECT dataset.campaign_id,
                             (SELECT campaigns.name FROM campaigns WHERE campaigns.id = dataset.campaign_id) AS campaign_name, 
                             (SUM(dataset.previousRev) / SUM(dataset.previousViews)) AS previousRatio, 
                             (SUM(dataset.beforeRev) / SUM(dataset.beforeViews)) AS beforeRatio,
                             ABS(( SUM(dataset.previousRev) / SUM(dataset.previousViews) ) - (  SUM(dataset.beforeRev) / SUM(dataset.beforeViews)  )) AS abs_change_factor,
                             (( SUM(dataset.previousRev) / SUM(dataset.previousViews) ) - (  SUM(dataset.beforeRev) / SUM(dataset.beforeViews)  )) AS change_factor,
                             (( SUM(dataset.previousRev) / SUM(dataset.previousViews) ) / (  SUM(dataset.beforeRev) / SUM(dataset.beforeViews)  )) AS net FROM';

        $datasetPart = "(SELECT  
                            campaign_id, 
                            (SELECT SUM(revenue) FROM campaign_revenue_view_statistics sub WHERE (DATE(sub.created_at) >= DATE('".$date['previous_from']."') and DATE(sub.created_at) <= DATE('".$date['previous_to']."')) AND sub.campaign_id = super.campaign_id AND sub.revenue_tracker_id = super.revenue_tracker_id) AS previousRev, 
                            (SELECT SUM(views) FROM campaign_revenue_view_statistics sub WHERE (DATE(sub.created_at) >= DATE('".$date['previous_from']."') and DATE(sub.created_at) <= DATE('".$date['previous_to']."')) AND sub.campaign_id = super.campaign_id AND sub.revenue_tracker_id = super.revenue_tracker_id) AS previousViews,
                            (SELECT SUM(revenue) FROM campaign_revenue_view_statistics sub WHERE (DATE(sub.created_at) >= DATE('".$date['before_from']."') and DATE(sub.created_at) <= DATE('".$date['before_to']."')) AND sub.campaign_id = super.campaign_id AND sub.revenue_tracker_id = super.revenue_tracker_id) AS beforeRev,
                            (SELECT SUM(views) FROM campaign_revenue_view_statistics sub WHERE (DATE(sub.created_at) >= DATE('".$date['before_from']."') and DATE(sub.created_at) <= DATE('".$date['before_to']."')) AND sub.campaign_id = super.campaign_id AND sub.revenue_tracker_id = super.revenue_tracker_id) AS beforeViews
                            FROM campaign_revenue_view_statistics super WHERE (DATE(super.created_at) >= DATE('".$date['before_from']."') AND DATE(super.created_at) <= DATE('".$date['previous_to']."')) $whereInAffiliateID GROUP BY super.campaign_id, super.revenue_tracker_id
                        ) dataset GROUP BY dataset.campaign_id ORDER BY abs_change_factor DESC ";

        $allCount = count(DB::select($selectPart.$datasetPart));

        $totalFiltered = $allCount;

        // Skip and Take
        $skipAndTake = 'LIMIT '.$inputs['start'].', '.$inputs['length'];

        // Log::info($selectPart.$datasetPart.$skipAndTake);
        $campaignChanges = DB::select($selectPart.$datasetPart.$skipAndTake);
        // Log::info($campaignChanges);

        $changesData = [];

        foreach ($campaignChanges as $change) {
            $changeFactor = round($change->change_factor, 2);
            $changeFactorIndicator = $changeFactor < 0.0 ? '<i class="fa fa-sort-down change-indicator-red"></i>' : '<i class="fa fa-sort-up change-indicator-green"></i>';

            $data = [
                'campaign' => "$change->campaign_name ($change->campaign_id)",
                'revenue_view' => round($change->previousRatio, 2),
                'change' => "$changeFactor $changeFactorIndicator",
                'net_percentage' => round($change->net, 2),
            ];

            array_push($changesData, $data);
        }

        $responseData = [
            'draw' => intval($inputs['draw']),   // for every request/draw by client side , they send a number as a parameter, when they receive a response/data they first check the draw number, so we are sending same number in draw.
            'recordsTotal' => $allCount,  // total number of records
            'recordsFiltered' => $totalFiltered, // total number of records after searching, if there is no searching then totalFiltered = totalData
            'data' => $changesData,   // total data array
        ];

        return response()->json($responseData, 200);
    }

    public function getMostChangeCampaigns(Request $request)
    {
        $inputs = $request->all();
        //Log::info($inputs);
        switch ($inputs['predefined_date_range']) {
            case 'last_week' :
                // Set the first day of the week to sunday
                Carbon::setWeekStartsAt(Carbon::SUNDAY);
                Carbon::setWeekEndsAt(Carbon::SATURDAY); // and it does not need neither to precede the start
                $from = Carbon::now()->subWeek(2)->startOfWeek()->toDateString();
                $fromDiff = Carbon::now()->subWeek(2)->weekOfYear;
                $to = Carbon::now()->subWeek()->endOfWeek()->toDateString();
                $toDiff = Carbon::now()->subWeek()->weekOfYear;
                Carbon::setWeekStartsAt(Carbon::MONDAY);
                Carbon::setWeekEndsAt(Carbon::SUNDAY); // and it does not need neither to precede the start
                break;

            case 'last_month':
                $from = Carbon::now()->subMonth(2)->startOfMonth()->toDateString();
                $fromDiff = Carbon::now()->subMonth(2)->month;
                $to = Carbon::now()->subMonth()->endOfMonth()->toDateString();
                $toDiff = Carbon::now()->subMonth()->month;
                break;

            default: //yesterday
                $fromDiff = $from = Carbon::yesterday()->subDay()->toDateString();
                $toDiff = $to = Carbon::yesterday()->toDateString();
                break;
        }

        //DB::enableQueryLog();
        $statistics = CampaignRevenueViewStatistic::campaignMostChanges($inputs['predefined_date_range'], $from, $to, $inputs['affiliates'])->get();
        //Log::info(DB::getQueryLog());

        $cids = $statistics->map(function ($st) {
            return $st->campaign_id;
        });

        $campaigns = Campaign::whereIn('id', $cids)->pluck('name', 'id');
        // Log::info($statistics);
        $reports = [];
        foreach ($statistics as $s) {
            $campaign = $s['campaign_id'];
            if (! isset($reports[$campaign])) {
                $reports[$campaign] = [
                    'name' => $campaigns[$campaign],
                ];
            }

            if ($s['identifier'] == $fromDiff) {
                $reports[$campaign]['from'] = $s;
            } else {
                $reports[$campaign]['to'] = $s;
            }

            if (isset($reports[$campaign]['from']) && isset($reports[$campaign]['to'])) {
                $change_factor = $reports[$campaign]['to']['ratio'] - $reports[$campaign]['from']['ratio'];
                $reports[$campaign]['change_factor'] = $change_factor;
                $reports[$campaign]['abs_change_factor'] = abs($change_factor);
                $reports[$campaign]['indicator'] = round($change_factor, 2) < 0.0 ? '<i class="fa fa-sort-down change-indicator-red"></i>' : '<i class="fa fa-sort-up change-indicator-green"></i>';
                $reports[$campaign]['net'] = $reports[$campaign]['to']['ratio'] != 0 && $reports[$campaign]['from']['ratio'] != 0 ? $reports[$campaign]['to']['ratio'] / $reports[$campaign]['from']['ratio'] : 0;
            }
        }

        foreach ($reports as $id => $report) {
            if (! isset($report['from']) || ! isset($report['to'])) {
                unset($reports[$id]);
            } elseif ($report['change_factor'] == 0) {
                unset($reports[$id]);
            }
        }

        return response()->json($reports, 200);
    }
}
