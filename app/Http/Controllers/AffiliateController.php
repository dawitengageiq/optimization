<?php

namespace App\Http\Controllers;

use App\Advertiser;
use App\Affiliate;
use App\AffiliateRevenueTracker;
use App\AffiliateWebsite;
use App\AffiliateWebsiteReport;
use App\Campaign;
use App\Category;
use App\Commands\GetUserActionPermission;
use App\ExternalPathAffiliateReport;
use App\FilterType;
use App\HandPAffiliateReport;
use App\Http\Requests;
use App\Http\Requests\AffiliateRequest;
use App\Http\Requests\ChangeAffiliatePasswordRequest;
use App\Http\Requests\ChangeContactPasswordRequest;
use App\Http\Requests\UpdateContactInfoRequest;
use App\Lead;
use App\LeadSentResult;
use App\Setting;
use App\User;
use App\UserMeta;
use Bus;
use Carbon\Carbon;
use DB;
use Excel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Log;
use Session;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AffiliateController extends Controller
{
    protected $canEdit = false;

    protected $canDelete = false;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('affiliate');
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

        $this->canEdit = Bus::dispatch(new GetUserActionPermission($user, 'use_edit_affiliate'));
        $this->canDelete = Bus::dispatch(new GetUserActionPermission($user, 'use_delete_affiliate'));
    }

    /**
     * Will return searched data for affiliates that is compatible with data tables server side processing
     */
    public function index(Request $request): JsonResponse
    {
        $inputs = $request->all();
        $totalRecords = $totalFiltered = Affiliate::count();
        $affiliatesData = [];
        $affiliate_types = config('constants.AFFILIATE_TYPE');

        $param = $inputs['search']['value'];
        $start = $inputs['start'];
        $length = $inputs['length'];
        $orderCol = $inputs['order'][0]['column'];
        $orderDir = $inputs['order'][0]['dir'];

        $columns = [
            // datatable column index  => database column name
            0 => 'id',
            1 => 'company',
            2 => 'type',
            3 => 'website_url',
            4 => 'address',
            5 => 'status',
        ];

        $totalFiltered = Affiliate::searchAffiliates($param, 0, null)->count();
        $affiliates = Affiliate::searchAffiliates($param, $start, $length)->orderBy($columns[$orderCol], $orderDir)->get();

        foreach ($affiliates as $affiliate) {
            $data = [];
            array_push($data, $affiliate->id);

            //create the company html
            $companyHTML = '<span id="aff-'.$affiliate->id.'-comp">'.$affiliate->company.'</span>';
            array_push($data, $companyHTML);

            //create type html
            $typeHTML = '<span id="aff-'.$affiliate->id.'-type" data-type="'.$affiliate->type.'">'.$affiliate_types[$affiliate->type].'</span>';
            array_push($data, $typeHTML);

            //create website url html
            $websiteURLHTML = '<a href="'.$affiliate->website_url.'" target="_blank"><span id="aff-'.$affiliate->id.'-web">'.$affiliate->website_url.'</span></a>';
            array_push($data, $websiteURLHTML);

            //create contact details html
            $addressHTML = '<span class="glyphicon glyphicon-envelope"></span> <span id="aff-'.$affiliate->id.'-add">'.$affiliate->address.'</span><br>';
            $cityHTML = '<span id="aff-'.$affiliate->id.'-cty">'.$affiliate->city.'</span>,';
            $stateHTML = ' <span id="aff-'.$affiliate->id.'-ste">'.$affiliate->state.'</span> ';
            $zipHTML = '<span id="aff-'.$affiliate->id.'-zip">'.$affiliate->zip.'</span><br><span class="glyphicon glyphicon-phone-alt"></span>';
            $phoneHTML = '<span id="aff-'.$affiliate->id.'-phn"> '.$affiliate->phone.'</span>';
            array_push($data, $addressHTML.$cityHTML.$stateHTML.$zipHTML.$phoneHTML);

            //create the status html
            $aff_stat = $affiliate->status == 1 ? 'Active' : 'Inactive';
            $statusHTML = '<span id="aff-'.$affiliate->id.'-stat" data-status="'.$affiliate->status.'">'.$aff_stat.'</span>';
            array_push($data, $statusHTML);

            //construct the action buttons
            $hidden = '<input type="hidden" id="aff-'.$affiliate->id.'-desc" value="'.$affiliate->description.'" />';

            //determine if current user do have edit and delete permissions for affiliates
            $editButton = '';
            $deleteButton = '';

            if ($this->canEdit) {
                $editButton = '<button class="editAffiliate btn-actions btn btn-primary" title="Edit" data-id="'.$affiliate->id.'"><span class="glyphicon glyphicon-pencil"></span></button>';
            }

            if ($this->canDelete) {
                $deleteButton = '<button class="deleteAffiliate btn-actions btn btn-danger" title="Delete" data-id="'.$affiliate->id.'"><span class="glyphicon glyphicon-trash"></span></button>';
            }

            array_push($data, $hidden.$editButton.$deleteButton);

            array_push($affiliatesData, $data);
        }

        // $totalFiltered = count($affiliatesData);

        $responseData = [
            'draw' => intval($inputs['draw']),   // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            'recordsTotal' => $totalRecords,  // total number of records
            'recordsFiltered' => $totalFiltered, // total number of records after searching, if there is no searching then totalFiltered = totalData
            'data' => $affiliatesData,   // total data array
        ];

        return response()->json($responseData, 200);
    }

    /**
     * Server side processing for campaigns tab
     */
    public function campaignList(Request $request): JsonResponse
    {
        $inputs = $request->all();
        $affiliateID = $request->user()->affiliate_id;

        $paramSearch = $inputs['search']['value'];
        $start = $inputs['start'];
        $length = $inputs['length'];
        $category = $inputs['datatable_category'];
        $status = $inputs['datatable_status'];
        $eiq_iframe_id = env('EIQ_IFRAME_ID', 0);

        $columns = [
            // datatable column index  => database column name
            1 => 'c.name',
            3 => 'c.description',
            4 => 'campaign_payout',
        ];

        // $campaignSQL = "SELECT COLUMN_SELECTED FROM campaigns c
        //                 LEFT JOIN campaign_payouts cp ON c.id = cp.campaign_id AND cp.affiliate_id = $affiliateID
        //                 LEFT JOIN affiliate_campaign_requests acr ON c.id = acr.campaign_id AND acr.affiliate_id = $affiliateID
        //                 WHERE ( c.status NOT IN (0,3) and c.campaign_type NOT IN (4,5,6) ) OR (c.id = $eiq_iframe_id AND c.status != 0)";

        $campaignSQL = "SELECT COLUMN_SELECTED FROM campaigns c
                        LEFT JOIN campaign_payouts cp ON c.id = cp.campaign_id AND cp.affiliate_id = $affiliateID
                        LEFT JOIN affiliate_campaign_requests acr ON c.id = acr.campaign_id AND acr.affiliate_id = $affiliateID
                        WHERE (c.status NOT IN (0,3) AND c.campaign_type NOT IN (4,5,6) AND c.is_external = 1) OR (c.id = $eiq_iframe_id AND c.status != 0)";

        //GET TOTAL COUNT OF CAMPAIGNS
        if ($paramSearch == '' && $category == '' && $status == '') {
            $totalSQL = $campaignSQL;
            $totalSQL = str_replace('COLUMN_SELECTED', '*', $totalSQL);
            $totalRecords = count(DB::select($totalSQL));
        }

        //ACTUAL SQL QUERY
        $col_select = 'c.*, acr.status AS affiliate_campaign_request_status, (CASE WHEN(cp.payout IS NULL) THEN c.default_payout ELSE cp.payout END) as campaign_payout ';
        $campaignSQL = str_replace('COLUMN_SELECTED', $col_select, $campaignSQL);
        $campaignSQL .= 'SEARCH_CRITERIA CATEGORY_CRITERIA STATUS_CRITERIA ORDER_CRITERIA';

        //SEARCH_CRITERIA
        $searchCriteria = '';
        if (! empty($paramSearch) || $paramSearch != '') {
            $searchCriteria .= " AND (c.name LIKE '%".$paramSearch."%'";
            $searchCriteria .= " OR c.description LIKE '%".$paramSearch."%'";

            if (is_numeric($paramSearch)) {
                $searchCriteria = $searchCriteria.' OR c.default_payout='.$paramSearch.' OR cp.payout='.$paramSearch;
            }

            $searchCriteria = $searchCriteria.')';
        }
        $campaignSQL = str_replace('SEARCH_CRITERIA', $searchCriteria, $campaignSQL);

        //CATEGORY_CRITERIA
        $categoryCriteria = '';
        if ($category != '') {
            if ($category == 'null' || $category == null) {
                $categoryCriteria = ' AND c.category_id IS NULL';
            } else {
                $categoryCriteria = " AND c.category_id= $category ";
            }
        }
        $campaignSQL = str_replace('CATEGORY_CRITERIA', $categoryCriteria, $campaignSQL);

        //STATUS CRITERIA
        $statusCriteria = '';
        if (! empty($status) || $status != '') {
            if ($status == 0) {
                $addQuery = ' OR acr.status IS NULL ';
            } else {
                $addQuery = '';
            }
            $statusCriteria = ' AND (acr.status='.$status.$addQuery.')';
        }
        $campaignSQL = str_replace('STATUS_CRITERIA', $statusCriteria, $campaignSQL);

        //ORDER CRITERIA
        $orderCriteria = '';
        if (isset($inputs['order'])) {
            $orderCriteria = $orderCriteria.'ORDER BY '.$columns[$inputs['order'][0]['column']].' '.$inputs['order'][0]['dir'];
        } else {
            $orderCriteria = $orderCriteria.'ORDER BY FIELD(c.campaign_type, 4) DESC, c.created_at ASC';
        }
        $campaignSQL = str_replace('ORDER_CRITERIA', $orderCriteria, $campaignSQL);

        if ($paramSearch != '' || $category != '' || $status != '') {
            $totalSQL = $campaignSQL;
            $totalRecords = count(DB::select($totalSQL));
        }

        //LIMIT
        $limitSQL = '';
        if ($length > 1) {
            $limitSQL = ' LIMIT '.$start.','.$length;
        }
        $campaignSQL .= $limitSQL;

        // Log::info($campaignSQL);
        // DB::enableQueryLog();

        $campaignsDataArray = [];
        $campaignsData = DB::select($campaignSQL);

        // Log::info($campaignsData);

        $advertiserNames = Advertiser::pluck('company', 'id');
        $categoryNames = Category::pluck('name', 'id');
        $totalFiltered = $totalRecords;

        // Log::info(DB::getQueryLog());

        foreach ($campaignsData as $data) {
            $id = $data->id;

            if (empty($data->image) || $data->image == '') {
                $img_link = url('images/img_unavailable.jpg');
            } else {
                $img_link = $data->image;
            }
            $row['image'] = '<img id="cmp-'.$id.'-img" class="campaign-row-image" src="'.$img_link.'" width="130"/>';

            $campaign_name = $data->publisher_name != '' ? $data->publisher_name : $data->name;
            $row['name'] = '<span id="cmp-'.$id.'-name">'.$campaign_name.'</span>';

            // Log::info($data->id.' - '.$data->category_id);
            // if(array_key_exists($data->category_id,$categoryNames)) {
            if (isset($categoryNames[$data->category_id])) {
                $row['category_name'] = $categoryNames[$data->category_id];
            } else {
                $row['category_name'] = 'Undefined';
            }

            if (strlen($data->description) > 74) {
                $hiddenDescription = '<textarea id="cmp-'.$id.'-description" hidden>'.$data->description.'</textarea>';
                $conciseDescription = '<span class="campaign-description-row">'.substr($data->description, 0, 74).'</span>';
                $descriptionLink = '<a href="#" data-id="'.$id.'" class="displayMoreDetailsBtn" data-advertiser_name="'.$advertiserNames[$data->advertiser_id].'">More Details</a>';
                $row['description'] = $hiddenDescription.'<span class="campaign-description-row">'.$conciseDescription.'</span> '.$descriptionLink;
            } else {
                $hiddenDescription = '<textarea id="cmp-'.$id.'-description" hidden>'.$data->description.'</textarea>';
                $descriptionLink = '<a href="#" data-id="'.$id.'" class="displayMoreDetailsBtn" data-advertiser_name="'.$advertiserNames[$data->advertiser_id].'">More Details</a>';
                $row['description'] = $hiddenDescription.'<span class="campaign-description-row">'.$data->description.'</span> '.$descriptionLink;
            }

            $row['payouts'] = '$'.$data->campaign_payout.'/Lead';

            switch ($data->affiliate_campaign_request_status) {
                case 1:
                    $row['request_status'] = '<span class="btn btn-default btn-link">Active</span>';
                    break;

                case 2:
                    $row['request_status'] = '<span class="btn btn-default btn-warning">Pending</span>';
                    break;

                case 3:
                    $row['request_status'] = '<span class="btn btn-default btn-danger">Rejected</span>';
                    break;

                default:
                    //default is apply to run
                    $row['request_status'] = '<button class="btn btn-default btn-success applyToRunRequestBtn" data-campaign_id="'.$data->id.'" data-affiliate_id="'.$affiliateID.'">Apply To Run</button>';
                    break;
            }
            //Request is active
            if ($data->affiliate_campaign_request_status == 1) {
                $if_disabled = '';
            } else {
                $if_disabled = 'disabled';
            }
            //action button
            $row['action'] = '<button class="getACodeBtn btn btn-default btn-get-code" data-campaign_id="'.$data->id.'" data-affiliate_id="'.$affiliateID.'" '.$if_disabled.'>Get A Code</button>';

            array_push($campaignsDataArray, $row);
        }

        $responseData = [
            'draw' => intval($inputs['draw']),   // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            'recordsTotal' => $totalRecords,  // total number of records
            'recordsFiltered' => $totalFiltered, // total number of records after searching, if there is no searching then totalFiltered = totalData
            'data' => $campaignsDataArray,   // total data array
        ];

        return response()->json($responseData, 200);
    }

    /**
     * Server side process for affiliate website views statistics
     */
    public function affiliateWebsiteViewsStatistics(Request $request): JsonResponse
    {
        $inputs = $request->all();
        $inputs['affiliate_id'] = $request->user()->affiliate_id;
        $inputs['period'] = $inputs['period'] == '' ? 'none' : $inputs['period'];

        $websites = AffiliateWebsite::where('affiliate_id', $request->user()->affiliate_id)->pluck('website_name', 'id')->toArray();

        $inputs['website_ids'] = array_keys($websites);
        // \Log::info($websites);
        // \Log::info($inputs);
        DB::enableQueryLog();
        // DB::connection('secondary')->enableQueryLog();
        $items = AffiliateWebsiteReport::externalPathRevenue($inputs)->groupBy('website_id')->get();
        Log::info(DB::getQueryLog());
        // Log::info(DB::connection('secondary')->getQueryLog());
        // \Log::info($items);
        $responseData = [
            'records' => $items,
            'websites' => $websites,
            // 'payouts' => AffiliateWebsite::where('affiliate_id', $request->user()->affiliate_id)->pluck('payout','id')->toArray()
        ];

        return response()->json($responseData, 200);
    }

    public function getRegRevenueBreakdown(Request $request): JsonResponse
    {
        $inputs = $request->all();
        // $inputs['affiliate_id'] = $request->user()->affiliate_id;
        $inputs['period'] = $inputs['period'] == '' ? 'none' : $inputs['period'];

        // $websites = AffiliateWebsite::where('website_id', $inputs['period'])->pluck('website_name','id')->toArray();

        // $inputs['website_ids'] = array_keys($websites);
        // \Log::info($websites);
        // \Log::info($inputs);
        // DB::connection('secondary')->enableQueryLog();
        $items = AffiliateWebsiteReport::externalPathRevenue($inputs)->groupBy('date')->get();
        // Log::info(DB::connection('secondary')->getQueryLog());
        // \Log::info($items);
        $responseData = [
            'records' => $items,
            'payouts' => AffiliateWebsite::where('affiliate_id', $request->user()->affiliate_id)->pluck('payout', 'id')->toArray(),
            // 'websites' => $websites
        ];

        return response()->json($responseData, 200);
    }

    /**
     * Server side process for affiliate statistics
     */
    public function affiliateHostedStatistics(Request $request): JsonResponse
    {
        $inputs = $request->all();
        $inputs['affiliate_id'] = $request->user()->affiliate_id;
        $inputs['period'] = $inputs['period'] == '' ? 'none' : $inputs['period'];
        // \Log::info($inputs);
        // \DB::enableQueryLog();
        // \DB::connection('secondary')->enableQueryLog();
        $allStats = HandPAffiliateReport::handPSubIDCampaignRevenueStats($inputs);
        $stats = $allStats->distinct()->get();
        // \Log::info(\DB::getQueryLog());
        // \Log::info(\DB::connection('secondary')->getQueryLog());
        // \Log::info($stats);
        $cmps = $stats->map(function ($st) {
            return $st->campaign_id;
        });
        $campaigns = Campaign::whereIn('id', $cmps)->pluck('name', 'id');

        $responseData = [
            'records' => $stats,
            'campaigns' => $campaigns,
        ];

        return response()->json($responseData, 200);
    }

    public function affiliateWebsiteStatistics(Request $request): JsonResponse
    {
        // DB::enableQueryLog();
        $inputs = $request->all();
        $inputs['affiliate_id'] = $request->user()->affiliate_id;
        $inputs['period'] = $inputs['period'] == '' ? 'none' : $inputs['period'];
        // \Log::info($inputs);
        // \DB::enableQueryLog();
        $allStats = HandPAffiliateReport::websiteRevenueStats($inputs);
        $stats = $allStats->distinct()->get();
        // \Log::info(\DB::getQueryLog());

        $responseData = [
            'records' => $stats,
        ];

        return response()->json($responseData, 200);
    }

    /**
     * Server side process for affiliate external path revenue statistics
     */
    public function externalPathStatistics(Request $request): JsonResponse
    {
        $inputs = $request->all();
        $inputs['affiliate_id'] = $request->user()->affiliate_id;
        $inputs['period'] = $inputs['period'] == '' ? 'none' : $inputs['period'];

        $settings = Setting::where('code', 'publisher_percentage_revenue')->first();
        $share = $settings ? $settings->description : 100;

        // \Log::info($inputs);
        // DB::connection('secondary')->enableQueryLog();
        $items = ExternalPathAffiliateReport::externalPathRevenue($inputs)->get();
        // Log::info(DB::connection('secondary')->getQueryLog());
        // \Log::info($items);

        $total_leads = 0;
        $total_revenue = 0;
        $total_share = 0;
        $share_percentage = $share / 100;

        foreach ($items as $i) {
            $lead_count = $i->count > 0 ? $i->count : 0;
            $revenue = $i->received > 0 ? $i->received : 0;
            $total_leads += $lead_count;
            $total_revenue += $revenue;
            $total_share += $revenue * $share_percentage;
        }

        $responseData = [
            'records' => $items,
            'publisher_revenue_share' => $share,
            'total_leads' => sprintf('%.2f', $total_leads),
            'total_revenue' => sprintf('%.2f', $total_revenue),
            'total_share' => sprintf('%.2f', $total_share),
        ];

        // \Log::info($responseData);
        return response()->json($responseData, 200);
    }

    /**
     * Affiliate dashboard
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function dashboard(): View
    {
        session(['affiliate_name' => auth()->user()->affiliate_id.' - '.auth()->user()->affiliate->company]);

        return view('affiliate.dashboard');
    }

    /**
     * Statistics tab
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function statistics(): View
    {
        session()->put('campaign_created_ordering', 1);
        $reg_report_email = UserMeta::where('key', 'reg_path_revenue_email_report')->where('user_id', auth()->user()->id)->first();

        return view('affiliate.statistics', compact('reg_report_email'));
    }

    public function account(): View
    {
        return view('affiliate.account');
    }

    public function edit_account(): View
    {
        //Burt Codes
        $affiliate = Affiliate::find(auth()->user()->affiliate_id);
        //var_dump($affiliate);
        $settings = UserMeta::where('key', 'coworker_email')->where('user_id', auth()->user()->id)->first();
        $coworker_email = $settings ? $settings->value : '';

        return view('affiliate.edit_account', compact('affiliate', 'coworker_email'));
    }

    public function edit_account_contact_info(UpdateContactInfoRequest $request)
    {
        // \Log::info($request->all());
        $user = User::find($request->input('user_id'));

        $user->title = $request->input('title');
        $user->first_name = $request->input('first_name');
        $user->middle_name = $request->input('middle_name');
        $user->last_name = $request->input('last_name');
        $user->gender = $request->input('gender');
        $user->position = $request->input('position');
        $user->email = $request->input('email');
        $user->address = $request->input('address');
        $user->mobile_number = $request->input('mobile_number');
        $user->phone_number = $request->input('phone_number');
        $user->instant_messaging = $request->input('instant_messaging');
        $user->save();

        if ($request->has('coworker_email')) {
            $setting = UserMeta::firstOrNew([
                'key' => 'coworker_email',
                'user_id' => $request->input('user_id'),
            ]);

            $coworker_email = str_replace(' ', '', $request->input('coworker_email'));
            $coworker_email = str_replace(',', ';', $coworker_email);
            $coworker_email = preg_replace('/\s+/', '', $coworker_email);
            // Log::info($coworker_email);
            $setting->value = $coworker_email;
            $setting->save();
            // Log::info($setting);
        }

        return redirect('/affiliate/edit_account');

        // $responseData = [
        //     'id' => $user->id,
        //     'message' => "Record has been updated..."
        // ];

        // return response()->json($responseData,200);

    }

    public function change_password(): View
    {
        return view('affiliate.change_password');
    }

    //ChangeContactPasswordRequest
    public function change_password_contact_info(ChangeContactPasswordRequest $request): JsonResponse
    {

        $user = User::find($request->input('user_id'));

        $user->password = Hash::make($request->input('password'));
        $user->save();

        $responseData = [
            'id' => $user->id,
            'message' => 'Password Has been Changed.',
        ];

        return response()->json($responseData, 200);
    }

    public function updatePassword(ChangeAffiliatePasswordRequest $request)
    {

    }

    /* PUBLISHER PORTAL END */

    /**
     * Get cities by state
     *
     * @return mixed
     */
    public function getCitiesByState(Request $request)
    {
        $name = $request->input('name');

        $states_cities = config('constants.US_STATES_CITIES');
        $cities = $states_cities[$name];

        return $cities;
    }

    /**
     * Get all available users
     *
     * @return mixed
     */
    public function getAvailableUsers(Request $request)
    {
        $id = $request->input('id');

        return \Bus::dispatch(
            new \GetAvailableUsers('affiliates', $id)
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
     * Store a newly created resource in storage.
     */
    public function store(
        AffiliateRequest $request,
        \App\Http\Services\UserActionLogger $userAction
    ): JsonResponse {
        $affiliate = new Affiliate;
        $affiliate->company = $request->input('company');
        $affiliate->type = $request->input('type');
        $affiliate->website_url = $request->input('website');
        $affiliate->phone = $request->input('phone');
        $affiliate->address = $request->input('address');
        $affiliate->city = $request->input('city');
        $affiliate->state = $request->input('state');
        $affiliate->zip = $request->input('zip');
        $affiliate->description = $request->input('description');
        $affiliate->status = $request->input('status');
        $affiliate->save();

        $responseData = [
            'id' => $affiliate->id,
            'canEdit' => $this->canEdit,
            'canDelete' => $this->canDelete,
        ];

        //Action Logger
        $userAction->logger(2, null, $affiliate->id, null, $affiliate->toArray());

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
        AffiliateRequest $request,
        \App\Http\Services\UserActionLogger $userAction
    ): JsonResponse {
        $affiliate = Affiliate::find($request->input('this_id'));

        $current_state = $affiliate->toArray(); //For Logging

        // $affiliate->id = $request->input('user');

        $affiliate->company = ucwords($request->input('company'));
        $affiliate->website_url = $request->input('website');
        $affiliate->phone = $request->input('phone');
        $affiliate->type = $request->input('type');
        $affiliate->address = $request->input('address');
        $affiliate->city = $request->input('city');
        $affiliate->state = $request->input('state');
        $affiliate->zip = $request->input('zip');
        $affiliate->description = $request->input('description');
        $affiliate->status = $request->input('status');

        $affiliate->save();

        // return $affiliate->id;

        $return['id'] = $affiliate->id;
        $return['user'] = count($affiliate->user) ? $affiliate->user->email : '';

        //Action Logger
        $userAction->logger(2, null, $affiliate->id, $current_state, $affiliate->toArray(), null, function ($old_value, $new_value) {
            $affiliate_type = config('constants.AFFILIATE_TYPE');

            //ReplaceType
            $old_value['type'] = isset($affiliate_type[$old_value['type']]) ? $affiliate_type[$old_value['type']] : $old_value['type'];
            $new_value['type'] = isset($affiliate_type[$new_value['type']]) ? $affiliate_type[$new_value['type']] : $new_value['type'];

            return [$old_value, $new_value];
        });

        return response()->json($return);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(
        Request $request,
        \App\Http\Services\UserActionLogger $userAction
    ) {
        $affiliate = Affiliate::find($request->input('id'));

        //Action Logger
        $userAction->logger(2, null, $request->input('id'), $affiliate->toArray(), null);

        $affiliate->delete();
    }

    /**
     * Affiliate page route for contacts
     */
    public function contacts(): View
    {
        return view('affiliate.contacts');
    }

    /**
     * Affiliate page route for campaigns
     */
    public function campaigns(Request $request): View
    {
        //$campaigns = Campaign::all();
        //$campaigns = Campaign::take(5)->get();
        // $totalCampaignCount = Campaign::count();

        // $advertisers = array_merge(array('0'=>''), Advertiser::pluck('company','id')->toArray());
        // //$affiliates = Affiliate::orderBy('company','asc')->pluck('company','id')->toArray();

        // $affiliates = Affiliate::select('id',DB::raw('CONCAT(id, " - ",company) AS company_name'))
        //     ->orderBy('id','asc')
        //     ->pluck('company_name','id')->toArray();

        // $lead_types = config('constants.LEAD_CAP_TYPES');
        // $campaign_types = config('constants.CAMPAIGN_TYPES');

        // $filter_types = FilterType::select('id',DB::raw('CONCAT(type, " - ",name) AS filter_name'))
        //     ->orderBy('filter_name','asc')
        //     ->pluck('filter_name','id')->toArray();
        $affiliateID = $request->user()->affiliate_id;
        // $revTracker = AffiliateRevenueTracker::where('revenue_tracker_id', $affiliateID)->first()->toArray();
        $websites = AffiliateWebsite::where('affiliate_id', $affiliateID)->pluck('website_name', 'id')->toArray();

        return view('affiliate.campaigns', compact('websites'));
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

        return view('affiliate.searchleads', compact('leads', 'inputs'));
    }

    /**
     * Function of getting the details
     */
    public function getLeadDetails($lead_id): JsonResponse
    {
        $data = [];
        //$data['leadDataADV'] = null;
        //$data['leadDataCSV'] = null;
        //$data['leadMessage'] = null;
        $data['leadSentResult'] = null;

        //$leadDataADV = LeadDataAdv::select('id','value')->where('id','=',$lead_id)->first();
        //$leadDataCSV = LeadDataCsv::select('id','value')->where('id','=',$lead_id)->first();
        //$leadMessage = LeadMessage::select('id','value')->where('id','=',$lead_id)->first();
        $leadSentResult = LeadSentResult::select('id', 'value')->where('id', '=', $lead_id)->first();

        /*
        if($leadDataADV!==null)
        {
            $data['leadDataADV'] = $leadDataADV;
        }

        if($leadDataCSV!==null)
        {
            $data['leadDataCSV'] = $leadDataCSV;
        }

        if($leadMessage!==null)
        {
            $data['leadMessage'] = $leadMessage;
        }
        */

        if ($leadSentResult !== null) {
            $data['leadSentResult'] = $leadSentResult;
        }

        return response()->json($data);
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

        return view('affiliate.revenueStats', compact('leads', 'inputs'));
    }

    /**
     * Top 10 campaigns by revenue yesterday
     */
    public function topTenCampaignsByRevenueYesterday(Request $request): JsonResponse
    {
        $yesterday = Carbon::now()->subDay()->toDateString();
        $user = $request->user();

        $selectSQL = "SELECT leads.campaign_id,CONCAT(campaigns.name,'(',leads.campaign_id,')') AS campaign, (SUM(leads.received) - SUM(leads.payout)) AS profit, SUM(leads.received) AS revenue, SUM(leads.payout) AS cost, leads.created_at FROM leads INNER JOIN campaigns ON leads.campaign_id=campaigns.id WHERE leads.lead_status=1 AND leads.affiliate_id=".$user->affiliate->id." AND DATE(leads.created_at) = DATE('".$yesterday."') GROUP BY leads.campaign_id ORDER BY profit DESC, revenue DESC LIMIT 10";
        $campaigns = DB::select($selectSQL);
        // Log::info('(Affiliate)Top 10 Campaign Revenue per day: '.$selectSQL);

        return response()->json($campaigns);
    }

    /**
     * Top 10 campaigns by revenue from the current week
     */
    public function topTenCampaignsByRevenueForCurrentWeek(Request $request): JsonResponse
    {
        $currentDate = Carbon::now()->toDateString();
        $user = $request->user();

        $selectSQL = "SELECT leads.campaign_id,CONCAT(campaigns.name,'(',leads.campaign_id,')') AS campaign, (SUM(leads.received) - SUM(leads.payout)) AS profit, SUM(leads.received) AS revenue, SUM(leads.payout) AS cost, leads.created_at FROM leads INNER JOIN campaigns ON leads.campaign_id=campaigns.id WHERE leads.lead_status=1 AND leads.affiliate_id=".$user->affiliate->id." AND YEARWEEK(DATE(leads.created_at)) = YEARWEEK(DATE('".$currentDate."')) GROUP BY leads.campaign_id ORDER BY profit DESC, revenue DESC LIMIT 10";
        $campaigns = DB::select($selectSQL);
        // Log::info('(Affiliate)Top 10 Campaign Revenue per day: '.$selectSQL);

        return response()->json($campaigns);
    }

    /**
     * Top 10 campaigns by revenue from the current month
     */
    public function topTenCampaignsByRevenueForCurrentMonth(Request $request): JsonResponse
    {
        $currentDate = Carbon::now()->toDateString();
        $user = $request->user();

        $selectSQL = "SELECT leads.campaign_id,CONCAT(campaigns.name,'(',leads.campaign_id,')') AS campaign, (SUM(leads.received) - SUM(leads.payout)) AS profit, SUM(leads.received) AS revenue, SUM(leads.payout) AS cost, leads.created_at FROM leads INNER JOIN campaigns ON leads.campaign_id=campaigns.id WHERE leads.lead_status=1 AND leads.affiliate_id=".$user->affiliate->id." AND MONTH(DATE(leads.created_at)) = MONTH(DATE('".$currentDate."')) GROUP BY leads.campaign_id ORDER BY profit DESC, revenue DESC LIMIT 10";
        $campaigns = DB::select($selectSQL);

        return response()->json($campaigns);
    }

    /**
     * Different lead counts
     */
    public function leadCounts(Request $request): JsonResponse
    {
        $user = $request->user();

        $selectSQL = "SELECT (CASE WHEN lead_status=0 THEN '#failed-leads' WHEN lead_status=1 THEN '#success-leads' WHEN lead_status=2 THEN '#rejected-leads' WHEN lead_status=3 THEN '#pending-leads' ELSE '#nah' END) AS lead_type, COUNT(id) AS lead_count FROM leads WHERE affiliate_id=".$user->affiliate->id.' GROUP BY lead_status ORDER BY lead_status';
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
        $param['affiliate_id'] = $user->affiliate->id;

        $campaigns = Campaign::activeCampaigns($param)->get();

        return response()->json($campaigns);
    }

    /**
     * Downloading of searched leads
     */
    public function downloadSearchedLeads(): BinaryFileResponse
    {
        $leads = session()->get('searched_leads');
        $title = 'SearchedLeads_'.Carbon::now()->toDateString();

        Excel::create($title, function ($excel) use ($title, $leads) {
            $excel->sheet($title, function ($sheet) use ($leads) {

                // Set auto size for sheet
                $sheet->setAutoSize(true);

                $headers = ['Lead ID', 'Campaign', 'Affiliate', 's1', 's2', 's3', 's4', 's5', 'Received', 'Payout', 'Lead Date', 'Status'];

                //set up the title header row
                $sheet->appendRow($headers);

                //style the headers
                $sheet->cells('A1:L1', function ($cells) {

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

                    $sheet->appendRow([
                        $lead->id,
                        $campaign->name.'('.$campaign->id.')',
                        $lead->affiliate_id,
                        $lead->s1,
                        $lead->s2,
                        $lead->s3,
                        $lead->s4,
                        $lead->s5,
                        $lead->received,
                        $lead->payout,
                        $lead->created_at,
                        $leadStatus[$lead->lead_status],
                    ]);
                }
            });
        })->store('xls', storage_path('downloads'));

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
        $leads = session()->get('revenue_leads');
        $title = 'RevenueLeads_'.Carbon::now()->toDateString();

        Excel::create($title, function ($excel) use ($title, $leads) {
            $excel->sheet($title, function ($sheet) use ($leads) {

                // Set auto size for sheet
                $sheet->setAutoSize(true);

                $headers = ['Lead Date', 'Campaign', 'Affiliate', 's1', 's2', 's3', 's4', 's5', 'Status', 'Lead Count', 'Cost', 'Revenue', 'Profit'];

                //set up the title header row
                $sheet->appendRow($headers);

                //style the headers
                $sheet->cells('A1:M1', function ($cells) {

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

                    $sheet->appendRow([
                        $lead->created_at->toDateString(),
                        $campaign->name.'('.$campaign->id.')',
                        $lead->affiliate_id,
                        $lead->s1,
                        $lead->s2,
                        $lead->s3,
                        $lead->s4,
                        $lead->s5,
                        $leadStatus[$lead->lead_status],
                        $lead->lead_count,
                        $lead->lead_status == 1 ? $lead->cost : 0,
                        $lead->lead_status == 1 ? $lead->revenue : 0,
                        $lead->lead_status == 1 ? $lead->revenue - $lead->cost : 0,
                    ]);
                }
            });
        })->store('xls', storage_path('downloads'));

        $file_path = storage_path('downloads').'/'.$title.'.xls';

        if (file_exists($file_path)) {
            return response()->download($file_path, $title.'.xls', [
                'Content-Length: '.filesize($file_path),
            ]);
        } else {
            exit('Requested file does not exist on our server!');
        }
    }

    public function validateDate($date)
    {
        $d = DateTime::createFromFormat('Y-m-d', $date);

        return $d && $d->format('Y-m-d') == $date;
    }

    public function status($id): JsonResponse
    {
        $affiliate = Affiliate::find($id);

        $response = [];

        if ($affiliate->exists()) {
            $response['affiliate_id'] = $affiliate->id;
            $response['name'] = $affiliate->company." ($affiliate->id)";
            $response['active'] = $affiliate->status == 1;
        }

        return response()->json($response, 200);
    }

    public function addWebsite(Request $request)
    {
        $this->validate($request, [
            'website_name' => 'required',
            'website_payout' => 'required|numeric',
            'revenue_tracker_id' => 'required|numeric',
        ]);

        $inputs = $request->all();
        $status = 0;
        $similar = AffiliateWebsite::where('affiliate_id', $inputs['website_affiliate'])->first();
        if ($similar) {
            $status = $similar->status;
        }

        $website = AffiliateWebsite::firstOrCreate([
            'affiliate_id' => $inputs['website_affiliate'],
            'revenue_tracker_id' => $inputs['revenue_tracker_id'],
            'website_name' => $inputs['website_name'],
            'payout' => $inputs['website_payout'],
            'website_description' => $inputs['website_description'],
            'status' => $status,
            'allow_datafeed' => $inputs['allow_datafeed'],
        ]);

        $setting = Setting::where('code', 'data_feed_excluded_affiliates')->first();
        $excluded_affiliates = explode(',', str_replace(' ', '', $setting->description));
        if ($inputs['allow_datafeed'] == 0) { //ADD
            if (count(array_keys($excluded_affiliates, $inputs['revenue_tracker_id'])) == 0) {
                $excluded_affiliates[] = $inputs['revenue_tracker_id'];
            }
        } else { //REMOVE
            foreach (array_keys($excluded_affiliates, $inputs['revenue_tracker_id']) as $key) {
                unset($excluded_affiliates[$key]);
            }
        }
        $setting->description = implode(',', $excluded_affiliates);
        $setting->save();

        return $website->id;
    }

    public function editWebsite(Request $request)
    {
        $this->validate($request, [
            'website_name' => 'required',
            'website_payout' => 'required|numeric',
            'revenue_tracker_id' => 'required|numeric',

        ]);

        $inputs = $request->all();

        // Log::info($inputs);

        $website = AffiliateWebsite::find($inputs['website_id']);
        $website->website_name = $inputs['website_name'];
        $website->website_description = $inputs['website_description'];
        $website->payout = $inputs['website_payout'];
        $website->revenue_tracker_id = $inputs['revenue_tracker_id'];
        $website->allow_datafeed = $inputs['allow_datafeed'];
        $website->save();

        $setting = Setting::where('code', 'data_feed_excluded_affiliates')->first();
        $excluded_affiliates = explode(',', str_replace(' ', '', $setting->description));
        if ($inputs['allow_datafeed'] == 0) { //ADD
            if (count(array_keys($excluded_affiliates, $inputs['revenue_tracker_id'])) == 0) {
                $excluded_affiliates[] = $inputs['revenue_tracker_id'];
            }
        } else { //REMOVE
            foreach (array_keys($excluded_affiliates, $inputs['revenue_tracker_id']) as $key) {
                unset($excluded_affiliates[$key]);
            }
        }
        $setting->description = implode(',', $excluded_affiliates);
        $setting->save();

        return $website;
    }

    public function deleteWebsite($id)
    {
        $website = AffiliateWebsite::find($id);

        $website->delete();

        return 1;
    }

    public function updateAffiliateWebsitesPayouts(Request $request)
    {

        $this->validate($request, [
            'website_payout' => 'required|numeric',
        ]);

        $inputs = $request->all();

        // Log::info($inputs);

        $websites = AffiliateWebsite::whereIn('id', $inputs['selected_websites'])->update(['payout' => $inputs['website_payout']]);

        return $websites;
    }

    public function deleteMulptipleWebsite(Request $request)
    {
        $inputs = $request->all();

        DB::table('affiliate_websites')->whereIn('id', $inputs['id'])->delete();

        return 1;
    }

    public function getWebsites(Request $request): JsonResponse
    {
        $inputs = $request->all();
        $webData = [];

        $affiliate_id = $inputs['affiliate_id'];
        $param = $inputs['search']['value'];
        $start = $inputs['start'];
        $length = $inputs['length'];
        $orderCol = $inputs['order'][0]['column'];
        $orderDir = $inputs['order'][0]['dir'];

        $columns = [
            'id',
            'id',
            'website_name',
            'website_description',
            'payout',
        ];

        $websites = AffiliateWebsite::searchWebsites($affiliate_id, $param, $start, $length, $columns[$inputs['order'][0]['column']], $inputs['order'][0]['dir'])->get();
        $totalFiltered = AffiliateWebsite::searchWebsites($affiliate_id, $param, null, null, null, null)->count();
        $totalRecords = $param == '' ? $totalFiltered : AffiliateWebsite::getAffiliateWebsites($affiliate_id)->count();

        foreach ($websites as $web) {
            $data = [];

            $idHTML = '<input name="website_id[]" type="checkbox" class="selectAffiliateWebsite" value="'.$web->id.'"> ';
            array_push($data, $idHTML);

            array_push($data, $web->id);

            array_push($data, '<span id="afWeb-'.$web->id.'-revTracker">'.$web->revenue_tracker_id.'</span>');

            $nameHTML = '<span id="afWeb-'.$web->id.'-name">'.$web->website_name.'</span>';
            array_push($data, $nameHTML);

            $desc = strlen($web->website_description) > 75 ? substr($web->website_description, 0, 75).'...' : $web->website_description;
            $descriptionHTML = '<span id="afWeb-'.$web->id.'-descPre">'.$desc.'</span>';
            array_push($data, $descriptionHTML);

            $payout = sprintf('%.3f', $web->payout);
            $payoutHTML = '<span id="afWeb-'.$web->id.'-pay">'.$payout.'</span>';
            array_push($data, $payoutHTML);

            $adf = $web->allow_datafeed == 1 ? 'Yes' : 'No';
            $adfHTML = $adf.'<input type="hidden" value="'.$web->allow_datafeed.'" id="afWeb-'.$web->id.'-adf">';
            array_push($data, $adfHTML);

            //determine if current user do have edit and delete permissions for affiliates
            $editButton = '';
            $deleteButton = '';

            $hiddenHTML = '<textarea id="afWeb-'.$web->id.'-desc" class="hidden">'.$web->website_description.'</textarea>';
            if ($this->canEdit) {
                $editButton = '<button type="button" class="editAffiliateWebsite btn-actions btn btn-default" title="Edit" data-id="'.$web->id.'"><span class="glyphicon glyphicon-pencil"></span></button>';
            }

            if ($this->canDelete) {
                $deleteButton = '<button type="button" class="deleteAffiliateWebsite btn-actions btn btn-default" title="Delete" data-id="'.$web->id.'"><span class="glyphicon glyphicon-trash"></span></button>';
            }

            array_push($data, $editButton.$deleteButton.$hiddenHTML);

            array_push($webData, $data);
        }

        // $totalFiltered = count($affiliatesData);

        $responseData = [
            'draw' => intval($inputs['draw']),   // for every request/draw by clientside , they send a number as a parameter, when
            'recordsTotal' => $totalRecords,  // total number of records
            'recordsFiltered' => $totalFiltered, // total number of records after searching, if there is no searching then totalFiltered = totalData
            'data' => $webData,   // total data array
        ];

        return response()->json($responseData, 200);
    }

    //PUBLISHER PORTAL

    /**
     * Affiliate page route for websites
     */
    public function websites(): View
    {
        return view('affiliate.websites');
    }

    public function updateAffiliateWebsite(Request $request)
    {

        $inputs = $request->all();
        $id = $inputs['this_id'];

        $website = AffiliateWebsite::find($id);
        $website->website_name = $inputs['name'];
        $website->website_description = $inputs['description'];
        $website->save();

        return $website;
    }

    public function getAffiliateWebsites(Request $request): JsonResponse
    {
        $inputs = $request->all();
        $webData = [];
        $statuses = config('constants.UNI_STATUS');

        $affiliate_id = auth()->user()->affiliate_id;
        $param = $inputs['search']['value'];
        $start = $inputs['start'];
        $length = $inputs['length'];
        $orderCol = $inputs['order'][0]['column'];
        $orderDir = $inputs['order'][0]['dir'];

        $columns = [
            'id',
            'website_name',
            'website_description',
            'payout',
            'status',
        ];

        $websites = AffiliateWebsite::searchWebsites($affiliate_id, $param, $start, $length, $columns[$inputs['order'][0]['column']], $inputs['order'][0]['dir'])->get();
        $totalFiltered = AffiliateWebsite::searchWebsites($affiliate_id, $param, null, null, null, null)->count();
        $totalRecords = $param == '' ? $totalFiltered : AffiliateWebsite::getAffiliateWebsites($affiliate_id)->count();

        foreach ($websites as $web) {
            $data = [];

            array_push($data, $web->id);

            $nameHTML = '<span id="afWeb-'.$web->id.'-name">'.$web->website_name.'</span>';
            array_push($data, $nameHTML);

            $desc = strlen($web->website_description) > 75 ? substr($web->website_description, 0, 75).'...' : $web->website_description;
            $descriptionHTML = '<span id="afWeb-'.$web->id.'-descPre">'.$desc.'</span>';
            array_push($data, $descriptionHTML);

            $payout = sprintf('%.3f', $web->payout);
            $payoutHTML = '<span id="afWeb-'.$web->id.'-pay">'.$payout.'</span>';
            array_push($data, $payoutHTML);

            $statusHTML = '<span id="afWeb-'.$web->id.'-status">'.$statuses[$web->status].'</span>';
            array_push($data, $statusHTML);

            $hiddenHTML = '<textarea id="afWeb-'.$web->id.'-desc" class="hidden">'.$web->website_description.'</textarea>';
            $editButton = '<button type="button" class="editAffiliateWebsite btn-actions btn btn-default" title="Edit" data-id="'.$web->id.'"><span class="glyphicon glyphicon-pencil"></span></button>';

            array_push($data, $editButton.$hiddenHTML);

            array_push($webData, $data);
        }

        // $totalFiltered = count($affiliatesData);

        $responseData = [
            'draw' => intval($inputs['draw']),   // for every request/draw by clientside , they send a number as a parameter, when
            'recordsTotal' => $totalRecords,  // total number of records
            'recordsFiltered' => $totalFiltered, // total number of records after searching, if there is no searching then totalFiltered = totalData
            'data' => $webData,   // total data array
        ];

        return response()->json($responseData, 200);
    }

    public function updateAffiliateWebsiteStatus(Request $request): JsonResponse
    {
        $affiliate_id = $request->input('affiliate_id');
        $current_status = $request->input('status');
        $new_status = $current_status == 1 ? 0 : 1;

        $update_count = AffiliateWebsite::where('affiliate_id', '=', $affiliate_id)->update(['status' => $new_status]);

        return response()->json([
            'new_status' => $new_status,
            'update_count' => $update_count,
        ], 200);
    }

    public function getAutocompleteAffiliate(Request $request): JsonResponse
    {
        // Log::info($request->all());
        $term = trim($request->input('term'));

        $affiliates = Affiliate::where('id', 'like', '%'.$term.'%')->pluck('id')->toArray();
        // Log::info($affiliates);

        return response()->json($affiliates, 200);
    }

    public function userMetaUpdate(Request $request): JsonResponse
    {
        $meta = UserMeta::firstOrNew([
            'user_id' => auth()->user()->id,
            'key' => $request->key,
        ]);
        $meta->value = $request->value;
        $meta->save();

        return response()->json($meta, 200);
    }
}
