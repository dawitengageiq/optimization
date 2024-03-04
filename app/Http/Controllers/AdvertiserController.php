<?php

namespace App\Http\Controllers;

use App\Advertiser;
use App\Affiliate;
use App\Campaign;
use App\Commands\GetUserActionPermission;
use App\FilterType;
use App\Http\Requests\AdvertiserRequest;
use Bus;
use Carbon;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Log;
use Session;

class AdvertiserController extends Controller
{
    protected $canEdit = false;

    protected $canDelete = false;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('advertiser');
        /*
        $this->middleware('restrict_access',['only' => [
            'dashboard',
            'campaigns',
            'searchLeads',
            'revenueStatistics'
        ]]);
        */

        //get the user
        $user = auth()->user();

        $this->canEdit = Bus::dispatch(new GetUserActionPermission($user, 'use_edit_advertiser'));
        $this->canDelete = Bus::dispatch(new GetUserActionPermission($user, 'use_delete_advertiser'));
    }

    /**
     * Display a listing of the resource. This is a server side processing
     */
    public function index(Request $request): JsonResponse
    {
        $inputs = $request->all();
        $totalRecords = $totalFiltered = Advertiser::count();
        $advertisersData = [];

        $param = $inputs['search']['value'];
        $start = $inputs['start'];
        $length = $inputs['length'];
        $orderCol = $inputs['order'][0]['column'];
        $orderDir = $inputs['order'][0]['dir'];

        $columns = [
            // datatable column index  => database column name
            0 => 'id',
            1 => 'company',
            2 => 'website_url',
            3 => 'address',
            4 => 'status',
        ];

        $totalFiltered = Advertiser::searchAdvertisers($param, 0, null)->count();
        $advertisers = Advertiser::searchAdvertisers($param, $start, $length)->orderBy($columns[$orderCol], $orderDir)->get();

        foreach ($advertisers as $advertiser) {
            $data = [];

            //create advertise id html
            array_push($data, $advertiser->id);

            //create the company html
            $companyHTML = '<span id="adv-'.$advertiser->id.'-comp">'.$advertiser->company.'</span>';
            array_push($data, $companyHTML);

            //create the website url html
            $websiteURLHTML = '<a href="'.$advertiser->website_url.'" target="_blank"><span id="adv-'.$advertiser->id.'-web">'.$advertiser->website_url.'</span></a>';
            array_push($data, $websiteURLHTML);

            //create contact details html
            $addressHTML = '<span class="glyphicon glyphicon-envelope"></span> <span id="adv-'.$advertiser->id.'-add">'.$advertiser->address.'</span><br>';
            $cityHTML = '<span id="adv-'.$advertiser->id.'-cty">'.$advertiser->city.'</span>,';
            $stateHTML = ' <span id="adv-'.$advertiser->id.'-ste">'.$advertiser->state.'</span>';
            $zipHTML = ' <span id="adv-'.$advertiser->id.'-zip">'.$advertiser->zip.'</span><br><span class="glyphicon glyphicon-phone-alt"></span>';
            $phoneHTML = '<span id="adv-'.$advertiser->id.'-phn">'.$advertiser->phone.'</span>';
            array_push($data, $addressHTML.$cityHTML.$stateHTML.$zipHTML.$phoneHTML);

            //create the status html
            $adv_stat = $advertiser->status == 1 ? 'Active' : 'Inactive';
            $statusHTML = '<span id="adv-'.$advertiser->id.'-stat" data-status="'.$advertiser->status.'">'.$adv_stat.'</span>';
            array_push($data, $statusHTML);

            //construct the action buttons
            $hidden = '<input type="hidden" id="adv-'.$advertiser->id.'-desc" value="'.$advertiser->description.'"/>';

            //determine if current user do have edit and delete permissions for advertisers
            $editButton = '';
            $deleteButton = '';

            if ($this->canEdit) {
                $editButton = '<button class="editAdvertiser btn-actions btn btn-primary" title="Edit" data-id="'.$advertiser->id.'"><span class="glyphicon glyphicon-pencil"></span></button>';
            }

            if ($this->canDelete) {
                $deleteButton = '<button class="deleteAdvertiser btn-actions btn btn-danger" title="Delete" data-id="'.$advertiser->id.'"><span class="glyphicon glyphicon-trash"></span></button>';
            }

            array_push($data, $hidden.$editButton.$deleteButton);

            array_push($advertisersData, $data);
        }

        $responseData = [
            'draw' => intval($inputs['draw']),   // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            'recordsTotal' => $totalRecords,  // total number of records
            'recordsFiltered' => $totalFiltered, // total number of records after searching, if there is no searching then totalFiltered = totalData
            'data' => $advertisersData,   // total data array
        ];

        return response()->json($responseData, 200);
    }

    /**
     * Advertiser dashboard
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function dashboard(): View
    {
        return view('advertiser.dashboard');
    }

    public function statistics(): View
    {
        return view('advertiser.statistics');
    }

    public function account(): View
    {
        return view('advertiser.account');
    }

    public function edit_account(): View
    {
        return view('advertiser.edit_account');
    }

    public function change_password(): View
    {
        return view('advertiser.change_password');
    }

    /* ADVERTISER PORTAL END */

    public function getCitiesByState(Request $request)
    {
        $name = $request->input('name');

        $states_cities = config('constants.US_STATES_CITIES');
        $cities = $states_cities[$name];

        return $cities;
    }

    public function getAvailableUsers(Request $request)
    {
        $id = $request->input('id');

        return \Bus::dispatch(
            new \GetAvailableUsers('advertisers', $id)
        );
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        //
    }

    /**
     * Store a newly created advertiser resource in storage.
     */
    public function store(
        AdvertiserRequest $request,
        \App\Http\Services\UserActionLogger $userAction
    ): JsonResponse {
        $inputs = $request->all();
        $advertiser = Advertiser::create($inputs);

        $responseData = [
            'id' => $advertiser->id,
            'canEdit' => $this->canEdit,
            'canDelete' => $this->canDelete,
        ];

        //Action Logger
        $userAction->logger(4, null, $advertiser->id, null, $advertiser->toArray());

        return response()->json($responseData, 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): Response
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id): Response
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(
        AdvertiserRequest $request,
        \App\Http\Services\UserActionLogger $userAction,
        $id
    ): JsonResponse {
        //

        $inputs = $request->all();
        //var_dump($inputs);

        $advertiser = Advertiser::find($id);

        $current_state = $advertiser->toArray(); //For Logging

        $advertiser->company = $inputs['company'];
        $advertiser->website_url = $inputs['website_url'];
        //$advertiser->user_id     = $inputs['user_id'];
        $advertiser->phone = $inputs['phone'];
        $advertiser->address = $inputs['address'];
        $advertiser->city = $inputs['city'];
        $advertiser->state = $inputs['state'];
        $advertiser->zip = $inputs['zip'];
        $advertiser->description = $inputs['description'];
        $advertiser->status = $inputs['status'];
        $advertiser->save();     //save updates

        //Action Logger
        $userAction->logger(4, null, $advertiser->id, $current_state, $advertiser->toArray());

        //return  'Advertiser with ID '.$id.' has been updated';
        //response to browser jason data
        return response()->json(['id' => $id], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return string
     */
    public function destroy(
        Request $request,
        \App\Http\Services\UserActionLogger $userAction
    ) {
        $advertiser = Advertiser::find($request->input('id'));

        //Action Logger
        $userAction->logger(4, null, $request->input('id'), $advertiser->toArray(), null);

        $advertiser->delete();

        return 'Advertiser with ID '.$request->input('id').' has been deleted';
    }

    /**
     * Advertiser page route for contacts
     */
    public function contacts(): View
    {
        return view('advertiser.contacts');
    }

    /**
     * Advertiser page route for campaigns
     */
    public function campaigns(): View
    {
        $advertisers = array_merge(['0' => ''], Advertiser::pluck('company', 'id')->toArray());

        $affiliates = Affiliate::select('id', DB::raw('CONCAT(id, " - ",company) AS company_name'))
            ->orderBy('id', 'asc')
            ->pluck('company_name', 'id')->toArray();

        $lead_types = config('constants.LEAD_CAP_TYPES');
        $campaign_types = config('constants.CAMPAIGN_TYPES');

        $filter_types = FilterType::select('id', DB::raw('CONCAT(type, " - ",name) AS filter_name'))
            ->orderBy('filter_name', 'asc')
            ->pluck('filter_name', 'id')->toArray();

        return view('advertiser.campaigns', compact('advertisers', 'lead_types', 'totalCampaignCount', 'affiliates', 'filter_types', 'campaign_types'));
    }

    /**
     * Search leads page
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function searchLeads(Request $request): View
    {
        //this is to determine if fresh visit in order to sort records by created at
        Session::forget('no_visit');

        $inputs = $request->all();
        $leads = [];

        if (count($inputs) > 0) {
            $leads = Lead::searchLeads($inputs)->get();
        }

        session()->put('searched_leads', $leads);

        if (count($leads) > 0) {
            session()->flash('searched_searched_leads', $leads);
        } else {
            //remove the leads array object in the session
            session()->pull('searched_searched_leads');
        }

        return view('advertiser.searchleads', compact('leads', 'inputs'));
    }

    /**
     * for revenueStats
     */
    public function revenueStatistics(Request $request): View
    {
        //this is to determine if fresh visit in order to sort records by created at
        Session::forget('no_visit');

        $inputs = $request->all();
        $leads = [];

        if (count($inputs) > 0) {
            $leads = Lead::getRevenueStats($inputs)->get();
        }

        session()->put('revenue_leads', $leads);

        if (count($leads) > 0) {
            session()->flash('searched_revenue_leads', $leads);
        } else {
            //remove the leads array object in the session
            session()->pull('searched_revenue_leads');
        }

        return view('advertiser.revenueStats', compact('leads', 'inputs'));
    }

    /**
     * Top 10 campaigns by revenue yesterday
     */
    public function topTenCampaignsByRevenueYesterday(Request $request): JsonResponse
    {
        $yesterday = Carbon::now()->subDay()->toDateString();
        $user = $request->user();

        $selectSQL = "SELECT leads.campaign_id,CONCAT(campaigns.name,'(',leads.campaign_id,')') AS campaign, (SUM(leads.received) - SUM(leads.payout)) AS profit, SUM(leads.received) AS revenue, SUM(leads.payout) AS cost, leads.created_at FROM leads INNER JOIN campaigns ON leads.campaign_id=campaigns.id WHERE leads.lead_status=1 AND DATE(leads.created_at) = DATE('".$yesterday."') AND leads.campaign_id=(SELECT campaigns.id FROM campaigns WHERE campaigns.advertiser_id=".$user->advertiser->id.') GROUP BY leads.campaign_id ORDER BY profit DESC, revenue DESC LIMIT 10';
        $campaigns = DB::select($selectSQL);

        // Log::info('(Advertiser)Top 10 Campaign Revenue per day: '.$selectSQL);

        return response()->json($campaigns);
    }

    /**
     * Top 10 campaigns by revenue from the current week
     */
    public function topTenCampaignsByRevenueForCurrentWeek(Request $request): JsonResponse
    {
        $currentDate = Carbon::now()->toDateString();
        $user = $request->user();

        $selectSQL = "SELECT leads.campaign_id,CONCAT(campaigns.name,'(',leads.campaign_id,')') AS campaign, (SUM(leads.received) - SUM(leads.payout)) AS profit, SUM(leads.received) AS revenue, SUM(leads.payout) AS cost, leads.created_at FROM leads INNER JOIN campaigns ON leads.campaign_id=campaigns.id WHERE leads.lead_status=1 AND YEARWEEK(DATE(leads.created_at)) = YEARWEEK(DATE('".$currentDate."')) AND leads.campaign_id=(SELECT campaigns.id FROM campaigns WHERE campaigns.advertiser_id=".$user->advertiser->id.') GROUP BY leads.campaign_id ORDER BY profit DESC, revenue DESC LIMIT 10';
        $campaigns = DB::select($selectSQL);

        // Log::info('(Advertiser)Top 10 Campaign Revenue per week: '.$selectSQL);

        return response()->json($campaigns);
    }

    /**
     * Top 10 campaigns by revenue from the current month
     */
    public function topTenCampaignsByRevenueForCurrentMonth(Request $request): JsonResponse
    {
        $currentDate = Carbon::now()->toDateString();
        $user = $request->user();

        $selectSQL = "SELECT leads.campaign_id,CONCAT(campaigns.name,'(',leads.campaign_id,')') AS campaign, (SUM(leads.received) - SUM(leads.payout)) AS profit, SUM(leads.received) AS revenue, SUM(leads.payout) AS cost, leads.created_at FROM leads INNER JOIN campaigns ON leads.campaign_id=campaigns.id WHERE leads.lead_status=1 AND MONTH(DATE(leads.created_at)) = MONTH(DATE('".$currentDate."')) AND leads.campaign_id=(SELECT campaigns.id FROM campaigns WHERE campaigns.advertiser_id=".$user->advertiser->id.') GROUP BY leads.campaign_id ORDER BY profit DESC, revenue DESC LIMIT 10';
        $campaigns = DB::select($selectSQL);

        return response()->json($campaigns);
    }

    /**
     * Different lead counts
     */
    public function leadCounts(Request $request): JsonResponse
    {
        $user = $request->user();

        $selectSQL = "SELECT (CASE WHEN lead_status=0 THEN '#failed-leads' WHEN lead_status=1 THEN '#success-leads' WHEN lead_status=2 THEN '#rejected-leads' WHEN lead_status=3 THEN '#pending-leads' ELSE '#nah' END) AS lead_type, COUNT(id) AS lead_count FROM leads WHERE campaign_id=(SELECT campaigns.id FROM campaigns WHERE campaigns.advertiser_id=".$user->advertiser->id.') GROUP BY lead_status ORDER BY lead_status';
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
     * Will return active campaigns
     */
    public function activeCampaigns(Request $request): JsonResponse
    {
        $user = $request->user();
        $param['advertiser_id'] = $user->advertiser->id;

        $campaigns = Campaign::activeCampaigns($param)->get();

        return response()->json($campaigns);
    }

    /**
     * This will determine if certain advertiser is active.
     */
    public function status($id): JsonResponse
    {
        $advertiser = Advertiser::find($id);

        $response = [];

        if ($advertiser->exists()) {
            $response['advertiser_id'] = $advertiser->id;
            $response['name'] = $advertiser->company." ($advertiser->id)";
            $response['active'] = $advertiser->status == 1;
        }

        return response()->json($response, 200);
    }
}
