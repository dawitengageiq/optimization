<?php

namespace App\Http\Controllers;

use App\Affiliate;
use App\AffiliateCampaign;
use App\AffiliateCampaignRequest;
use App\AffiliateWebsite;
use App\Campaign;
use App\CampaignConfig;
use App\CampaignContent;
use App\CampaignCreative;
use App\CampaignFilter;
use App\CampaignFilterGroup;
use App\CampaignFilterGroupFilter;
use App\CampaignJsonContent;
use App\CampaignPayout;
use App\CampaignPostingInstruction;
use App\Commands\GetUserActionPermission;
use App\FilterType;
use App\Http\Requests\CampaignFilterGroupFilterRequest;
use App\Http\Requests\CampaignFilterRequest;
use App\Http\Services;
use App\Jobs\OfferGoesDownJob;
use App\Jobs\UpdateCampaignPayoutJob;
use App\Jobs\UpdateCampaignTypeOrder;
use App\Lead;
use App\LeadCount;
use App\LinkOutCount;
use Bus;
use Carbon\Carbon;
use DB;
use File;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Log;
use Maatwebsite\Excel\Facades\Excel;
use Storage;

class CampaignController extends Controller
{
    protected $canEdit = false;

    protected $canDelete = false;

    protected $user;

    protected $logger;

    public function __construct(Services\UserActionLogger $logger)
    {
        //get the user
        $user = auth()->user();

        $this->canEdit = Bus::dispatch(new GetUserActionPermission($user, 'use_edit_campaign'));
        $this->canDelete = Bus::dispatch(new GetUserActionPermission($user, 'use_delete_campaign'));

        $this->user = $user;
        $this->logger = $logger;
    }

    /**
     * Will return searched data for campaigns that is compatible with data tables server side processing
     */
    public function index(Request $request): JsonResponse
    {
        $inputs = $request->all();
        // Log::info($inputs);
        $campaignsData = [];

        $param = $inputs['search']['value'];
        $start = $inputs['start'];
        $length = $inputs['length'];
        $filters = null;

        if (isset($inputs['json_content_filter'])) {
            $filters['json_content'] = $inputs['json_content_filter'];
        }

        if (isset($inputs['show_inactive'])) {
            $filters['show_inactive'] = $inputs['show_inactive'];
        }

        $columns = [
            'campaigns.priority',
            'campaigns.id',
            'campaigns.image',
            'campaigns.name',
            'advertisers.company',
            'campaigns.campaign_type',
            'campaigns.lead_cap_type',
            'campaigns.lead_cap_value',
            'campaigns.default_received',
            'campaigns.rate',
            'campaigns.status',
        ];

        $orderBy = isset($inputs['order'][0]['column']) ? $columns[$inputs['order'][0]['column']] : null;
        $orderDir = isset($inputs['order'][0]['dir']) ? $inputs['order'][0]['dir'] : null;

        //get all lead cap types contants
        $leadCapTypes = config('constants.LEAD_CAP_TYPES');
        $campaignTypes = config('constants.CAMPAIGN_TYPES');

        //get the campaign status constants
        $campaignStatuses = config('constants.CAMPAIGN_STATUS');

        // DB::connection('secondary')->enableQueryLog();
        $campaigns = Campaign::searchCampaigns($param, $start, $length, $orderBy, $orderDir, $filters)
            ->select(DB::RAW('campaigns.*, advertisers.company'))->get();
        $totalFiltered = Campaign::searchCampaigns($param, null, null, null, null, $filters)->count();
        // Log::info(DB::connection('secondary')->getQueryLog());

        foreach ($campaigns as $campaign) {
            $data = [];

            //create the priority html
            $priorityHTML = '<span id="cmp-'.$campaign->id.'-prio">'.$campaign->priority.'</span>';
            array_push($data, $priorityHTML);

            array_push($data, $campaign->id);

            //create the html image
            $img = '';
            if (is_null($campaign->image) || $campaign->image == '') {
                $url = url('images/img_unavailable.jpg');
                $img = 'none';
            } else {
                $url = $campaign->image;
            }

            $imageHTML = '<span class="imgPreTbl" id="cmp-'.$campaign->id.'-img" data-img="'.$img.'"><img src="'.$url.'"/></span>';
            array_push($data, $imageHTML);

            //create campaign name html
            $campaignNameHTML = '<span id="cmp-'.$campaign->id.'-name">'.$campaign->name.'</span>';
            array_push($data, $campaignNameHTML);

            //create company html
            $companyNameHTML = '<span id="cmp-'.$campaign->id.'-adv" data-adv="'.$campaign->advertiser_id.'">'.$campaign->company.' ('.$campaign->advertiser_id.')'.'</span>';
            array_push($data, $companyNameHTML);

            //create the campaign type
            $campaignType = $campaignTypes[$campaign->campaign_type];

            $typeHTML = '<span id="cmp-'.$campaign->id.'-type" data-type="'.$campaign->campaign_type.'">'.$campaignType.'</span>';
            array_push($data, $typeHTML);

            //determine the right cap type
            $leadCapTypeHTML = '<span id="cmp-'.$campaign->id.'-lct" data-type="'.$campaign->lead_cap_type.'">'.$leadCapTypes[$campaign->lead_cap_type].'</span>';
            array_push($data, $leadCapTypeHTML);

            //create the lead cap value html
            $leadCapValueHTML = '<span id="cmp-'.$campaign->id.'-lcv">'.$campaign->lead_cap_value.'</span>';
            array_push($data, $leadCapValueHTML);

            //create the notes html
            // $notes = strlen($campaign->notes) > 75 ? substr($campaign->notes, 0, 75).'...' : $campaign->notes;
            // $notesHTML = '<span id="cmp-'.$campaign->id.'-notes-preview">'.$notes.'</span>';
            // array_push($data,$notesHTML);

            //create the default receive html
            $defaultReceiveHTML = '<span id="cmp-'.$campaign->id.'-drcv">'.$campaign->default_received.'</span>';
            array_push($data, $defaultReceiveHTML);

            //rate
            $statusHTML = '<span id="cmp-'.$campaign->id.'-rate">'.$campaign->rate.'</span>';
            array_push($data, $statusHTML);

            //determine the right status
            $statusHTML = '<span id="cmp-'.$campaign->id.'-stat" data-status="'.$campaign->status.'">'.$campaignStatuses[$campaign->status].'</span>';
            array_push($data, $statusHTML);

            $date = Carbon::parse($campaign->created_at);
            $now = Carbon::now();
            $diffDays = $date->diffInDays($now);

            //construct the buttons
            $createdHidden = '<input type="hidden" id="cmp-'.$campaign->id.'-created" value="'.$campaign->created_at.'">';
            $createdHidden .= '<input type="hidden" id="cmp-'.$campaign->id.'-crtDiff" value="'.$diffDays.'">';
            $notesHidden = '<textarea id="cmp-'.$campaign->id.'-notes" class="hidden" disabled>'.$campaign->notes.'</textarea>';
            $descHidden = '<textarea id="cmp-'.$campaign->id.'-desc" class="hidden" disabled>'.$campaign->description.'</textarea>';
            $campaignDefaultPayoutHidden = '<input type="hidden" id="cmp-'.$campaign->id.'-dpyt" value="'.$campaign->default_payout.'"/>';
            // $campaignDefaultReceivedHidden = '<input type="hidden" id="cmp-'.$campaign->id.'-drcv" value="'.$campaign->default_received.'"/>';
            $categoryHidden = '<input type="hidden" id="cmp-'.$campaign->id.'-ctgry" value="'.$campaign->category_id.'">';
            $linkOutOfferHidden = '<input type="hidden" id="cmp-'.$campaign->id.'-lnkOutOffer" value="'.$campaign->linkout_offer_id.'">';
            $programIdHidden = '<input type="hidden" id="cmp-'.$campaign->id.'-prgId" value="'.$campaign->olr_program_id.'">';
            $publisherHidden = '<input type="hidden" id="cmp-'.$campaign->id.'-pubN" value="'.$campaign->publisher_name.'">';
            $publisherHidden .= '<input type="hidden" id="cmp-'.$campaign->id.'-pubE" value="'.$campaign->is_external.'">';
            $publisherHidden .= '<input type="hidden" id="cmp-'.$campaign->id.'-advEmail" value="'.$campaign->advertiser_email.'">';
            //determine if current user do have edit and delete permissions for campaigns
            $editButton = '';
            $deleteButton = '';

            if ($this->canEdit) {
                $editButton = '<button class="editCampaign btn-actions btn btn-primary" title="Edit" data-id="'.$campaign->id.'"><span class="glyphicon glyphicon-pencil"></span></button>';
                // if(!in_array($campaign->campaign_type, [4, 3, 7])) {
                if ($campaign->campaign_type != 4 && $campaign->campaign_type != 6) {
                    $editButton .= '<button class="campaignFormBuilder btn-actions btn btn-info" title="Edit" data-id="'.$campaign->id.'" data-type="'.$campaign->campaign_type.'"><span class="glyphicon glyphicon-list-alt"></span></button>';

                    $editButton .= '<button class="campaignJsonFormBuilder btn-actions btn btn-success" title="Edit" data-id="'.$campaign->id.'" data-type="'.$campaign->campaign_type.'"><span class="glyphicon glyphicon glyphicon glyphicon-equalizer"></span></button>';
                }
            }

            if ($this->canDelete) {
                $deleteButton = '<button class="deleteCampaign btn-actions btn btn-danger" title="Delete" data-id="'.$campaign->id.'"><span class="glyphicon glyphicon-trash"></span></button>';
            }

            array_push($data, $notesHidden.$descHidden.$campaignDefaultPayoutHidden.$categoryHidden.$linkOutOfferHidden.$programIdHidden.$publisherHidden.$editButton.$deleteButton.$createdHidden);
            array_push($campaignsData, $data);
        }

        $responseData = [
            'draw' => intval($inputs['draw']),   // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            'recordsTotal' => Campaign::count(),  // total number of records
            'recordsFiltered' => $totalFiltered, // total number of records after searching, if there is no searching then totalFiltered = totalData
            'data' => $campaignsData,   // total data array
        ];

        return response()->json($responseData, 200);
    }

    /**
     * Store newly create campaign
     */
    public function store(Request $request): JsonResponse
    {
        $this->validate($request, [
            'name' => 'required',
            'advertiser' => 'required|exists:advertisers,id',
            'lead_type' => 'required',
            'lead_value' => 'required_unless:lead_type,0|numeric',
            // 'default_payout'    => 'required|numeric',
            // 'default_received'  => 'required|numeric',
            //'image'             => 'image|mimes:jpeg,bmp,png',
            //'description'   => 'required',
            'status' => 'required',
            'campaign_type' => 'required',
            'category' => 'required',
            'linkout_offer_id' => 'required_if:campaign_type,5|numeric',
            'program_id' => 'numeric',
        ]);

        $fileName = null;

        if ($request->hasFile('image')) {

            $file = $request->file('image');

            $ext = $file->getClientOriginalExtension();
            $filename = md5(time()).".$ext";

            $destinationPath = 'images/campaigns/';
            //Storage::disk('local')->put($destinationPath.$fileName, File::get($file));

            //$filename = $file->getClientOriginalName();
            $uploadSuccess = $file->move($destinationPath, $filename);
            $fileName = url().'/'.$destinationPath.$filename;
        } else {
            if ($request->input('image') != '' || ! is_null($request->input('image'))) {
                $url = $request->input('image');
                $ext = pathinfo($url, PATHINFO_EXTENSION);
                $filename = md5(time()).".$ext";
                $file = file_get_contents($url);
                $save = file_put_contents('images/campaigns/'.$filename, $file);
                $fileName = url().'/images/campaigns/'.$filename;
            }
        }

        if ($request->input('lead_type') == 0) {
            $lead_value = 0;
        } else {
            $lead_value = $request->input('lead_value');
        }

        $lastCount = Campaign::count();

        $campaign = new Campaign;
        $campaign->name = trim($request->input('name'));
        $campaign->advertiser_id = $request->input('advertiser');
        $campaign->status = $request->input('status');
        $campaign->description = $request->input('description');
        $campaign->notes = $request->input('notes');
        $campaign->image = $fileName;
        $campaign->lead_cap_type = $request->input('lead_type');
        $campaign->lead_cap_value = $lead_value;
        $campaign->default_received = 0;
        $campaign->default_payout = 0;
        $campaign->priority = $lastCount + 1;
        $campaign->campaign_type = $request->input('campaign_type');
        $campaign->category_id = $request->input('category');
        $campaign->linkout_offer_id = in_array($request->input('campaign_type'), [5, 6]) ? $request->input('linkout_offer_id') : null;
        $campaign->olr_program_id = $request->input('campaign_type') != 4 || $request->input('campaign_type') != 5 || $request->input('campaign_type') != 6 ? $request->input('program_id') : null;
        $campaign->rate = $request->input('rate');
        $campaign->advertiser_email = $request->input('advertiser_email');
        // $campaign->publisher_name = $request->input('publisher_name');
        // $campaign->is_external = $request->input('is_external');
        $campaign->status_dupe = $request->input('status') != 0 ? $request->input('status') : 1;
        $campaign->linkout_cake_status = $request->input('campaign_type') == 5 && $request->input('status') != 0 ? 1 : 0;
        $campaign->save();

        CampaignConfig::firstOrCreate([
            'id' => $campaign->id,
        ]);

        CampaignContent::firstOrCreate([
            'id' => $campaign->id,
        ]);

        /*Link Out Cap*/
        if ($campaign->campaign_type == 5 && $campaign->lead_cap_type != 0) {
            $exp_date = $this->getLinkOutCapExpDate($campaign->lead_cap_type);
            // $linkOutCap = LinkOutCount::firstOrNew(['campaign_id' => $campaign->id, 'affiliate_id' => null]);
            $linkOutCap = LinkOutCount::firstOrCreate([
                'campaign_id' => $campaign->id,
                'affiliate_id' => null,
                'expiration_date' => $exp_date,
            ]);
        }

        //construct the response data
        $responseData = [
            'id' => $campaign->id,
            'img' => $fileName,
            'priority' => $campaign->priority,
            'canEdit' => $this->canEdit,
            'canDelete' => $this->canDelete,
        ];

        //Action Logger: Add Campaign Info
        $value_mask = [
            'status' => config('constants.CAMPAIGN_STATUS'),
            'lead_cap_type' => config('constants.LEAD_CAP_TYPES'),
            'campaign_type' => config('constants.CAMPAIGN_TYPES'),
            'is_external' => ['False', 'True'],
        ];

        $this->logger->log(3, 31, $campaign->id, null, null, $campaign->toArray(), null, $value_mask);

        return response()->json($responseData, 200);
    }

    public function update(Request $request): JsonResponse
    {
        // Log::info($request->all());
        // Log::info(apache_request_headers());
        // Log::info('Header: ' . $request->header('X-CSRF-Token'));
        $this->validate($request, [
            'name' => 'required',
            'advertiser' => 'required',
            'lead_type' => 'required',
            'lead_value' => 'required_unless:lead_type,0|numeric',
            'priority' => 'required',
            'default_payout' => 'sometimes|numeric',
            'default_received' => 'sometimes|numeric',
            'status' => 'required',
            'campaign_type' => 'required',
            'category' => 'required',
            'linkout_offer_id' => 'required_if:campaign_type,5|numeric',
            'program_id' => 'numeric',
        ]);

        $errors = [];
        $noErrors = true;

        $campaign_id = $request->input('this_id');
        $campaign = Campaign::find($campaign_id);

        //Status Change Checker & Content Check
        if ($campaign->status != $request->input('status')) {
            if ($request->input('status') == 2 || $request->input('status') == 1) {
                $content = CampaignContent::find($campaign_id);
                if ($content->stack == '') {
                    $errors = ['No campaign stack found.'];
                } else {
                    $errors = $this->contentTagChecking($content->stack);
                }

                if (count($errors) > 0) {
                    $noErrors = false;
                }
            }
        }

        if ($noErrors) {
            $current_state = $campaign->toArray(); //for logging

            $fileName = null;

            if ($request->hasFile('image')) { //check if has image file

                //check if has existing image
                if ($campaign->image != '' || ! is_null($campaign->image)) {
                    $path_parts = pathinfo($campaign->image);
                    $img_path = public_path('images\campaigns\\'.$path_parts['basename']);
                    if (file_exists($img_path)) { //check if file exists
                        unlink($img_path);
                    }
                }

                $file = $request->file('image');

                $ext = $file->getClientOriginalExtension();
                $filename = md5(time()).".$ext";

                $destinationPath = 'images/campaigns/';

                $uploadSuccess = $file->move($destinationPath, $filename);
                $fileName = url().'/'.$destinationPath.$filename;
            } else {
                if ($request->input('image') != '' || ! is_null($request->input('image'))) { //check if has image url

                    //check if has existing image
                    if ($campaign->image != '' || ! is_null($campaign->image)) {
                        $path_parts = pathinfo($campaign->image);
                        $img_path = public_path('images\campaigns\\'.$path_parts['basename']);
                        if (file_exists($img_path)) { //check if file exists
                            unlink($img_path);
                        }
                    }

                    $url = $request->input('image');
                    $ext = pathinfo($url, PATHINFO_EXTENSION);
                    $filename = md5(time()).".$ext";
                    $file = file_get_contents($url);
                    $save = file_put_contents('images/campaigns/'.$filename, $file);
                    $fileName = url().'/images/campaigns/'.$filename;
                } else {
                    $fileName = $campaign->image;
                }
            }

            /* CAP CHANGE CHECKER */
            if ($request->input('lead_type') == 0) {
                $lead_value = 0;
            } else {
                $lead_value = $request->input('lead_value');
            }

            if ($campaign->lead_cap_type != $request->input('lead_type')) {

                /* LINK OUT CAP */
                if ($request->input('campaign_type') == 5) {
                    $linkOutCap = LinkOutCount::firstOrNew(['campaign_id' => $campaign->id, 'affiliate_id' => null]);
                    if ($request->input('lead_type') != 0) {
                        $exp_date = $this->getLinkOutCapExpDate($request->input('lead_type'));
                        $linkOutCap->expiration_date = $exp_date;
                        $linkOutCap->count = 0;
                        $linkOutCap->save();
                    } else {
                        $linkOutCap->delete();
                    }
                }

                LeadCount::where('campaign_id', $campaign->id)->whereNull('affiliate_id')->update(['reference_date' => Carbon::now()->toDateString(), 'count' => 0]);

                $campaign->lead_cap_type = $request->input('lead_type');
            }

            /* CAMPAIGN TYPE CHECKER */
            if ($campaign->campaign_type != $request->input('campaign_type')) {
                $tracking = (new UpdateCampaignTypeOrder($campaign_id, $campaign->campaign_type, $request->input('campaign_type')));
                $this->dispatch($tracking);

                $campaign->campaign_type = $request->input('campaign_type');
            }

            //Status Change Checker
            if ($campaign->status != $request->input('status')) {
                if ($request->input('status') == 0) { //If inactive
                    $tracking = (new OfferGoesDownJob($campaign->id, null, null));
                    $this->dispatch($tracking);
                } elseif ($campaign->status == 2 && $request->input('status') == 1) { //If public >> private
                    $tracking = (new OfferGoesDownJob($campaign->id, null, 'p2p'));
                    $this->dispatch($tracking);
                }
            }

            $campaign->lead_cap_value = $lead_value;
            $campaign->name = trim($request->input('name'));
            $campaign->advertiser_id = $request->input('advertiser');
            $campaign->status = $request->input('status');
            $campaign->description = $request->input('description');
            $campaign->notes = $request->input('notes');
            $campaign->image = $fileName;
            if ($request->has('default_payout')) {
                $campaign->default_payout = $request->input('default_payout');
            }
            if ($request->has('default_received')) {
                $campaign->default_received = $request->input('default_received');
            }
            $campaign->category_id = $request->input('category');
            $campaign->linkout_offer_id = in_array($request->input('campaign_type'), [5, 6]) ? $request->input('linkout_offer_id') : null;
            $campaign->olr_program_id = $request->input('campaign_type') != 4 || $request->input('campaign_type') != 5 || $request->input('campaign_type') != 6 ? $request->input('program_id') : null;
            $campaign->rate = $request->input('rate');
            $campaign->publisher_name = $request->input('publisher_name');
            $campaign->is_external = $request->input('is_external');
            $campaign->advertiser_email = $request->input('advertiser_email');
            if ($request->input('status') != 0) {
                $campaign->status_dupe = $request->input('status');
            }
            $campaign->linkout_cake_status = $request->input('campaign_type') == 5 && $request->input('status') != 0 ? 1 : 0;
            $campaign->save();

            //Action Logger: Edit Campaign Info
            $value_mask = [
                'status' => config('constants.CAMPAIGN_STATUS'),
                'lead_cap_type' => config('constants.LEAD_CAP_TYPES'),
                'campaign_type' => config('constants.CAMPAIGN_TYPES'),
                'is_external' => ['False', 'True'],
            ];

            $this->logger->log(3, 31, $campaign->id, null, $current_state, $campaign->toArray(), null, $value_mask);

            $current_state = $campaign->toArray(); //for logging

            // Log::info($campaign);

            $return['id'] = $campaign_id;
            $return['image'] = $campaign->image;

            //Check if order has changed
            $affected = [];

            if (is_null($campaign->priority)) {
                $campaign->priority = $request->input('priority');
                $campaign->save();
                //Action Logger: Update Campaign Priority
                $this->logger->log(3, 31, $campaign->id, null, $current_state, $campaign->toArray());
            } else {
                if ($campaign->priority != $request->input('priority')) {
                    $affected = $this->orderPriority($campaign->id, $campaign->priority, $request->input('priority'));

                    foreach ($affected as $priority => $id) {
                        $campaign = Campaign::find($id);

                        $current_state = $campaign->toArray(); //for logging

                        $campaign->priority = $priority;
                        $campaign->save();

                        //Action Logger: Update Campaign Priority
                        $this->logger->log(3, 31, $campaign->id, null, $current_state, $campaign->toArray());
                    }

                }
            }

            $return['affected'] = $affected;
        }

        $return['status'] = $noErrors;
        $return['errors'] = $errors;

        return response()->json($return);
    }

    private function getLinkOutCapExpDate($cap_type)
    {
        switch ($cap_type) {
            case 1 : //daily
                $exp_date = Carbon::now()->endOfDay();
                break;
            case 2 : //weekly
                $exp_date = Carbon::now()->endOfWeek();
                break;
            case 3 : //monthly
                $exp_date = Carbon::now()->endOfMonth();
                break;
            case 4: //yearly
                $exp_date = Carbon::now()->endOfYear();
                break;
            default:
                $exp_date = null;
                break;
        }

        return $exp_date;
    }

    public function xxsample()
    {
        $campaigns = Campaign::orderBy('priority', 'asc')->pluck('id', 'priority');
        $new_prio = Campaign::max('priority');
        $old_prio = 49;
        $id = 49;
        if ($old_prio > $new_prio) {
            $affected[$new_prio] = $id;

            for ($x = $new_prio; $x <= $old_prio; $x++) {
                if ($campaigns[$x] != $id) {
                    $affected[] = $campaigns[$x];
                }
            }

        } else {
            $c = $old_prio;

            for ($x = $old_prio; $x <= $new_prio; $x++) {
                if ($campaigns[$x] != $id) {
                    $affected[$c++] = $campaigns[$x];
                }
            }

            $affected[$new_prio] = $id;
        }

        // return $affected;

        // $id = 49;
        // $affected = $this->orderPriority($id, 49, $max_prio);
        foreach ($affected as $priority => $campaign_id) {
            if ($campaign_id != $id) {
                $campaign = Campaign::find($campaign_id);
                $campaign->priority = $priority;
                $campaign->save();
            }
        }

        return $affected;
    }

    public function destroy(Request $request)
    {
        $max_prio = Campaign::max('priority');
        $id = $request->input('id');
        $campaign = Campaign::find($id);
        $campaign_prio = $campaign->priority;
        // $campaign->delete();

        //check if has existing image
        if ($campaign->image != '' || ! is_null($campaign->image)) {
            $path_parts = pathinfo($campaign->image);
            $img_path = public_path('images\campaigns\\'.$path_parts['basename']);

            if (file_exists($img_path)) { //check if file exists
                unlink($img_path);
            }
        }

        $affected = $this->orderPriority($id, $campaign_prio, $max_prio);

        foreach ($affected as $priority => $campaign_id) {
            //if($campaign_id != $id) {
            $cmpPrio = Campaign::find($campaign_id);
            $cur_ste = $cmpPrio->toArray();
            $cmpPrio->priority = $priority;
            $cmpPrio->save();
            //}
            //Action Log: Campaign Priority Update
            $this->logger->log(3, 31, $campaign_id, null, $cur_ste, $cmpPrio->toArray());
        }

        //Action Logger: Delete Campaign
        $value_mask = [
            'status' => config('constants.CAMPAIGN_STATUS'),
            'lead_cap_type' => config('constants.LEAD_CAP_TYPES'),
            'campaign_type' => config('constants.CAMPAIGN_TYPES'),
            'is_external' => ['False', 'True'],
        ];
        $this->logger->log(3, 31, $campaign->id, null, $campaign->toArray(), null, null, $value_mask);

        $campaign->delete();

        $tracking = (new UpdateCampaignTypeOrder($id, $campaign->campaign_type, 0));
        $this->dispatch($tracking);

        return $affected;
    }

    public function getCampaignInfo(Request $request): JsonResponse
    {
        $eiq_iframe_id = env('EIQ_IFRAME_ID', 0);
        $campaign = $request->input('id');

        /* FILTER GROUPS */
        $filter_groups = CampaignFilterGroup::where('campaign_id', $campaign)->orderBy('name', 'asc')->get();
        $list_filter_groups = [];
        foreach ($filter_groups as $fg) {
            $list_filter_groups[$fg->name] = $fg->id;
        }
        $return['filter_group_list'] = $list_filter_groups;
        $return['filter_groups'] = $filter_groups;

        /* AFFILIATES */
        $return_value['available'] = []; // Affiliate::getAvailableAffiliates($campaign)->pluck('name','id')->toArray();
        // $affiliated = AffiliateCampaign::where('campaign_id','=',$campaign)->orderBy('affiliate_id','asc')->get();
        $affiliated = AffiliateCampaign::join('affiliates', 'affiliates.id', '=', 'affiliate_campaign.affiliate_id')
            ->where('campaign_id', '=', $campaign)
            ->select(['affiliate_campaign.id', 'affiliate_id', 'affiliates.company', 'lead_cap_type', 'lead_cap_value'])
            ->orderBy('affiliate_id', 'asc')->get();
        $c = 0;
        $lead_cap_type = config('constants.LEAD_CAP_TYPES');

        if ($eiq_iframe_id == $campaign) {
            $websites = AffiliateWebsite::selectRAW('DISTINCT(affiliate_id) as affiliate_id, status')->pluck('status', 'affiliate_id')->toArray();
        }

        foreach ($affiliated as $affiliate) {
            $return_value['affiliated'][$c]['id'] = $affiliate->id;
            $return_value['affiliated'][$c]['affiliate_id'] = $affiliate->affiliate_id;
            $return_value['affiliated'][$c]['affiliate_name'] = $affiliate->affiliate_id.' - '.$affiliate->company;
            $return_value['affiliated'][$c]['cap_type'] = $affiliate->lead_cap_type;
            $return_value['affiliated'][$c]['cap_type_name'] = $lead_cap_type[$affiliate->lead_cap_type];
            $return_value['affiliated'][$c]['cap_value'] = $affiliate->lead_cap_value;

            $affiliate_status = 0;
            if ($eiq_iframe_id == $campaign) {
                if (in_array($affiliate->affiliate_id, array_keys($websites))) {
                    $affiliate_status = $websites[$affiliate->affiliate_id];
                }
            }
            $return_value['affiliated'][$c]['status'] = $affiliate_status;

            $c++;
        }

        $return['affiliates'] = $return_value;

        /* PAYOUTS */
        $pay_return['available'] = []; //Affiliate::getAvailableAffiliatesForPayout($campaign)->pluck('name','id')->toArray();
        $payouts = CampaignPayout::join('affiliates', 'affiliates.id', '=', 'campaign_payouts.affiliate_id')
            ->where('campaign_id', '=', $campaign)
            ->select(['campaign_payouts.id', 'affiliate_id', 'affiliates.company', 'received', 'payout', 'campaign_id'])
            ->orderBy('affiliate_id', 'asc')->get();
        $k = 0;
        foreach ($payouts as $payout) {
            $pay_return['affiliate'][$k]['id'] = $payout->id;
            $pay_return['affiliate'][$k]['campaign_id'] = $payout->campaign_id;
            $pay_return['affiliate'][$k]['affiliate_id'] = $payout->affiliate_id;
            $pay_return['affiliate'][$k]['affiliate_name'] = $payout->affiliate_id.' - '.$payout->affiliate->company;
            $pay_return['affiliate'][$k]['received'] = $payout->received;
            $pay_return['affiliate'][$k]['payout'] = $payout->payout;
            $k++;
        }
        $return['payouts'] = $pay_return;

        /* CONFIG */
        $campaignConfig = CampaignConfig::find($campaign);
        $return['config'] = [];

        if ($campaignConfig != null) {
            $return['config'] = $campaignConfig->toArray();
        }

        /* CONTENT */
        $content = CampaignContent::with('campaign_type')->find($campaign);

        if ($content) {
            $campaign_long = $content->long;
            $campaign_stack = $content->stack;
            $campaign_high_paying = [
                'id' => $content->id,
                'name' => $content->name,
                'deal' => $content->deal,
                'description' => $content->description,
                'fields' => collect($content->fields)->toArray(),
                'additional_fields' => collect($content->additional_fields)->toArray(),
                'rules' => collect($content->rules)->toArray(),
                'sticker' => $content->sticker,
                'cpa_creative_id' => $content->cpa_creative_id,
                'type' => $content->campaign_type->campaign_type,
            ];
            $stack_lock = $content->stack_lock;
        } else {
            $campaign_long = '';
            $campaign_stack = '';
            $campaign_high_paying = [];
            $stack_lock = 0;
        }

        $return['content']['long'] = $campaign_long;
        $return['content']['stack'] = $campaign_stack;
        $return['content']['stack_lock'] = $stack_lock;
        $return['content']['high_paying'] = $campaign_high_paying;

        /* Posting Instruction */
        $cpi = CampaignPostingInstruction::find($campaign);
        if ($cpi) {
            $posting_instruction = $cpi->posting_instruction;
            $sample_code = $cpi->sample_code;
        } else {
            $posting_instruction = '';
            $sample_code = '';
        }
        $return['posting_instruction'] = $posting_instruction;
        $return['sample_code'] = $sample_code;

        // Log::info($return);
        return response()->json($return);
    }

    public function getAvailableAffiliates(Request $request)
    {
        $id = $request->input('id');

        return Affiliate::getCampaignAffiliateChoices($id)->pluck('name', 'id')->toArray();
        //return Affiliate::getCampaignAffiliateChoices($id)->get()->toArray();
    }

    public function getAffiliatesData(Request $request)
    {
        $id = $request->input('id');
        $return_value['available'] = Affiliate::getAvailableAffiliates($id)->pluck('name', 'id')->toArray();
        $affiliated = AffiliateCampaign::where('campaign_id', '=', $id)->get();
        $c = 0;
        foreach ($affiliated as $affiliate) {
            $return_value['affiliated'][$c]['id'] = $affiliate->id;
            $return_value['affiliated'][$c]['campaign_id'] = $affiliate->campaign_id;
            $return_value['affiliated'][$c]['affiliate_id'] = $affiliate->affiliate_id;
            $return_value['affiliated'][$c]['affiliate_name'] = $affiliate->affiliate_id.' - '.$affiliate->affiliate->company;
            $return_value['affiliated'][$c]['cap_type'] = $affiliate->lead_cap_type;
            $return_value['affiliated'][$c]['lead_count'] = $affiliate->lead_cap_value;
            $c++;
        }

        return $return_value;
    }

    public function addCampaignAffiliate(Request $request)
    {
        // Log::info($request->all());
        $this->validate($request, [
            'lead_cap_type' => 'required',
            'lead_cap_value' => 'numeric',
        ]);

        $campaign_id = $request->input('this_campaign');
        $affiliates = $request->input('affiliates');
        $lead_cap_type = $request->input('lead_cap_type');
        $lead_cap_value = $request->input('lead_cap_value');

        //If Lead Cap Type is Unlimited, Value is 0
        if ($lead_cap_type == 0) {
            $lead_cap_value = 0;
        }
        //If lead cap type not unlimited and lead cap value
        if ($lead_cap_type != 0 && $lead_cap_value == '') {
            $lead_cap_value = 0;
        }

        $campaign = Campaign::find($campaign_id);

        foreach ($affiliates as $affiliate_id) {
            $affiliate = new AffiliateCampaign;
            $affiliate->campaign_id = $campaign_id;
            $affiliate->affiliate_id = $affiliate_id;
            $affiliate->lead_cap_type = $lead_cap_type;
            $affiliate->lead_cap_value = $lead_cap_value;
            $affiliate->save();

            //Action Logger: Add Campaign Affiliate
            $this->logger->log(3, 33, $campaign_id, "Add campaign affiliate id: $affiliate_id", null, $affiliate->toArray(), null, [
                'lead_cap_type' => config('constants.LEAD_CAP_TYPES'),
            ]);

            /*Link Out Cap*/
            if ($campaign->campaign_type == 5 && $lead_cap_type != 0) {
                $exp_date = $this->getLinkOutCapExpDate($lead_cap_type);
                $linkOutCap = LinkOutCount::firstOrCreate([
                    'campaign_id' => $campaign->id,
                    'affiliate_id' => $affiliate->affiliate_id,
                    'expiration_date' => $exp_date,
                ]);
            }

            $new_affiliates[] = $affiliate->id;
        }

        return $new_affiliates;
    }

    public function editCampaignAffiliate(Request $request)
    {
        // Log::info($request->all());

        $this->validate($request, [
            'edit_lead_cap_type' => 'required',
            'edit_lead_cap_value' => 'numeric',
            'selected_affiliate' => 'required',
        ]);

        // $affiliates = $request->input('select_affiliate');
        $affiliates = $request->input('selected_affiliate');
        $lead_cap_type = $request->input('edit_lead_cap_type');
        $lead_cap_value = $request->input('edit_lead_cap_value');

        //If Lead Cap Type is Unlimited, Value is 0
        if ($lead_cap_type == 0) {
            $lead_cap_value = 0;
        }
        //If lead cap type not unlimited and lead cap value
        if ($lead_cap_type != 0 && $lead_cap_value == '') {
            $lead_cap_value = 0;
        }

        $campaign_id = $request->input('this_campaign');
        // $campaign = Campaign::find($campaign_id);
        $campaign = Campaign::where('id', $campaign_id)->select('campaign_type')->first();

        // AffiliateCampaign::whereIn('id', $affiliates)->update(['lead_cap_type' => $lead_cap_type, 'lead_cap_value' => $lead_cap_value]);
        // LeadCount::where('campaign_id', $campaign_id)->whereIn('affiliate_id',$affiliates)->update(['reference_date' => Carbon::now()->toDateString(), 'count' => 0]);

        foreach ($affiliates as $affiliate) {
            $aff_camp = AffiliateCampaign::find($affiliate);

            $current_state = $aff_camp->toArray();

            /* LINK OUT CAP */
            if ($campaign->campaign_type == 5 && $aff_camp->lead_cap_type != $lead_cap_type) {
                $linkOutCap = LinkOutCount::firstOrNew(['campaign_id' => $campaign_id, 'affiliate_id' => $aff_camp->affiliate_id]);
                if ($lead_cap_type != 0) {
                    $exp_date = $this->getLinkOutCapExpDate($lead_cap_type);
                    $linkOutCap->expiration_date = $exp_date;
                    $linkOutCap->count = 0;
                    $linkOutCap->save();
                } else {
                    $linkOutCap->delete();
                }

            }

            $leadCount = LeadCount::where('campaign_id', $aff_camp->campaign_id)->where('affiliate_id', $aff_camp->affiliate_id)->first();
            if ($leadCount) {
                if ($aff_camp->lead_cap_type != $lead_cap_type) {
                    $leadCount->reference_date = Carbon::now()->toDateString();
                    $leadCount->count = 0;
                    $leadCount->save();
                }
            }
            $aff_camp->lead_cap_type = $lead_cap_type;
            $aff_camp->lead_cap_value = $lead_cap_value;
            $aff_camp->save();

            //Action Logger: Edit Campaign Affiliate
            $this->logger->log(3, 33, $campaign_id, "Update campaign affiliate id: $aff_camp->affiliate_id", $current_state, $aff_camp->toArray(), null, [
                'lead_cap_type' => config('constants.LEAD_CAP_TYPES'),
            ]);
        }

        return $affiliates;
    }

    public function deleteCampaignAffiliate(Request $request)
    {
        $affiliates = $request->input('select_affiliate');
        $campaign = AffiliateCampaign::find($affiliates[0])->campaign_id;
        $the_affiliates = [];
        $eiq_frame = env('EIQ_IFRAME_ID', 0);

        foreach ($affiliates as $id) {
            /* FIND AFFILIATE CAMPAIGN DATA*/
            $affiliate = AffiliateCampaign::find($id);
            $affiliate_id = $affiliate->affiliate_id;

            /* CHECK AFFILIATE REQUEST IF ALLOWED */
            $affiliate_request = AffiliateCampaignRequest::where('affiliate_id', $affiliate_id)
                ->where('campaign_id', $campaign)
                ->where('status', 1)
                ->first();
            if ($affiliate_request) { //if exists; change status to deactivate;
                $affiliate_request->status = 0;
                $affiliate_request->save();
            }

            $the_affiliates[] = $affiliate_id;

            //Action Logger: Delete Campaign Affiliate
            $this->logger->log(3, 33, $campaign, "Delete campaign affiliate id: $affiliate->affiliate_id", $affiliate->toArray(), null, null, [
                'lead_cap_type' => config('constants.LEAD_CAP_TYPES'),
            ]);

            /* DELETE AFFILIATE CAMPAIGN */
            $affiliate->delete();
        }

        LinkOutCount::where('campaign_id', $campaign)->whereIn('affiliate_id', $the_affiliates)->delete();

        // If Campaign is EIQ Iframe, delete affiliate websites also
        if ($campaign == $eiq_frame) {
            AffiliateWebsite::whereIn('affiliate_id', $the_affiliates)->delete();
        }

        $tracking = (new OfferGoesDownJob($campaign, $the_affiliates, null));
        $this->dispatch($tracking);

        return Affiliate::getAvailableAffiliates($campaign)->pluck('name', 'id')->toArray();
    }

    public function addCampaignPayout(Request $request)
    {
        $this->validate($request, [
            'this_campaign' => 'required',
            'payout_receivable' => 'required|numeric',
            'payout_payable' => 'required|numeric',
            'payout' => 'required',
        ]);

        $affiliates = $request->input('payout');

        foreach ($affiliates as $affiliate) {
            $payout = new CampaignPayout;
            $payout->campaign_id = $request->input('this_campaign');
            $payout->affiliate_id = $affiliate;
            $payout->received = $request->input('payout_receivable');
            $payout->payout = $request->input('payout_payable');
            $payout->save();
            $new_affiliates[] = $payout->id;

            //Action Logger: Add Campaign Payout
            $this->logger->log(3, 34, $payout->campaign_id, 'Add payout for campaign affiliate id: '.$payout->affiliate_id, null, $payout->toArray(), null, null);
        }

        return $new_affiliates;
    }

    public function editCampaignPayout(Request $request)
    {
        $this->validate($request, [
            'edit_payout_payable' => 'required|numeric',
            'edit_payout_receivable' => 'required|numeric',
            'selected_payout' => 'required',
        ]);

        $affiliates = $request->input('selected_payout');

        foreach ($affiliates as $affiliate) {
            $payout = CampaignPayout::find($affiliate);

            $current_state = $payout->toArray();

            $payout->received = $request->input('edit_payout_receivable');
            $payout->payout = $request->input('edit_payout_payable');
            $payout->save();

            //Action Logger: Edit Campaign Payout
            $this->logger->log(3, 34, $payout->campaign_id, 'Update payout for campaign affiliate id: '.$payout->affiliate_id, $current_state, $payout->toArray(), null, null);
        }

        return $affiliates;
    }

    public function deleteCampaignPayout(Request $request)
    {
        $affiliates = $request->input('select_payout');
        // Log::info($affiliates);

        foreach ($affiliates as $id) {
            $payout = CampaignPayout::find($id);

            //Action Logger: Edit Campaign Payout
            $this->logger->log(3, 34, $payout->campaign_id, 'Delete payout for campaign affiliate id: '.$payout->affiliate_id, $payout->toArray(), null, null, null);

            $payout->delete();
        }

        return $affiliates;
    }

    public function editCampaignConfig(Request $request)
    {
        $this->validate($request, [
            'post_url' => 'required|url',
            'post_header' => 'required',
            'post_data' => 'required',
            'post_data_map' => 'required',
            'post_method' => 'required',
            'post_success' => 'required',
            'post_data_fixed_value' => 'required',
            // 'ping_url'       => 'required',
            // 'ping_success'   => 'required'
        ]);

        $id = $request->input('this_campaign');
        $config = CampaignConfig::FirstOrNew(['id' => $id]);

        //for logging
        if (! $config->exists) {
            $current_state = null;
        } else {
            $current_state = $config->toArray();
        }

        // $config = CampaignConfig::find($id);
        $config->post_url = $request->input('post_url');
        $config->post_header = $request->input('post_header');
        $config->post_data = $request->input('post_data');
        $config->post_data_fixed_value = $request->input('post_data_fixed_value');
        $config->post_data_map = $request->input('post_data_map');
        $config->post_method = $request->input('post_method');
        $config->post_success = $request->input('post_success');
        $config->ping_url = $request->input('ping_url');
        $config->ping_success = $request->input('ping_success');
        $config->ftp_sent = $request->input('ftp_sent') == '' ? 0 : $request->input('ftp_sent');
        $config->ftp_protocol = $request->input('ftp_protocol') == '' ? null : $request->input('ftp_protocol');
        $config->ftp_username = $request->input('ftp_username') == '' ? null : $request->input('ftp_username');
        $config->ftp_password = $request->input('ftp_password') == '' ? null : $request->input('ftp_password');
        $config->ftp_host = $request->input('ftp_host') == '' ? null : $request->input('ftp_host');
        $config->ftp_port = $request->input('ftp_port') == '' ? null : $request->input('ftp_port');
        $config->ftp_timeout = $request->input('ftp_timeout') == '' ? null : $request->input('ftp_timeout');
        $config->ftp_directory = $request->input('ftp_directory') == '' ? null : $request->input('ftp_directory');
        $config->email_sent = $request->input('email_sent') == '' ? 0 : $request->input('email_sent');
        $config->email_to = $request->input('email_to') == '' ? null : $request->input('email_to');
        $config->email_title = $request->input('email_title') == '' ? null : $request->input('email_title');
        $config->email_body = $request->input('email_body') == '' ? null : $request->input('email_body');
        $config->save();

        //Action Logger: Edit Campaign Config
        $key_mask = [
            'ftp_sent' => 'send through ftp',
            'email_sent' => 'send through email',
        ];
        $value_mask = [
            'ftp_sent' => ['No', 'Yes'],
            'email_sent' => ['No', 'Yes'],
            'ftp_protocol' => config('constants.FTP_PROTOCOL_TYPES'),
        ];

        $this->logger->log(3, 35, $id, null, $current_state, $config->toArray(), $key_mask, $value_mask);
    }

    public function campaignConfigInterface(Request $request)
    {
        // Log::info($request->all());
        $this->validate($request, [
            'post_url' => 'required|url',
            'post_method' => 'required',
            'post_success' => 'required',
        ]);

        $inputs = $request->all();

        $post_data = [];
        foreach ($inputs['field'] as $adv => $ours) {
            if ($ours != 'fixed_data') {
                $post_data[$ours] = $adv;
            }
        }

        $post_data_fixed = [];
        foreach ($inputs['fixed'] as $key => $value) {
            if ($value != '' && $inputs['field'][$key] == 'fixed_data') {
                $post_data_fixed[$key] = $value;
            }
        }

        $config = CampaignConfig::find($inputs['this_campaign']);

        $current_state = $config->toArray();

        $config->post_url = $inputs['post_url'];
        $config->post_header = $inputs['post_header'] != '' ? $inputs['post_header'] : 'no post header needed';
        $config->post_data_map = $inputs['post_data_map'] != '' ? $inputs['post_data_map'] : 'no post data map needed';
        $config->post_method = $inputs['post_method'];
        $config->post_success = $inputs['post_success'];
        $config->post_data = json_encode($post_data);
        $config->post_data_fixed_value = count($post_data_fixed) > 0 ? json_encode($post_data_fixed) : 'no post data fixed value';
        $config->save();
        // Log::info($config->post_data);
        // Log::info($config->post_data_fixed_value);

        //Action Logger: Edit Campaign Config
        $this->logger->log(3, 35, $inputs['this_campaign'], null, $current_state, $config->toArray(), null, null);

        return $config;
    }

    public function editCampaignLongContent(Request $request)
    {
        $this->validate($request, [
            'content' => 'required|max:65533',
        ]);

        $id = $request->input('this_campaign');

        $content = CampaignContent::firstOrNew([
            'id' => $id,
        ]);

        if ($content->exists) {
            $current_state = $content->toArray();
        } else {
            $current_state = null;
        }

        $content->long = $request->input('content');
        $content->save();

        //Action Logger: Update Campaign Long Content
        $this->logger->log(3, 36, $id, null, $current_state, $content->toArray(), [
            'long' => 'long content',
        ], null);
    }

    public function editCampaignStackContent(Request $request): Response
    {
        // Log::info($request->all());
        // Log::info(mb_strlen($request->input('content')));
        $this->validate($request, [
            'content' => 'required|max:65533',
        ]);

        $errors = [];
        $noErrors = true;

        //Content Check
        $errors = $this->contentTagChecking($request->input('content'));
        if (count($errors) > 0) {
            $noErrors = false;
        }

        if ($noErrors) {
            $id = $request->input('this_campaign');

            $content = CampaignContent::firstOrNew([
                'id' => $id,
            ]);

            if ($content->exists) {
                $current_state = $content->toArray();
            } else {
                $current_state = null;
            }

            $content->stack = $request->input('content');
            $content->save();

            //Action Logger: Update Campaign Stack Content
            $this->logger->log(3, 37, $id, null, $current_state, $content->toArray(), [
                'stack' => 'stack content',
            ], null);
        }

        return response(['status' => $noErrors, 'errors' => $errors]);
    }

    //    public function editCampaignHighPayingContent(Request $request)
    //    {
    //        $this->validate($request, [
    //            'content'    => 'required|max:65533',
    //        ]);
    //
    //        $id = $request->input('this_campaign');
    //
    //        $content = CampaignContent::firstOrNew([
    //            'id'    =>  $id
    //        ]);
    //
    //        $content->high_paying = $request->input('content');
    //        $content->save();
    //    }

    public function editCampaignHighPayingContent(Request $request): Response
    {
        $this->validate($request, [
            'name' => 'required',
            'description' => 'required',
            'sticker' => 'required',
            'deal' => 'required',
        ]);

        $campaign = CampaignContent::find($request->id);
        $current_state = $campaign->toArray();
        if ($campaign) {
            $campaign->update($request->all());

            //Action Logger: Update High Paying
            $this->logger->log(3, 38, $request->id, null, $current_state, $campaign->toArray(), [
                'high_paying' => 'high paying content',
            ], null);

            return response(['code' => 200, 'message' => 'Success']);
        }

        return response(['code' => 400, 'message' => 'Bad Request']);
    }

    public function editCampaignPostingInstruction(Request $request)
    {
        $this->validate($request, [
            'sample_code' => 'required|max:65533',
            'posting_instruction' => 'required|max:65533',
        ]);

        $id = $request->input('this_campaign');

        $content = CampaignPostingInstruction::firstOrNew([
            'id' => $id,
        ]);

        if ($content->exists) {
            $current_state = $content->toArray();
        } else {
            $current_state = null;
        }

        $content->sample_code = $request->input('sample_code');
        $content->posting_instruction = $request->input('posting_instruction');
        $content->save();

        //Action Logger: Update Campaign Posting Instruction
        $this->logger->log(3, 39, $id, null, $current_state, $content->toArray(), null, null);
    }

    /* SORT PRIORITY */
    //one by one
    public function orderPriority($id, $old_prio, $new_prio)
    {
        // Log::info('DUPE CHECKER');
        // Log::info($id.' - '.$old_prio.' - '.$new_prio);
        $campaigns = Campaign::orderBy('priority', 'asc')->pluck('priority', 'id')->toArray();
        $count_campaigns = count($campaigns);
        // Log::info($campaigns);

        //Dupe Checker
        $camp_dupes = array_count_values($campaigns);
        $camp_diffs = array_count_values($camp_dupes);
        $dupe_checker = count($camp_diffs); //Should always be 1

        //Kulang Checker
        $priorities = array_values($campaigns); // Get Priorities
        $missing_checker = [];
        for ($x = 0; $x < $count_campaigns; $x++) {
            if (! in_array($priorities[$x], $missing_checker)) {
                $missing_checker[] = $priorities[$x];
            }
        }

        if ($dupe_checker != 1 || count($missing_checker) != $count_campaigns) {
            // Log::info('ORDER PRIORITY FIX ERROR');
            // Log::info($camp_diffs);
            // Log::info($missing_checker);
            $prio = 1;
            $new_priority = [];
            foreach ($campaigns as $cid => $priority) {
                $campaign = Campaign::find($cid);
                $campaign->priority = $prio;
                $campaign->save();
                $new_priority[$prio] = $cid;
                // Log::info($prio.' - '.$cid);
                $prio++;
            }
            $campaigns = $new_priority;
        } else {
            $campaigns = array_keys($campaigns);
            $correct_list = [];
            $counter = 1;
            foreach ($campaigns as $c) {
                $correct_list[$counter++] = $c;
            }
            $campaigns = $correct_list;
        }

        // Log::info($campaigns);
        // $campaigns = Campaign::orderBy('priority','asc')->pluck('id');
        // Log::info($campaigns);

        if ($old_prio > $new_prio) {
            $c = $new_prio + 1;
            $affected[$new_prio] = $id;
            // Log::info('Check');
            for ($x = $new_prio; $x <= $old_prio; $x++) {
                // Log::info($affected);
                if ($campaigns[$x] != $id) {
                    $affected[$c++] = $campaigns[$x];
                }
            }

            // for($x = $new_prio; $x <= $old_prio; $x++)
            // {
            //     if($campaigns[$x] != $id) $affected[] = $campaigns[$x];
            // }
        } else {
            // Log::info('Check 1');
            $c = $old_prio;
            $affected = [];
            for ($x = $old_prio; $x <= $new_prio; $x++) {
                // Log::info($affected);
                if ($campaigns[$x] != $id) {
                    $affected[$c++] = $campaigns[$x];
                }
            }

            // for($x = $old_prio; $x <= $new_prio; $x++)
            // {
            //     if($campaigns[$x] != $id) $affected[$c++] = $campaigns[$x];
            // }

            $affected[$new_prio] = $id;
        }
        // Log::info($affected);
        return $affected;
    }

    public function getNameIDPriority()
    {
        $campaigns = Campaign::select('id', 'name', 'priority', 'campaign_type', 'status')
            ->orderBy('priority', 'asc')->get()->toArray();

        $campaign_types = config('constants.CAMPAIGN_TYPES');
        $campaign_status = config('constants.CAMPAIGN_STATUS');

        for ($x = 0; $x < count($campaigns); $x++) {
            $campaigns[$x]['campaign_type'] = $campaign_types[$campaigns[$x]['campaign_type']];
            $campaigns[$x]['status'] = $campaign_status[$campaigns[$x]['status']];
        }

        return $campaigns;
    }

    /* SORT BY REVENUE */
    public function sortCampaignByRevenue()
    {

        $stack_type_order = \App\Setting::where('code', 'stack_path_campaign_type_order')->first()->string_value;
        $stack_type_order = json_decode($stack_type_order, true);
        $stack_type_order = array_values($stack_type_order);

        $campaigns_by_revenue = Campaign::leftJoin('leads', 'leads.campaign_id', '=', 'campaigns.id')
            ->select(DB::raw('SUM(leads.received) as total'), 'campaigns.id', 'campaigns.name', 'campaigns.priority', 'campaigns.campaign_type', 'campaigns.status')
            ->groupBy('campaigns.id')
            ->groupBy('leads.lead_status')
            ->orderByRaw('leads.lead_status IS NULL')
            ->orderByRaw('FIELD(leads.lead_status,1,0,2,3)')
            ->orderBy('total', 'desc')
            ->orderByRaw('FIELD(campaigns.campaign_type, '.implode(',', $stack_type_order).')')
            ->orderBy('priority', 'asc')
            ->get()->toArray();

        $campaigns = [];
        $campaign_checker = [];
        $campaign_types = config('constants.CAMPAIGN_TYPES');
        $campaign_status = config('constants.CAMPAIGN_STATUS');

        foreach ($campaigns_by_revenue as $campaign) {
            if (! in_array($campaign['id'], $campaign_checker)) {
                $campaign['campaign_type'] = $campaign_types[$campaign['campaign_type']];
                $campaign['status'] = $campaign_status[$campaign['status']];
                $campaigns[] = $campaign;
                $campaign_checker[] = $campaign['id'];
            }
        }

        return $campaigns;
    }

    public function updateCampaignPriority(Request $request)
    {
        $campaigns_string = $request->input('campaigns');
        $campaigns_string = str_replace('cmpprt[]=', '', $campaigns_string);
        $new_order = explode('&', $campaigns_string);
        // Log::info($new_order);

        $current_string = $request->input('current_sort');
        $current_string = str_replace('old_prio%5B%5D=', '', $current_string);
        $current_sort = explode('&', $current_string);
        // Log::info($current_sort);

        $counter = 1;

        // $camp_prio = [];
        for ($x = 0; $x < count($new_order); $x++) {
            // $camp_prio[$counter] = $new_order[$x];
            if ($new_order[$x] != $current_sort[$x]) {
                $campaign = Campaign::find($new_order[$x]);

                $current_state = $campaign->toArray(); //for logging

                $campaign->priority = $counter;
                $campaign->save();

                //Action Logger: Update Campaign Priority
                $this->logger->log(3, 31, $campaign->id, 'Update info via sort campaigns', $current_state, $campaign->toArray());
            }
            $counter++;
        }

        // Log::info($camp_prio);

        return $this->getNameIDPriority();
    }

    public function checkCampaignDailyStatus()
    {
        //get all campaigns
        $campaigns = Campaign::all();

        //create the email messate
        $subject = 'Campaign that has no success leads for this day ('.Carbon::now()->toDateString().')';
        $message = 'Campaign that has no success leads as of '.Carbon::now()->toDateString().'.'."\r\n\r\n\r\n";
        $to = 'burt@engageiq.com';
        $headers = 'From: burt@engageiq.com'."\r\n".
            'Reply-To: burt@engageiq.com'."\r\n".
            'X-Mailer: PHP/'.phpversion();

        $counter = 0;
        foreach ($campaigns as $campaign) {
            //get all success leads
            $succesLeads = Lead::getDailyCampaignSuccessLeads($campaign->id, Carbon::now()->toDateString())->get()->toArray();

            if (count($succesLeads) == 0) {
                $message = $message.$campaign->name.'('.$campaign->id.')'."\r\n\r\n";
                $counter++;
            }
        }

        if ($counter > 0) {
            mail($to, $subject, $message, $headers);
        }
    }

    /* FILTER GROUP */
    public function getCampaignFilterGroupFilters(Request $request)
    {
        $id = $request->input('id');
        // return $filter_groups = CampaignFilterGroupFilter::where('campaign_filter_group_id', $id)->get()->toArray();

        $cFilters = CampaignFilterGroupFilter::where('campaign_filter_group_id', $id)->get();
        if (count($cFilters) > 0) {
            $c = 0;
            foreach ($cFilters as $cFilter) {
                $filters[$c]['id'] = $cFilter->id;
                $filters[$c]['filter_type_id'] = $cFilter->filter_type_id;
                $filters[$c]['filter_name'] = $cFilter->filter_type->type.' - '.$cFilter->filter_type->name;
                if (! is_null($cFilter->value_text) || $cFilter->value_text != '') {
                    $filters[$c]['value_type'] = 1;
                    $filters[$c]['value_value'] = $cFilter->value_text;
                } elseif (! is_null($cFilter->value_boolean) || $cFilter->value_boolean != '') {
                    $filters[$c]['value_type'] = 2;
                    $filters[$c]['value_value'] = $cFilter->value_boolean == 1 ? 'True' : 'False';
                } elseif (! is_null($cFilter->value_min_date) || $cFilter->value_min_date != '' || ! is_null($cFilter->value_max_date) || $cFilter->value_max_date != '') {
                    $filters[$c]['value_type'] = 3;
                    $filters[$c]['value_value'] = $cFilter->value_min_date.' - '.$cFilter->value_max_date;
                } elseif (! is_null($cFilter->value_min_integer) || $cFilter->value_min_integer != '' || ! is_null($cFilter->value_max_integer) || $cFilter->value_max_integer != '') {
                    $filters[$c]['value_type'] = 4;
                    $filters[$c]['value_value'] = $cFilter->value_min_integer.' - '.$cFilter->value_max_integer;
                } elseif (! is_null($cFilter->value_array) || $cFilter->value_array != '') {
                    $filters[$c]['value_type'] = 5;
                    $filters[$c]['value_value'] = $cFilter->value_array;
                } elseif (! is_null($cFilter->value_min_time) || $cFilter->value_min_time != '' || ! is_null($cFilter->value_max_time) || $cFilter->value_max_time != '') {
                    $filters[$c]['value_type'] = 6;
                    $min_time = Carbon::parse($cFilter->value_min_time)->format('h:i A');
                    $max_time = Carbon::parse($cFilter->value_max_time)->format('h:i A');
                    $filters[$c]['value_value'] = $min_time.' - '.$max_time;
                } else {
                    $filters[$c]['value_type'] = 0;
                    $filters[$c]['value_value'] = '';
                }

                $filters[$c]['value_text'] = $cFilter->value_text;
                $filters[$c]['value_min_integer'] = $cFilter->value_min_integer;
                $filters[$c]['value_max_integer'] = $cFilter->value_max_integer;
                $filters[$c]['value_min_date'] = $cFilter->value_min_date;
                $filters[$c]['value_max_date'] = $cFilter->value_max_date;
                $filters[$c]['value_min_time'] = Carbon::parse($cFilter->value_min_time)->format('h:i A');
                $filters[$c]['value_max_time'] = Carbon::parse($cFilter->value_max_time)->format('h:i A');
                $filters[$c]['value_boolean'] = $cFilter->value_boolean;
                $filters[$c]['value_array'] = $cFilter->value_array;

                $c++;
            }
        } else {
            $filters = null;
        }

        return $filters;
    }

    public function addCampaignFilterGroup(Request $request)
    {
        $campaign_id = $request->input('this_campaign');
        $campaign_filter_groups = CampaignFilterGroup::where('campaign_id', $campaign_id)->pluck('name')->toArray();
        $campaign_filter_groups = implode($campaign_filter_groups, ',');
        $this->validate($request, [
            'filter_group_name' => 'required|not_in:'.$campaign_filter_groups,
            'filter_group_status' => 'required',
        ]);

        $filter_group = new CampaignFilterGroup();
        $filter_group->campaign_id = $campaign_id;
        $filter_group->name = $request->input('filter_group_name');
        $filter_group->description = $request->input('filter_group_description');
        $filter_group->status = $request->input('filter_group_status');
        $filter_group->save();

        //Action Logger: Add Campaign Filter Group
        $this->logger->log(3, 32, $campaign_id, 'Add filter group named: '.$filter_group->name, null, $filter_group->toArray(), null, ['status' => ['Inactive', 'Active']]);

        return $filter_group->id;
    }

    public function editCampaignFilterGroup(Request $request)
    {
        $this->validate($request, [
            'filter_group_name' => 'required',
            'filter_group_status' => 'required',
        ]);

        $id = $request->input('this_id');

        $filter_group = CampaignFilterGroup::find($id);

        $current_state = $filter_group->toArray(); //for logging

        $filter_group->name = $request->input('filter_group_name');
        $filter_group->description = $request->input('filter_group_description');
        $filter_group->status = $request->input('filter_group_status');
        $filter_group->save();

        //Action Logger: Edit Campaign Filter Group
        $this->logger->log(3, 32, $filter_group->campaign_id, 'Update filter group named: '.$filter_group->name, $current_state, $filter_group->toArray(), null, ['status' => ['Inactive', 'Active']]);

        return $id;
    }

    public function deleteCampaignFilterGroup(Request $request)
    {
        $id = $request->input('id');
        $filter_group = CampaignFilterGroup::find($id);

        //Action Logger: Delete Campaign Filter Group
        $this->logger->log(3, 32, $filter_group->campaign_id, 'Delete filter group named: '.$filter_group->name, $filter_group->toArray(), null, null, ['status' => ['Inactive', 'Active']]);

        $filter_group->delete();
    }

    public function addCampaignFilter(CampaignFilterGroupFilterRequest $request)
    {
        $campaign_filter_group_filters = [];
        $filter_groups = $request->input('filter_group');
        $value_type = $request->input('value_type');
        $filter_type = $request->input('filter_type');

        //for logging
        $filter_names = FilterType::where('id', $filter_type)->pluck('name', 'id');
        $value_mask = [
            'value_boolean' => ['False', 'True'],
            'filter_type_id' => $filter_names,
        ];
        $campaign_id = CampaignFilterGroup::where('id', $filter_groups[0])->first()->campaign_id;
        $group_names = CampaignFilterGroup::whereIn('id', $filter_groups)->pluck('name', 'id');

        foreach ($filter_groups as $filter_group) {
            $filter = new CampaignFilterGroupFilter();
            $filter->campaign_filter_group_id = $filter_group;
            $filter->filter_type_id = $filter_type;

            switch ($value_type) {
                case 1:
                    $filter->value_text = $request->input('filter_value_01_text');
                    break;
                case 2:
                    $filter->value_boolean = $request->input('filter_value_01_select');
                    break;
                case 3:
                    $filter->value_min_date = $request->input('filter_value_01_date');
                    $filter->value_max_date = $request->input('filter_value_02_date');
                    break;
                case 4:
                    $filter->value_min_integer = $request->input('filter_value_01_input');
                    $filter->value_max_integer = $request->input('filter_value_02_input');
                    break;
                case 5:
                    $filter->value_array = $request->input('filter_value_01_array');
                    break;
                case 6:
                    $min = Carbon::parse($request->input('filter_value_01_time'))->format('H:i');
                    $max = Carbon::parse($request->input('filter_value_02_time'))->format('H:i');
                    $filter->value_min_time = $min;
                    $filter->value_max_time = $max;
                    break;

                default:
                    break;
            }

            $filter->save();
            $campaign_filter_group_filters[] = $filter->id;

            //Action Logger: Add Campaign Filter Group Filter
            $this->logger->log(3, 32, $campaign_id, "Add $filter_names[$filter_type] filter in filter group named: $group_names[$filter_group]", null, $filter->toArray(), null, $value_mask);
        }

        return $campaign_filter_group_filters;
    }

    public function editCampaignFilter(CampaignFilterRequest $request)
    {
        $filter_groups = $request->input('filter_group');

        $value_type = $request->input('value_type');
        $filter_type_id = $request->input('filter_type');

        $chosen_filter_id = $request->input('this_id');
        //Get Filter Type of the chosen filter to be edited.
        $current_filter_type_id = CampaignFilterGroupFilter::find($chosen_filter_id)->filter_type_id;

        $listOfAffected = [];

        //for logging
        $filter_names = FilterType::pluck('name', 'id');
        $value_mask = [
            'value_boolean' => ['False', 'True'],
            'filter_type_id' => $filter_names,
        ];
        $campaign_id = CampaignFilterGroup::where('id', $filter_groups[0])->first()->campaign_id;
        $group_names = CampaignFilterGroup::whereIn('id', $filter_groups)->pluck('name', 'id');

        foreach ($filter_groups as $filter_group_id) {
            $checkIfExists = CampaignFilterGroupFilter::where('campaign_filter_group_id', $filter_group_id)
                ->where('filter_type_id', $current_filter_type_id)->count();

            if ($checkIfExists != 0) {
                $filter = CampaignFilterGroupFilter::where('campaign_filter_group_id', $filter_group_id)
                    ->where('filter_type_id', $current_filter_type_id)->first();

                $current_state = $filter->toArray(); //For Logging

                $filter->filter_type_id = $filter_type_id;
                $filter->value_text = null;
                $filter->value_boolean = null;
                $filter->value_min_date = null;
                $filter->value_max_date = null;
                $filter->value_min_integer = null;
                $filter->value_max_integer = null;
                $filter->value_array = null;
                $filter->value_min_time = null;
                $filter->value_max_time = null;
                switch ($value_type) {
                    case 1:
                        $filter->value_text = $request->input('filter_value_01_text');
                        break;
                    case 2:
                        $filter->value_boolean = $request->input('filter_value_01_select');
                        break;
                    case 3:
                        $filter->value_min_date = $request->input('filter_value_01_date');
                        $filter->value_max_date = $request->input('filter_value_02_date');
                        break;
                    case 4:
                        $filter->value_min_integer = $request->input('filter_value_01_input');
                        $filter->value_max_integer = $request->input('filter_value_02_input');
                        break;
                    case 5:
                        $filter->value_array = $request->input('filter_value_01_array');
                        break;
                    case 6:
                        $min = Carbon::parse($request->input('filter_value_01_time'))->format('H:i');
                        $max = Carbon::parse($request->input('filter_value_02_time'))->format('H:i');
                        $filter->value_min_time = $min;
                        $filter->value_max_time = $max;
                        break;
                    default:
                        break;
                }
                $filter->save();

                //Action Logger: Edit Campaign Filter Group Filter
                $ft_id = $filter->filter_type_id;
                $this->logger->log(3, 32, $campaign_id, "Update $filter_names[$ft_id] filter in filter group named: $group_names[$filter_group_id]", $current_state, $filter->toArray(), null, $value_mask);

                $listOfAffected[] = $filter->id;
            }
        }

        return $listOfAffected;
    }

    public function getCampaignFilters(Request $request)
    {
        $campaign = $request->input('id');

        $cFilters = CampaignFilter::where('campaign_id', $campaign)->get();
        if (count($cFilters) > 0) {
            $c = 0;
            foreach ($cFilters as $cFilter) {
                $filters[$c]['id'] = $cFilter->id;
                $filters[$c]['filter_type_id'] = $cFilter->filter_type_id;
                $filters[$c]['filter_name'] = $cFilter->filter_type->type.' - '.$cFilter->filter_type->name;
                if (! is_null($cFilter->value_text) || $cFilter->value_text != '') {
                    $filters[$c]['value_type'] = 1;
                    $filters[$c]['value_value'] = $cFilter->value_text;
                } elseif (! is_null($cFilter->value_boolean) || $cFilter->value_boolean != '') {
                    $filters[$c]['value_type'] = 2;
                    $filters[$c]['value_value'] = $cFilter->value_boolean == 1 ? 'True' : 'False';
                } elseif (! is_null($cFilter->value_min_date) || $cFilter->value_min_date != '' || ! is_null($cFilter->value_max_date) || $cFilter->value_max_date != '') {
                    $filters[$c]['value_type'] = 3;
                    $filters[$c]['value_value'] = $cFilter->value_min_date.' - '.$cFilter->value_max_date;
                } elseif (! is_null($cFilter->value_min_integer) || $cFilter->value_min_integer != '' || ! is_null($cFilter->value_max_integer) || $cFilter->value_max_integer != '') {
                    $filters[$c]['value_type'] = 4;
                    $filters[$c]['value_value'] = $cFilter->value_min_integer.' - '.$cFilter->value_max_integer;
                } elseif (! is_null($cFilter->value_array) || $cFilter->value_array != '') {
                    $filters[$c]['value_type'] = 5;
                    $filters[$c]['value_value'] = $cFilter->value_array;
                } else {
                    $filters[$c]['value_type'] = 0;
                    $filters[$c]['value_value'] = '';
                }

                $filters[$c]['value_text'] = $cFilter->value_text;
                $filters[$c]['value_min_integer'] = $cFilter->value_min_integer;
                $filters[$c]['value_max_integer'] = $cFilter->value_max_integer;
                $filters[$c]['value_min_date'] = $cFilter->value_min_date;
                $filters[$c]['value_max_date'] = $cFilter->value_max_date;
                $filters[$c]['value_boolean'] = $cFilter->value_boolean;

                $c++;
            }
        } else {
            $filters = null;
        }

        return $filters;
    }

    public function deleteCampaignFilter(Request $request)
    {
        $id = $request->input('id');
        $filter = CampaignFilterGroupFilter::find($id);
        $filter_type = $filter->filter_type_id;

        //for logging
        $filter_names = FilterType::where('id', $filter_type)->pluck('name', 'id');
        $value_mask = [
            'value_boolean' => ['False', 'True'],
            'filter_type_id' => $filter_names,
        ];
        $filter_group = CampaignFilterGroup::where('id', $filter->campaign_filter_group_id)->select('campaign_id', 'name')->first();

        //Action Logger: Delete Campaign Filter Group Filter
        $this->logger->log(3, 32, $filter_group->campaign_id, "Delete $filter_names[$filter_type] filter in filter group named: $filter_group->name", $filter->toArray(), null, null, $value_mask);

        $filter->delete();
    }

    /* CAMPAIGN CREATIVES */
    /**
     * Will return campaign's creative combinations that is compatible with data tables server side processing
     */
    public function getCampaignCreative(Request $request): JsonResponse
    {
        $inputs = $request->all();
        // Log::info($inputs);
        $creativesData = [];

        // $start = $inputs['start'];
        // $length = $inputs['length'];

        $columns = [
            0 => 'id',
        ];

        if (isset($inputs['order'])) {
            $order_by = $columns[$inputs['order'][0]['column']];
            $order_dir = $inputs['order'][0]['dir'];
        } else {
            $order_by = 'created_at';
            $order_dir = 'desc';
        }
        $total_count = CampaignCreative::where('campaign_id', $inputs['campaign_id'])->count();
        $creatives = CampaignCreative::where('campaign_id', $inputs['campaign_id'])
            ->orderBy($order_by, $order_dir);

        // if($length>1) $creatives = $creatives->skip($start)->take($length);

        $creatives = $creatives->get();

        foreach ($creatives as $creative) {
            $id = $creative->id;

            $idCol = '<span>'.$id.'</span><div style="margin-bottom: 50px;"></div>';

            $idCol .= '<input id="cmpCrtv-'.$id.'-weight" name="weight" value="'.$creative->weight.'" class="form-control spinner cmpCreativeField" data-id="'.$id.'">';

            if (is_null($creative->image) || $creative->image == '') {
                $image = url('images/img_unavailable.jpg');
            } else {
                $image = $creative->image;
            }

            $imageCol =
                '<div><div class="row">
                    <div class="col-md-12">
                        <div class="creativeIconPreview">
                            <img id="cmpCrtv-'.$id.'-img-preview" src="'.$creative->image.'"/>
                        </div>
                    </div>
                    <div id="cmpCrtv-'.$id.'-imgLinkDiv" class="col-md-12 form_div" style="display:none">
                        <label for="image">Image URL:</label>
                        <input class="form-control this_field cmpCreativeField campaignCreativeImageLink" name="image" type="text" id="cmpCrtv-'.$id.'-img" data-id="'.$id.'" value="'.$creative->image.'" style="width:100%">
                    </div>
                </div></div>';
            $previewLink = config('constants.LEAD_REACTOR_PATH').'dynamic_live/preview_stack.php?id='.$inputs['campaign_id'].'&creative='.$id;
            $actionCol = '<button id="cmpCrtv-'.$id.'-edit-button" class="btn btn-default editCampaignCreative" type="button" data-id="'.$id.'" data-type="edit"><span class="glyphicon glyphicon-pencil"></span></button>
                            <button id="cmpCrtv-'.$id.'-save-button" class="btn btn-primary saveCampaignCreative" type="button" data-id="'.$id.'" style="display:none"><i class="fa fa-floppy-o" aria-hidden="true"></i></button>
                            <a id="cmpCrtv-'.$id.'-preview-button" href="'.$previewLink.'" target="_blank" class="previewCampaignCreative btn btn-default pull-right" data-id="'.$id.'"><span class="glyphicon glyphicon-eye-open"></span></a>
                            <button id="cmpCrtv-'.$id.'-delete-button" class="btn btn-default deleteCampaignCreative" type="button" data-id="'.$id.'"><span class="glyphicon glyphicon-trash"></span></button>';
            $hiddenDataCol = '<input type="hidden" id="cmpCrtv-'.$id.'-weight-original" name="original_weight" value="'.$creative->weight.'">
                              <textarea id="cmpCrtv-'.$id.'-desc-original" class="hidden" name="original_description">'.$creative->description.'</textarea>
                              <input type="hidden" id="cmpCrtv-'.$id.'-img-original" name="original_image" value="'.$creative->image.'">';
            $data = [
                $idCol,
                '<textarea id="cmpCrtv-'.$id.'-desc" class="stackCreativeDesc form-control this_field cmpCreativeField" required="true" name="cmpCrtv-'.$id.'-desc">'.$creative->description.'</textarea>',
                $imageCol,
                $actionCol.$hiddenDataCol,
            ];
            array_push($creativesData, $data);
        }

        // Log::info($creativesData);

        $responseData = [
            'draw' => intval($inputs['draw']),   // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            'recordsTotal' => $total_count,  // total number of records
            'recordsFiltered' => count($creatives), // total number of records after searching, if there is no searching then totalFiltered = totalData
            'data' => $creativesData,
            'canEdit' => $this->canEdit,
            'canDelete' => $this->canDelete,
            'canAdd' => $total_count < 4 ? true : false,
        ];

        return response()->json($responseData, 200);
    }

    public function updateCampaignCreative(Request $request)
    {
        // Log::info($request->all());
        $creative = CampaignCreative::find($request->input('id'));

        //for logging
        if ($creative->image == '' && $creative->description == '') {
            $current_state = null;
            $log_action = 'Add';
        } else {
            $current_state = $creative->toArray();
            $log_action = 'Update';
        }

        $creative->weight = $request->input('weight');
        $creative->description = $request->input('description');
        $creative->image = $request->input('image');
        $creative->save();

        //Action Logger: Add/Update Campaign Creative
        $this->logger->log(3, 371, $creative->campaign_id, "$log_action creative id: $creative->id", $current_state, $creative->toArray(), null, null);

        return $request->all();
    }

    public function addCampaignCreative(Request $request)
    {
        $campaign_id = $request->input('id');
        $creative = CampaignCreative::firstOrCreate([
            'campaign_id' => $campaign_id,
            'weight' => 0,
            'image' => '',
            'description' => '',
        ]);
    }

    public function deleteCampaignCreative(Request $request)
    {
        $creative = CampaignCreative::find($request->input('id'));

        //Action Logger: Delete Campaign Creative
        $this->logger->log(3, 371, $creative->campaign_id, "Delete creative id: $creative->id", $creative->toArray(), null, null, null);

        $creative->delete();

        return 0;
    }

    public function uploadCampaignPayout(Request $request): JsonResponse
    {
        $file = $request->file('file');

        $originalExtension = $file->getClientOriginalExtension();
        $originalFilename = '';

        $responseData = [
            'status' => 'upload_successful',
            'message' => 'Campaign payout uploaded successfully!',
        ];

        if ($originalExtension == 'xls' || $originalExtension == 'xlsx' || $originalExtension == 'csv') {
            if ($originalExtension == 'xls') {
                $originalFilename = basename($file->getClientOriginalName(), '.xls');
            } elseif ($originalExtension == 'xlsx') {
                $originalFilename = basename($file->getClientOriginalName(), '.xlsx');
            } elseif ($originalExtension == 'csv') {
                $originalFilename = basename($file->getClientOriginalName(), '.csv');
            }

            $fullFilename = $originalFilename.'_'.Carbon::today()->toDateString().'.'.$originalExtension;

            if (Storage::exists('uploads/'.$fullFilename)) {
                //delete the old and replace with new
                Storage::delete('uploads/'.$fullFilename);
            }

            //save the file to local storage
            Storage::disk('local')->put($fullFilename, File::get($file));

            //copy the uploaded file to uploads
            Storage::move($fullFilename, 'uploads/'.$fullFilename);

            Log::info("Processing file $fullFilename for campaign update upload.");

            //get the file path of spreadsheet file to be read
            $filePath = storage_path('app/uploads').'/'.$fullFilename;

            try {
                $results = Excel::load($filePath)->get();
                $job = (new UpdateCampaignPayoutJob($filePath));
                dispatch($job);

                $responseData = [
                    'status' => 'upload_successful',
                    'message' => 'Campaign payout uploaded successfully! We will email you once it is done.',
                ];

            } catch (\Exception $ex) {
                Log::info('EXCEL ERROR FOUND1');
                Log::info($ex);
                $responseData = [
                    'status' => 'file_problem',
                    'message' => 'There was an error encountered in the file.',
                    'details' => $ex,
                ];
            } catch (\Error $er) {
                Log::info('EXCEL ERROR FOUND2');
                Log::info($er);
                $responseData = [
                    'status' => 'file_problem',
                    'message' => 'There was an error encountered in the file.',
                    'details' => $er,
                ];
            } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
                Log::info('EXCEL ERROR FOUND3');
                Log::info($e);
                $responseData = [
                    'status' => 'file_problem',
                    'message' => 'There was an error encountered in the file.',
                    'details' => $e,
                ];
            }
        } else {
            $responseData = [
                'status' => 'file_problem',
                'message' => 'Invalid file format or extension!',
            ];
        }

        return response()->json($responseData, 200);
    }

    public function getCampaignContentFormBuilder(Request $request): JsonResponse
    {
        $id = $request->input('id');
        $type = $request->input('type');
        $content = CampaignContent::find($id);
        $response = [];
        $response['lock'] = $content->stack_lock;
        $response['content'] = $content->stack;
        $content_stack = $content->stack;

        //remove comments
        //php comments
        // $content_stack = preg_replace('!/\*.*?\*/!s', '', $content_stack);
        // $content_stack = preg_replace('/\n\s*\n/', "", $content_stack);
        // $content_stack = preg_replace('#^\s*//.+$#m', "", $content_stack);
        //html comments
        // $content_stack = preg_replace('/<!--(.|\s)*?-->/', '', $content_stack);
        $content_stack = preg_replace('~<!--(.*?)-->~s', '', $content_stack);
        $content_stack = str_replace('-->', '', $content_stack);
        $content_stack = str_replace('<--', '', $content_stack);

        if (strpos($content->stack, '<?php') !== false) {
            if ($content->stack_lock == 0) {
                $content->stack_lock = 1;
                $content->save();
                $response['auto_lock'] = true;
                $response['lock'] = $content->stack_lock;
            }
            // $content_stack = preg_replace('!/\*.*?\*/!s', '', $content_stack);
            // $content_stack = preg_replace('/\n\s*\n/', "", $content_stack);
            // $content_stack = preg_replace('/<!--(.|\s)*?-->/', '', $content_stack);
            ob_start();
            eval('?>'.$content_stack);
            $content_stack = ob_get_contents();
            ob_end_clean();
        }

        if ($content_stack != null || $content_stack != '') {
            // create new DOMDocument
            $document = new \DOMDocument('1.0', 'UTF-8');

            // set error level
            $internalErrors = libxml_use_internal_errors(true);

            // load HTML
            $document->loadHTML($content_stack);

            // Restore error level
            libxml_use_internal_errors($internalErrors);

            $xpath = new \DOMXpath($document);

            // Form
            $form_qry = $xpath->query('//form');
            $this_form = $form_qry->item(0);

            $form_action = $this_form->getAttribute('action');
            if (strpos($form_action, 'leadreactor.engageiq.com/sendLead') !== false) {
                $form_action = 'lead_reactor';
            } elseif (strpos($form_action, 'leadfilter.engageiq.com/storeLead') !== false) {
                $form_action = 'lead_filter';
            }

            $response['form'] = [
                'action' => $form_action,
                'id' => $this_form->getAttribute('id'),
                'class' => trim($this->string_replace_this('stack_survey_form', '', $this_form->getAttribute('class'))),
            ];

            //LinkOut
            if ($type == 5) {
                $linkout_qry = $xpath->query("//*[contains(@class, 'pop_up_stack')]");
                if ($linkout_qry->length == 0) {
                    $redirect_link = '';
                } else {
                    $lk_yes_btn = $linkout_qry->item(0);
                    $redirect_link = $lk_yes_btn->getAttribute('data-url');
                }
                $response['form']['redirect_link'] = $redirect_link;
            }
            // Log::info($response);

            //Labels
            $get_labels = $document->getElementsByTagName('label');
            $labels = [];
            $gLabels = [];
            $counter = 0;
            foreach ($get_labels as $label) {
                if (! isset($labels[$label->getAttribute('for')])) {
                    $labels[$label->getAttribute('for')] = trim($label->nodeValue);
                }

                if ($label->nodeValue != '') {
                    $gLabels[$label->getAttribute('for')][] = trim($this->innerXML($get_labels->item($counter)));
                }
                // Log::info($label->ownerDocument->saveXML($label));
                // Log::info($document->saveHTML($label));
                // Log::info($document->saveHTML($label));
                // Log::info(trim($this->innerXML($get_labels->item($counter))));
                // Log::info('-------');

                $counter++;
            }
            // Log::info($labels);
            // Log::info($gLabels);

            //Fields
            $standard_fields = array_keys(config('constants.STANDARD_REQUIRED_FIELDS'));
            $field_names = [];
            $col = $xpath->query('//input|//textarea|//select|//article');
            if (is_object($col)) {
                foreach ($col as $node) {
                    // $classes = explode(' ', $node->getAttribute('class'));

                    $name = $node->getAttribute('name');
                    $value = $node->getAttribute('value');
                    $type = '';
                    if ($node->tagName == 'select') {
                        $type = 'dropdown';
                    } elseif ($node->tagName == 'textarea') {
                        $type = 'textbox';
                    } elseif ($node->tagName == 'article') {
                        $type = 'article';
                    } else {
                        $input_type = $node->getAttribute('type');
                        // $type = $input_type;
                        if ($input_type == 'hidden') {
                            $type = 'hidden';
                        } elseif ($input_type == 'text') {
                            $type = 'text';
                        } elseif ($input_type == 'radio') {
                            $type = 'radio';
                        } elseif ($input_type == 'checkbox') {
                            $type = 'checkbox';
                        }
                    }

                    if ($type == 'radio' && ($value == 'YES' || $value == 'NO') && strpos($name, '-campaign') >= 0) {
                        //Yes & No Button
                    } else {
                        if (($type == 'radio' || $type == 'checkbox') && in_array($name, $field_names)) {
                            // radio/checkbox field already exists
                            $field_key = array_search($name, $field_names);

                            $response['fields'][$field_key]['options']['values'][] = $value;

                            if ($node->hasAttribute('accepted')) {
                                $response['fields'][$field_key]['has_accepts'] = true;
                                $response['fields'][$field_key]['accepted_options'][] = $value;
                            }

                            if ($node->hasAttribute('checked')) {
                                $response['fields'][$field_key]['value'] = $value;
                            }

                            if ($response['fields'][$field_key]['label'] == '') {
                                $nextCount = count($response['fields'][$field_key]['options']['values']) - 1;
                            } else {
                                $nextCount = count($response['fields'][$field_key]['options']['values']);
                            }

                            $response['fields'][$field_key]['options']['displays'][] = isset($gLabels[$name][$nextCount]) ? $gLabels[$name][$nextCount] : '';

                            // Log::info($response['fields'][$field_key]);
                            // Log::info($nextCount);
                            // Log::info(isset($gLabels[$name][$nextCount]) ? $gLabels[$name][$nextCount] : '');
                        } elseif ($type == 'article') {
                            // Log::info('ARTICLE');
                            // Log::info(trim($this->innerXML($node)));
                            // Log::info($node->ownerDocument->saveXML($node));
                            $response['fields'][] = [
                                'label' => '',
                                'name' => '',
                                'type' => $type,
                                'id' => $node->getAttribute('id'),
                                'class' => $node->getAttribute('class'),
                                'value' => trim($this->innerXML($node)),
                            ];

                            $field_names[] = $type;
                        } else {

                            if ($type == 'radio' || $type == 'checkbox') {
                                $value = '';
                            }

                            $field = [
                                'name' => $name,
                                'type' => $type,
                                'value' => $value,
                                'label' => isset($labels[$name]) ? $labels[$name] : '',
                                'id' => $node->getAttribute('id'),
                                'class' => $node->getAttribute('class'),
                                // 'accepted' => $node->hasAttribute('accepted') ? true : false,
                                'standard' => in_array($name, $standard_fields) ? true : false,
                            ];

                            $field['validation']['required'] = $node->hasAttribute('required') ? true : false;

                            // Log::info($field);
                            if ($node->tagName == 'select') {
                                $option_values = [];
                                $option_displays = [];
                                $has_accepts = false;
                                $accepted_options = [];
                                $children = $node->childNodes;
                                foreach ($children as $child) {
                                    // Log::info($child->getAttribute('value'));
                                    $child_value = $child->hasAttribute('value') ? $child->getAttribute('value') : '';

                                    $option_values[] = $child_value;
                                    $option_displays[] = $child->nodeValue;
                                    if ($child->hasAttribute('accepted')) {

                                        $has_accepts = true;
                                        $accepted_options[] = $child_value;
                                    }

                                    if ($child->hasAttribute('selected')) {
                                        $field['value'] = $child_value;
                                    }
                                }

                                $field['options']['values'] = $option_values;
                                $field['options']['displays'] = $option_displays;
                                $field['has_accepts'] = $has_accepts;
                                $field['accepted_options'] = $accepted_options;
                                // Log::info($field);
                            }

                            if ($type == 'radio' || $type == 'checkbox') {
                                $this_value = $node->getAttribute('value');
                                $has_accepts = false;
                                $accepted_options = [];

                                if ($node->hasAttribute('accepted')) {
                                    $has_accepts = true;
                                    $accepted_options[] = $this_value;
                                }

                                if ($node->hasAttribute('checked')) {
                                    $field['value'] = $this_value;
                                }

                                $field['options']['values'][] = $this_value;
                                $field['has_accepts'] = $has_accepts;
                                $field['accepted_options'] = $accepted_options;

                                if ($field['label'] == '') {
                                    $nextCount = 0;
                                } else {
                                    $nextCount = isset($field['options']['values']) ? 1 : count($field['options']['values']) + 1;
                                }

                                $field['options']['displays'][] = isset($gLabels[$name][$nextCount]) ? $gLabels[$name][$nextCount] : '';
                            }

                            $field_names[] = $name;
                            $response['fields'][] = $field;
                            // Log::info($field);
                        }

                    }
                }
            }
            //CSS & JS
            $js_query = $xpath->query('//script|//noscript');
            if (is_object($js_query)) {
                $custom_js = '';
                // $script = $js_query->item(0);
                // Log::info($document->saveXML($script));
                foreach ($js_query as $script) {
                    // $custom_js .= $script->nodeValue."\n";

                    $custom_js .= $document->saveHTML($script)."\n";
                }
            }
            $response['custom']['js'] = is_object($js_query) ? trim($custom_js) : '';

            $css_query = $xpath->query('//style');
            if (is_object($css_query)) {
                $custom_css = '';
                foreach ($css_query as $style) {
                    $custom_css .= $style->nodeValue."\n";
                }
            }
            $response['custom']['css'] = is_object($js_query) ? trim($custom_css) : '';

            // $match_string = '/\$\(\"#'.$response['form']['id'].'\"\)\.validate\(\{([\S\s]*)?rules ?: ?{[A-Za-z0-9_\s\S]*\}([\S\s]*)?\}\);/';
            // $match_string = '/\$\(\"#'.$response['form']['id'].'\"\)\.validate\(\{([A-Za-z0-9_\s\S]*)\}\);/';
            // preg_match($match_string, $response['custom']['js'], $match);
            // if(isset($match)) Log::info($match);

            //Jquery Validation
            // $cleaned_js = $string = str_replace(' ', '', $response['custom']['js']);
            $cleaned_js = preg_replace('/\s+/', '', $response['custom']['js']);
            $jquery_validate = $this->getStringBetween($cleaned_js, '$("#'.$response['form']['id'].'").validate({rules:{', '}});');
            if ($jquery_validate != '') {
                // $rules = $this->getStringBetween(trim($jquery_validate), '$("#'.$response['form']['id'].'").validate({', '});');
                $field_rules = explode('},', $jquery_validate);
                // Log::info($field_rules);
                // Log::info($field_names);
                foreach ($field_rules as $field) {
                    if ($field != '') {
                        // Log::info($field);
                        $field_rules = explode(':{', $field);
                        // Log::info($field_rules);
                        // Log::info($field_rules[0]);
                        $key = array_search(trim($field_rules[0], "'"), $field_names);
                        if ($key != false) {
                            $rules = explode(',', $field_rules[1]);
                            // Log::info($field_rules[0]); //name
                            // Log::info($field_rules[1]);
                            // Log::info($rules);
                            // Log::info('-------------');

                            $c = 0;
                            foreach ($rules as $rule) {
                                $r = explode(':', $rule);
                                if (isset($r[0], $r[1])) {
                                    $field_name = $r[0];
                                    $value = $r[1];

                                    if ($field_name == 'equalTo' || $field_name == 'minWordCount') {
                                        $value = trim($value, '"');
                                    }
                                    $response['fields'][$key]['validation'][$field_name] = $value;
                                } else {
                                    if (isset($field_name)) {
                                        if ($field_name == 'range' && $r[0] != '') {
                                            $response['fields'][$key]['validation'][$field_name] .= ','.$r[0];
                                        }
                                    }

                                    if (isset($r[0]) && $r[0] == 'remote') {
                                        if (strpos($field_rules[2], 'zip_checker')) {
                                            $response['fields'][$key]['validation']['zip'] = true;
                                        }
                                    }
                                    // Log::info($r);
                                    // Log::info($key);
                                    // Log::info($rules[$c-1]);
                                    // Log::info($field_name);
                                    // Log::info($response['fields'][$key]['validation'][$field_name]);
                                }

                                $c++;
                            }
                        }
                    }

                }
            }
            // Log::info($cleaned_js);
            // Log::info($jquery_validate);
        }
        // Log::info($response['fields']);
        // Log::info($response);
        return response()->json($response, 200);
    }

    public function formCampaignContent(Request $request)
    {
        $inputs = $request->all();
        // Log::info($inputs);
        // Log::info('<------------------------------------------------------------------->');
        $campaign_id = $inputs['id'];
        $campaign_type = $inputs['type'];

        if ($campaign_type == 5) { //linkout
            $template = Storage::get('form_builder_linkout_template.php');
            $template = $this->string_replace_this('[FB_CPA_LINK]', $inputs['form']['linkout_url'], $template);
        } else {
            $template = Storage::get('form_builder_coreg_template.php');
            $action_url = [
                'lead_reactor' => 'http://leadreactor.engageiq.com/sendLead/',
                'lead_filter' => 'http://leadfilter.engageiq.com/storeLead/',
                'custom' => $inputs['form']['custom_url'],
            ];

            $template = $this->string_replace_this('[FB_FACTION]', $action_url[$inputs['form']['url']], $template);
        }

        /* Form */
        $form_id = $inputs['form']['id'] == '' ? 'cmp-'.$campaign_id.'-form' : $inputs['form']['id'];
        $template = $this->string_replace_this('[FB_FID]', $form_id, $template);
        $form_id_others = str_replace('-form', '', $form_id);
        $template = $this->string_replace_this('[FB_FID_O]', $form_id_others, $template);
        $template = $this->string_replace_this('[FB_FCLASS]', $inputs['form']['class'], $template);

        /* Custom */
        $css = $inputs['custom']['css'] != '' ? '<style>'.$inputs['custom']['css'].'</style>' : '';
        $template = $this->string_replace_this('[FB_CUSTOM_CSS]', $css, $template);
        $js = $inputs['custom']['js'] != '' ? $inputs['custom']['js'] : '';

        /* Fields */
        $template = $this->string_replace_this('[VALUE_CAMPAIGN_ID]', $campaign_id, $template);
        $hasHidden = false;
        $hasAdditional = false;
        $hasAcceptValidation = false;
        $hidden_fields = '';
        $additional_fields = '';
        // $standard_fields = config('constants.STANDARD_REQUIRED_FIELDS');
        $hasAdditionalFields = false;
        $hasAddtFieldsNotArticle = false;
        $hasMoreValidations = false;
        $validation_rules = '';

        if (isset($inputs['fields'])) {
            foreach ($inputs['fields'] as $field) {
                $type = $field['type'];
                $ifStandard = isset($field['standard']) ? ($field['standard'] == 'true' ? true : false) : false;
                $name = $field['name']; //Log::info($name);
                $value = $field['value'];
                $label = $field['label'];
                $id_attr = $field['id'] != '' ? ' id="'.$field['id'].'"' : '';
                $class_attr = $field['class'] != '' ? ' class="'.$field['class'].'"' : '';
                $ifRequired = isset($field['validation']['required']) ? ($field['validation']['required'] == 'true' ? true : false) : false;
                $required_attr = $ifRequired ? ' required ' : '';
                $hasAccepts = isset($field['has_accepts']) ? ($field['has_accepts'] == 'true' ? true : false) : false;
                $field_accept_string = '';
                if ($hasAccepts == 'true' && $hasAccepts == true) {
                    $hasAcceptValidation = true;
                    $field_accept_string = ' data-check="true" ';
                }

                if ($type != 'hidden' && $hasAdditionalFields == false) {
                    $hasAdditionalFields = true;
                }

                if ($type != 'article' && $type != 'hidden'
                    && $hasAddtFieldsNotArticle == false) {
                    $hasAddtFieldsNotArticle = true;
                }

                // if($ifStandard == false) Log::info($name.' - not standard');
                // else Log::info($name);

                if ($type == 'hidden') {
                    if ($ifStandard == false) {
                        $hasHidden = true;
                        $hidden_element = '<input type="hidden" name="'.$name.'" value="'.$value.'"'.$id_attr.$class_attr.'/>';
                        if ($label == '') {
                            $hidden_fields .= $hidden_element."\n";
                        } else {
                            // $label_element = $label != '' ? '<label for="'.$name.'"></label></br>'."\n" : '';
                            // $additional_fields .= $label_element.$hidden_element;
                            $additional_fields .= $this->form_builder_add_table_row($name, $label, $hidden_element);
                        }
                    }
                } elseif ($type == 'text') {
                    $text_element = '<input type="text" name="'.$name.'" value="'.$value.'"'.$id_attr.$class_attr.$required_attr.'/>';
                    $additional_fields .= $this->form_builder_add_table_row($name, $label, $text_element);
                } elseif ($type == 'textbox') {
                    $textbox_element = '<textarea name="'.$name.'"'.$id_attr.$class_attr.$required_attr.'>'.$value.'</textarea>';
                    $additional_fields .= $this->form_builder_add_table_row($name, $label, $textbox_element);
                } elseif ($type == 'dropdown') {
                    $select_element = '<select name="'.$name.'" '.$id_attr.$class_attr.$required_attr.$field_accept_string.'>'."\n";
                    $select_element .= "\t\t\t".'<option value="">Select</option>'."\n";
                    if (isset($field['options']['values'])) {
                        foreach ($field['options']['values'] as $key => $option) {
                            if ($option != '') {
                                $ifSelected = $option == $value ? 'selected' : '';
                                $ifAccepted = '';
                                if ($hasAccepts) {
                                    if (isset($field['accepted_options'])) {
                                        $ifAccepted = in_array($option, $field['accepted_options']) ? ' accepted' : '';
                                    }
                                }
                                $select_element .= "\t\t\t".'<option value="'.$option.'" '.$ifSelected.$ifAccepted.'>'.$field['options']['displays'][$key].'</option>'."\n";
                            }
                        }
                    }
                    $select_element .= "\t\t".'</select>';
                    $additional_fields .= $this->form_builder_add_table_row($name, $label, $select_element);
                } elseif ($type == 'radio') {
                    $radio_elements = '';
                    if (isset($field['options']['values'])) {
                        $r_count = 1;
                        foreach ($field['options']['values'] as $key => $radio) {
                            if ($radio != '') {
                                $ifSelected = $radio == $value ? ' checked ' : '';
                                $ifAccepted = '';
                                if ($hasAccepts) {
                                    if (isset($field['accepted_options'])) {
                                        $ifAccepted = in_array($radio, $field['accepted_options']) ? ' accepted' : '';
                                    }
                                }
                                $radio_display = $field['options']['displays'][$key] != '' ? $field['options']['displays'][$key] : $radio;

                                $radio_elements .= "\t\t".'<input type="radio" name="'.$name.'" value="'.$radio.'"'.$id_attr.$class_attr.$required_attr.$ifAccepted.$field_accept_string.$ifSelected.'/>'."\n";
                                $radio_elements .= "\t\t".'<label for="'.$name.'" class="fld-val-'.$r_count++.'">'.$radio_display.'</label><br />'."\n";
                            }
                        }
                        $additional_fields .= $this->form_builder_add_table_row($name, $label, $radio_elements);
                    }
                } elseif ($type == 'checkbox') {
                    $check_elements = '';
                    if (isset($field['options']['values'])) {
                        $ch_count = 1;
                        foreach ($field['options']['values'] as $key => $box) {
                            if ($box != '') {
                                $ifSelected = $box == $value ? ' checked ' : '';
                                $ifAccepted = '';
                                if ($hasAccepts) {
                                    if (isset($field['accepted_options'])) {
                                        $ifAccepted = in_array($box, $field['accepted_options']) ? ' accepted' : '';
                                    }
                                }
                                $box_display = $field['options']['displays'][$key] != '' ? $field['options']['displays'][$key] : $box;

                                $check_elements .= "\t\t".'<input type="checkbox" name="'.$name.'" value="'.$box.'"'.$id_attr.$class_attr.$required_attr.$ifAccepted.$field_accept_string.$ifSelected.'/>'."\n";
                                $check_elements .= "\t\t".'<label for="'.$name.'" class="fld-val-'.$ch_count++.'">'.$box_display.'</label><br />'."\n";
                            }
                        }
                        $additional_fields .= $this->form_builder_add_table_row($name, $label, $check_elements);
                    }
                } elseif ($type == 'article') {
                    $article_element = '<article'.$id_attr.$class_attr.'>'.$value.'</article>';
                    $additional_fields .= $this->form_builder_add_article_table_row($article_element);
                }

                if ($type != 'hidden' && isset($field['validation'])) {
                    // $val_count = count($field['validation']);
                    // if(in_array('required', $field['validation']) && $val_count > 1) $hasMoreValidations = true;
                    // else if(! in_array('required', $field['validation'])) $hasMoreValidations = true;

                    $val_name = $name;
                    if (strpos($name, '[') != false || strpos($name, ']') != false) {
                        $val_name = "'$name'";
                    }
                    $temp_rules = "\t$val_name: {\n";
                    $this_has_rules = false;
                    foreach ($field['validation'] as $rule => $rval) {
                        if ($rule != 'required' || (strpos($name, '[') != false || strpos($name, ']') != false)) {
                            if ($rval != 'false') {
                                $this_has_rules = true;
                                $hasMoreValidations = true;
                                if ($rule == 'zip') {
                                    $temp_rules .= "\t\tremote : {
            url: $(\"meta[name='lrUrl']\").attr('content') + 'zip_checker',
            dataType: 'jsonp',
            data : { zip : function() { return $('#$form_id input[name=\"$name\"]').val(); }}
        },\n";
                                } else {
                                    $temp_rules .= "\t\t$rule : $rval,\n";
                                }
                            }
                        }
                    }
                    $temp_rules .= "\t},\n";
                    if ($this_has_rules) {
                        $validation_rules .= $temp_rules;
                    }
                }
            }
        }
        // Log::info($validation_rules);

        // Log::info($hidden_fields);
        // Custom Table
        $custom_table_start = '';
        $custom_table_end = '';
        $custom_table_input_field_preface = '';
        if ($hasAdditionalFields) {
            $custom_table_start = "\n".'<br/><br/>'."\n".
            '<table id="custom_questions" width="100%" border="0" cellspacing="0" cellpadding="0" style="display:none;">';
            $custom_table_end = "\n".'</table>';

            if ($hasAddtFieldsNotArticle) {
                $custom_table_input_field_preface = "\n".'<tr><td height="28" colspan="3" align="left" bgcolor="#CCCCCC"><strong>Please fill out the form below:</strong></td></tr>'."\n";
            }
        }
        $template = $this->string_replace_this('[VALUE_CUSTOM_TABLE_START]', $custom_table_start, $template);
        $template = $this->string_replace_this('[VALUE_CUSTOM_TABLE_END]', $custom_table_end, $template);
        $template = $this->string_replace_this('[VALUE_CUSTOM_FILL]', $custom_table_input_field_preface, $template);

        $template = $this->string_replace_this('[FB_HIDDEN_FIELDS]', $hidden_fields, $template);
        $template = $this->string_replace_this('[FB_ADDITIONAL_FIELDS]', $additional_fields, $template);

        $additional_fields_yes_btn = $hasAdditionalFields ? 'show_custom_questions' : '';
        $additional_fields_no_btn = $hasAdditionalFields ? 'hide_custom_questions' : '';

        $template = $this->string_replace_this('[FB_HAS_ADDTL_FIELDS_YES_BTN]', $additional_fields_yes_btn, $template);
        $template = $this->string_replace_this('[FB_HAS_ADDTL_FIELDS_NO_BTN]', $additional_fields_no_btn, $template);

        $form_validation = $hasAcceptValidation ? 'data-accept="false"' : '';
        $template = $this->string_replace_this('[FB_HAS_FORM_VALIDATION]', $form_validation, $template);

        /* JS */
        if ($hasMoreValidations) {
            $validate_start = '$("#'.$form_id.'").validate({';
            $validate_end = '});';
            $val_start_pos = strpos($js, $validate_start);
            if ($val_start_pos >= 0 && $val_start_pos != false) {
                //already exists
                $val_rules = $this->getStringBetween($js, $validate_start, $validate_end);
                $js = substr_replace($js, '[VALIDATION_RULES]', $val_start_pos, strlen($val_rules) + strlen($validate_start) + strlen($validate_end));
            } else {
                //not exists
                //check if document ready exists
                $document_ready = '$(document).ready(function(){';
                $dr_pos = strpos($js, $document_ready);
                if ($dr_pos != false) { //exists
                    // $js = substr_replace($js, $document_ready."\n[VALIDATION_RULES]", $dr_pos, $dr_pos + strlen($document_ready));
                    $js = $this->string_insert($js, $document_ready, "\n[VALIDATION_RULES]");
                } else {
                    $script = '<script>';
                    $script_pos = strpos($js, $script);
                    //check if script exists
                    if ($script_pos != false) { //exists
                        $doc_read_string = $document_ready."\n[VALIDATION_RULES]\n}";
                        $js = substr_replace($js, $doc_read_string, $script_pos, $script_pos + strlen($script) + 1);
                    } else {
                        $js = "<script>\n$(document).ready(function(){\n[VALIDATION_RULES]\n});</script>";
                    }
                }
            }

            $val_rule_string = '';
            if ($validation_rules != '') {
                $val_rule_string = $validate_start."\nrules:{\n".$validation_rules.'}'.$validate_end;
            }
            $js = $this->string_replace_this('[VALIDATION_RULES]', $val_rule_string, $js);
        }
        $template = $this->string_replace_this('[FB_CUSTOM_JS]', $js, $template);
        // $for_fb_js = str_replace('<script>', '', $js);
        // $for_fb_js = str_replace('</script>', '', $for_fb_js);
        // Log::info($js);

        $template = trim($template);
        // Log::info($additional_fields);
        // Log::info($template);

        $content = CampaignContent::find($campaign_id);

        $current_state = $content->toArray(); //for logging

        $content->stack = $template;
        $content->save();

        //FOR JSON
        $json = CampaignJsonContent::firstOrNew([
            'id' => $campaign_id,
        ]);
        if (! $json->exists) {
            $json->script = $inputs['custom']['js'];
            unset($inputs['custom']);
            $json->json = json_encode($inputs);
            $json->save();
        }

        //Action Logger: Add Campaign Stack Form Builder
        $this->logger->log(3, 37, $campaign_id, 'Update stack content via form builder', $current_state, $content->toArray(), ['stack' => 'stack content'], null);

        $response = [
            'stack' => $template,
            'form_id' => $form_id,
            'js' => $js,
        ];

        return $response;
    }

    protected function getStringBetween($string, $start, $finish)
    {
        $string = ' '.$string;
        $position = strpos($string, $start);
        if ($position == 0) {
            return '';
        }
        $position += strlen($start);
        $length = strpos($string, $finish, $position) - $position;

        return substr($string, $position, $length);
    }

    protected function string_replace_this($replace_this, $with_this, $in_this)
    {
        return str_replace($replace_this, $with_this, $in_this);
    }

    protected function form_builder_add_table_row($name, $label, $field)
    {
        if ($label != '') {
            $the_label = '<b>'.$label.'</b>';
            $has_label_br = "\t\t".'<br/>'."\n";
        } else {
            $the_label = '';
            $has_label_br = '';
        }

        return
            '<tr>'."\n".
            "\t".'<td colspan="3" align="left">'."\n".
            "\t\t".'<br/><label for="'.$name.'" class="fld-lbl">'.$the_label.'</label>'."\n".
            $has_label_br.
            "\t\t".$field."\n".
            "\t".'</td>'."\n".
            '</tr>'."\n";
    }

    protected function form_builder_add_article_table_row($field)
    {
        return
            '<tr>'."\n".
            "\t".'<td colspan="3" align="left"><br/>'."\n".
            "\t\t".$field."\n".
            "\t".'</td>'."\n".
            '</tr>'."\n";
    }

    protected function string_insert($str, $search, $insert)
    {
        $index = strpos($str, $search);
        if ($index === false) {
            return $str;
        }

        return substr_replace($str, $search.$insert, $index, strlen($search));
    }

    protected function innerXML($node)
    {
        $doc = $node->ownerDocument;
        $frag = $doc->createDocumentFragment();
        foreach ($node->childNodes as $child) {
            $frag->appendChild($child->cloneNode(true));
        }

        return $doc->saveXML($frag);
    }

    public function updateCampaignStackLockStatus(Request $request)
    {
        $inputs = $request->all();

        $content = CampaignContent::find($inputs['id']);
        $current_state = $content->toArray();
        $content->stack_lock = $inputs['lock'];
        $content->save();

        //Action Logger: Update Campaign Form Builder Lock
        $this->logger->log(3, 37, $inputs['id'], null, $current_state, $content->toArray(), null, ['stack_lock' => ['Disabled', 'Enabled']]);

        return $inputs['lock'];
    }

    public function uploadFieldOptions(Request $request): JsonResponse
    {
        $options = [];
        $counter = 0;
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            foreach (file($request->file('file')) as $line) {
                $x = explode(',', $line);
                if (count($x) > 1) {
                    $options['values'][$counter] = trim($x[0]);
                    $options['displays'][$counter] = trim($x[1]);
                } else {
                    $options['values'][$counter] = trim($x[0]);
                    $options['displays'][$counter] = trim($x[0]);
                }
                $counter++;
            }
        }

        return response()->json($options, 200);
    }

    /* Campaign Affiliate Management */
    public function getCampaignInfoForAffiliateMgmt(Request $request)
    {
        $operation = $request->input('operation');
        $affiliates = $request->input('affiliates');
        $eiq_frame = env('EIQ_IFRAME_ID', 0);

        if ($operation == 1) {
            $campaigns = Campaign::where('campaigns.id', '!=', $eiq_frame)->select('id', 'name', 'campaign_type', 'status')
                ->get()->toArray();
        } else {
            $campaigns = AffiliateCampaign::join('campaigns', 'campaigns.id', '=', 'affiliate_campaign.campaign_id')->whereIn('affiliate_id', $affiliates)
                ->where('affiliate_campaign.campaign_id', '!=', $eiq_frame)
                ->select('campaigns.id', 'name', 'campaign_type', 'status')->get()->toArray();
        }

        $campaign_types = config('constants.CAMPAIGN_TYPES');
        $campaign_status = config('constants.CAMPAIGN_STATUS');

        for ($x = 0; $x < count($campaigns); $x++) {
            $campaigns[$x]['type'] = $campaign_types[$campaigns[$x]['campaign_type']];
            $campaigns[$x]['status'] = $campaign_status[$campaigns[$x]['status']];
        }

        return $campaigns;
    }

    public function campaignAffiliateManagement(Request $request): JsonResponse
    {
        $inputs = $request->all();
        // Log::info($inputs);
        $aff_id = $inputs['affiliate_id'];
        $lead_cap_type = $inputs['lead_cap_type'];
        $lead_cap_value = isset($inputs['lead_cap_value']) ? $inputs['lead_cap_value'] : 0;
        $exp_date = $this->getLinkOutCapExpDate($lead_cap_type);
        $eiq_frame = env('EIQ_IFRAME_ID', 0);

        if (isset($inputs['all_cam_campaigns']) && $inputs['all_cam_campaigns'] == 'ALL') {
            $getCampaigns = Campaign::pluck('campaign_type', 'id')->toArray();
            foreach ($getCampaigns as $c => $t) {
                $campaigns[] = $c;
                $campaign_types[$c] = $t;
            }

        } else {
            $campaigns = $inputs['cam_campaign'];
            $campaign_types = $inputs['cam_campaign_type'];
        }

        if (isset($inputs['cam_op']) && $inputs['cam_op'] == 1) {
            //ADD/EDIT Affiliate
            foreach ($campaigns as $cmp_id) {
                //get row
                $cmp_aff = AffiliateCampaign::firstOrNew([
                    'affiliate_id' => $aff_id,
                    'campaign_id' => $cmp_id,
                ]);

                if ($cmp_aff->exists) {
                    $current_state = $cmp_aff->toArray();
                    $action = 'Update';
                } else {
                    $current_state = null;
                    $action = 'Add';
                }

                // linkout cap
                if ($campaign_types[$cmp_id] == 5 && $cmp_aff->lead_cap_type != $lead_cap_type) {
                    $linkOutCap = LinkOutCount::firstOrNew([
                        'campaign_id' => $cmp_id,
                        'affiliate_id' => $aff_id,
                    ]);
                    if ($lead_cap_type != 0) {
                        $linkOutCap->expiration_date = $exp_date;
                        $linkOutCap->count = 0;
                        $linkOutCap->save();
                    } else {
                        $linkOutCap->delete();
                    }
                }

                $leadCount = LeadCount::where('campaign_id', $cmp_id)->where('affiliate_id', $aff_id)->first();
                if ($leadCount) {
                    if ($cmp_aff->lead_cap_type != $lead_cap_type) {
                        $leadCount->reference_date = Carbon::now()->toDateString();
                        $leadCount->count = 0;
                        $leadCount->save();
                    }
                }

                //save
                $cmp_aff->lead_cap_type = $lead_cap_type;
                $cmp_aff->lead_cap_value = $lead_cap_value;
                $cmp_aff->save();

                //Action Logger: Add/Edit Campaign Affiliate via Campaign Affiliate Management
                $this->logger->log(3, 33, $cmp_id, "$action campaign affiliate id: $aff_id via campaign affiliate management", $current_state, $cmp_aff->toArray(), null, [
                    'lead_cap_type' => config('constants.LEAD_CAP_TYPES'),
                ]);
            }
        } else {
            //REMOVE AFFILIATE

            //for logging
            $aff_camps = AffiliateCampaign::where('affiliate_id', $aff_id)->whereIn('campaign_id', $campaigns)->get();
            foreach ($aff_camps as $aff) {
                //Action Logger: Delete Campaign Affiliate via Campaign Affiliate Management
                $this->logger->log(3, 33, $aff->campaign_id, "Delete campaign affiliate id: $aff_id via campaign affiliate management", $aff->toArray(), null, null, [
                    'lead_cap_type' => config('constants.LEAD_CAP_TYPES'),
                ]);
            }

            //delete multiple
            AffiliateCampaign::where('affiliate_id', $aff_id)->whereIn('campaign_id', $campaigns)->delete();

            LinkOutCount::where('affiliate_id', $aff_id)->whereIn('campaign_id', $campaigns)->delete();

            foreach ($campaigns as $cmp_id) {

                // check affiliate request has allowed
                $affiliate_request = AffiliateCampaignRequest::where('affiliate_id', $aff_id)
                    ->where('campaign_id', $cmp_id)
                    ->where('status', 1)
                    ->first();
                if ($affiliate_request) { //if exists; change status to deactivate;
                    $affiliate_request->status = 0;
                    $affiliate_request->save();
                }
            }
        }

        return response()->json([
            'operation' => isset($inputs['cam_op']) && $inputs['cam_op'] == 1 ? 'add' : 'remove',
        ], 200);
    }

    public function getCampaignInfoForAffiliateMgmtDatatable(Request $request): JsonResponse
    {
        $inputs = $request->all();
        // Log::info($inputs);
        $campaignsData = [];
        $affiliate = $inputs['affiliate'];
        $operation = $inputs['operation'];

        if ($affiliate == '') {
            $totalFiltered = 0;
            $totalRecords = 0;
        } else {
            $param = $inputs['search']['value'];
            $start = $inputs['start'];
            $length = $inputs['length'];

            // $operation = $inputs['operation'];

            $columns = [
                'affiliate_campaign.lead_cap_type',
                'campaigns.id',
                'name',
                'campaign_type',
                'status',
                'affiliate_campaign.lead_cap_type',
            ];

            $campaignTypes = config('constants.CAMPAIGN_TYPES');
            $campaignStatuses = config('constants.CAMPAIGN_STATUS');
            $capTypes = config('constants.LEAD_CAP_TYPES');

            // DB::enableQueryLog();
            $campaigns = Campaign::campaignAffiliateManagementTable($param, $start, $length, $columns[$inputs['order'][0]['column']], $inputs['order'][0]['dir'], $affiliate, $operation)
                ->select('campaigns.id', 'name', 'campaign_type', 'status', 'affiliate_campaign.lead_cap_type', 'affiliate_campaign.lead_cap_value')
                ->get();
            $totalFiltered = Campaign::campaignAffiliateManagementTable($param, null, null, null, null, $affiliate, $operation)->count();
            $totalRecords = $param == '' ? $totalFiltered : Campaign::count();
            // Log::info(DB::getQueryLog());

            if (($start == 0 || $param == 'ALL CAMPAIGNS') && $totalFiltered > 0) {
                array_push($campaignsData, [
                    '<input type="checkbox" name="all_cam_campaigns" id="allCAMcampaigns-chkbx" value="ALL">',
                    '',
                    '<strong>ALL CAMPAIGNS</strong>',
                    '',
                    '',
                    '',
                ]);
            }

            foreach ($campaigns as $c) {
                if ($c->lead_cap_type === null) {
                    $cap_dis = 'Default';
                } else {
                    if ($c->lead_cap_type > 0) {
                        $cap_dis = $c->lead_cap_value.' '.$capTypes[$c->lead_cap_type];
                    } else {
                        $cap_dis = $capTypes[$c->lead_cap_type];
                    }
                }

                array_push($campaignsData, [
                    '<input type="checkbox" name="cam_campaign[]" value="'.$c->id.'"/> <input type="checkbox" name="cam_campaign_type['.$c->id.']" value="'.$c->campaign_type.'" class="hidden cam_cmp_type"/>',
                    $c->id,
                    $c->name,
                    $campaignTypes[$c->campaign_type],
                    $campaignStatuses[$c->status],
                    $cap_dis,
                ]);
            }
        }

        $responseData = [
            'draw' => intval($inputs['draw']),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalFiltered,
            'data' => $campaignsData,
        ];

        return response()->json($responseData, 200);
    }

    public function getCampaignAffiliateDatatable(Request $request): JsonResponse
    {
        $inputs = $request->all();
        // Log::info($inputs);
        $affsData = [];
        $campaign = $inputs['campaign'];
        $totalFiltered = 0;
        $totalRecords = 0;

        if ($campaign != '') {
            $param = $inputs['search']['value'];
            $start = $inputs['start'];
            $length = $inputs['length'];

            $columns = [
                'affiliates.id',
                'affiliates.id',
                'lead_cap_type',
                'lead_cap_value',
            ];

            $capTypes = config('constants.LEAD_CAP_TYPES');

            // DB::enableQueryLog();
            $affiliates = AffiliateCampaign::campaignAffiliateSearch($campaign, false, $param, $start, $length, $columns[$inputs['order'][0]['column']], $inputs['order'][0]['dir'])
                ->select(['affiliate_id', 'company', 'lead_cap_type', 'lead_cap_value'])
                ->get();
            $totalFiltered = AffiliateCampaign::campaignAffiliateSearch($campaign, true, $param, null, null, null, null)->count();
            $totalRecords = $param == '' ? $totalFiltered : AffiliateCampaign::where('campaign_id', $campaign)->count();
            // Log::info(DB::getQueryLog());

            // if(($start == 0 || $param == 'ALL AFFILIATES') && $totalFiltered > 0) {
            //     array_push($affsData,[
            //         '<input type="checkbox" name="all_cam_affiliates" id="allCAMaffiliates-chkbx" value="ALL">',
            //         '<strong>ALL AFFILIATES</strong>',
            //         '',
            //         '',
            //         '',
            //         ''
            //     ]);
            // }

            foreach ($affiliates as $f) {
                $deets = '<input name="select_affiliate[]" class="selectCampaignAffiliate" value="'.$f->id.'" data-name="'.$f->company.'" type="checkbox"><input type="hidden" name="ca-'.$f->id.'-deets" value="'.json_encode($f).'"/>';
                array_push($affsData, [
                    $deets,
                    $f->id.' - '.$f->company,
                    '<span id="ca-'.$f->id.'-type" data-id="'.$f->lead_cap_type.'">'.$capTypes[$f->lead_cap_type].'</span>',
                    '<span id="ca-'.$f->id.'-value">'.$f->lead_cap_value.'</span>',
                ]);
            }
        }

        $responseData = [
            'draw' => intval($inputs['draw']),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalFiltered,
            'data' => $affsData,
        ];

        return response()->json($responseData, 200);
    }

    //NOT USED
    public function campAffiliateDataTable(Request $request): JsonResponse
    {
        $inputs = $request->all();
        // Log::info($inputs);
        $affsData = [];
        $campaign = $inputs['campaign'];
        $totalFiltered = 0;
        $totalRecords = 0;

        if ($campaign != '') {
            $param = $inputs['search']['value'];
            $start = $inputs['start'];
            $length = $inputs['length'];

            $status = $inputs['status'];

            $columns = [
                'affiliates.id',
                'affiliates.id',
                'affiliate_campaign.lead_cap_type',
                'payout',
                'received',
            ];

            $capTypes = config('constants.LEAD_CAP_TYPES');

            DB::enableQueryLog();
            $affiliates = Affiliate::affiliateCampaignSearch($param, $start, $length, $columns[$inputs['order'][0]['column']], $inputs['order'][0]['dir'], $campaign, $status, false)
                ->select(DB::raw('affiliates.id, company, affiliate_campaign.lead_cap_type,
                    affiliate_campaign.lead_cap_value,
                    CASE WHEN affiliate_campaign.lead_cap_type IS NULL THEN 0 ELSE 1 END as is_affiliate_campaign,
                    received,payout'))
                ->get();
            $totalFiltered = Affiliate::affiliateCampaignSearch($param, null, null, null, null, $campaign, $status, true)->count();
            $totalRecords = $param == '' ? $totalFiltered : Affiliate::count();
            Log::info(DB::getQueryLog());

            if (($start == 0 || $param == 'ALL AFFILIATES') && $totalFiltered > 0) {
                array_push($affsData, [
                    '<input type="checkbox" name="all_cam_affiliates" id="allCAMaffiliates-chkbx" value="ALL">',
                    '<strong>ALL AFFILIATES</strong>',
                    '',
                    '',
                    '',
                    '',
                ]);
            }

            foreach ($affiliates as $f) {
                if ($f->lead_cap_type === null) {
                    $cap_dis = 'Default';
                } else {
                    if ($f->lead_cap_type > 0) {
                        $cap_dis = $f->lead_cap_value.' '.$capTypes[$f->lead_cap_type];
                    } else {
                        $cap_dis = $capTypes[$f->lead_cap_type];
                    }
                }

                $deets = '<input type="checkbox" name="cam_affiliate[]" value="'.$f->id.'"/><input type="hidden" name="camAff-details-'.$f->id.'" value="'.json_encode($f).'"/>';
                array_push($affsData, [
                    $deets,
                    $f->id.' - '.$f->company,
                    $cap_dis,
                    $f->payout == null ? 'Default' : $f->payout,
                    $f->received == null ? 'Default' : $f->received,
                ]);
            }
        }

        $responseData = [
            'draw' => intval($inputs['draw']),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalFiltered,
            'data' => $affsData,
        ];

        return response()->json($responseData, 200);
    }

    public function getAvailableCampaignAffiliates(Request $request): JsonResponse
    {
        $id = $request->input('id');
        $affiliates = Affiliate::getAvailableAffiliates($id)->pluck('name', 'id')->toArray();

        return response()->json($affiliates, 200);
    }

    public function getAvailableAffiliatePayouts(Request $request): JsonResponse
    {
        $id = $request->input('id');
        $affiliates = Affiliate::getAvailableAffiliatesForPayout($id)->pluck('name', 'id')->toArray();

        return response()->json($affiliates, 200);
    }

    public function payoutHistoryTable(Request $request): JsonResponse
    {
        $inputs = $request->all();
        $historyData = [];

        $start = $inputs['start'];
        $length = $inputs['length'];
        $campaign_id = $inputs['campaign'];

        // DB::enableQueryLog();
        $logs = \App\UserActionLog::campaignPayoutHistory($campaign_id, $start, $length)->get();
        $total_logs = DB::select("SELECT COUNT(*) as total FROM (select (CASE WHEN summary LIKE 'Add%' THEN 'Add' WHEN summary LIKE 'Update%' THEN 'Update' ELSE 'Delete' END) as type from user_action_logs where reference_id = $campaign_id  and section_id = 3 and (sub_section_id is null or sub_section_id = 31) AND (summary LIKE '%rate%' OR summary LIKE '%received%' OR summary LIKE '%payout%') group by created_at, reference_id, section_id, sub_section_id, user_id, type) as subQuery");
        // Log::info(DB::getQueryLog());
        $totalFiltered = $total_logs[0]->total;

        foreach ($logs as $log) {

            $rate = null;
            $payout = null;
            $received = null;

            $summaries = explode('[{S}]', $log->summary);
            $values = explode('[{S}]', $log->new_value);

            //determine action
            if ($log->type == 'Update') { //Update
                //get column name
                foreach ($summaries as $key => $summary) {
                    $col = explode('Column:', $summary);
                    if (isset($col[1])) {
                        $column = str_replace(['.', ',', '?'], '', trim($col[1]));
                        // Log::info($column);
                        if ($column == 'rate') {
                            $rate = $values[$key];
                        } elseif ($column == 'default received') {
                            $received = $values[$key];
                        } elseif ($column == 'default payout') {
                            $payout = $values[$key];
                        }
                    }
                }
            } elseif ($log->type == 'Add') { //Add
                $value = json_decode($values[0], true);
                $rate = $value['rate'];
                $received = $value['default received'];
                $payout = $value['default payout'];
            }

            $date_applied = Carbon::parse($log->created_at)->toDateTimeString();

            $historyData[] = [
                $date_applied, //default or affiliate,
                $payout, //payout
                $received, //received
                $rate, //rate
                $date_applied,
                $log->user_name,
            ];
        }

        $responseData = [
            'draw' => intval($inputs['draw']),   // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            'recordsTotal' => $totalFiltered,  // total number of records
            'recordsFiltered' => $totalFiltered, // total number of records after searching, if there is no searching then totalFiltered = totalData
            'data' => $historyData,   // total data array
        ];

        return response()->json($responseData, 200);
    }

    public function getCampaignJsonContent(Request $request): JsonResponse
    {
        $id = $request->input('id');
        $type = $request->input('type');
        $content = CampaignJsonContent::find($id);

        if (! $content) {
            $content = CampaignJsonContent::firstOrCreate([
                'id' => $id,
            ]);
        }
        $response = [
            'json' => json_decode($content->json, true),
            'script' => $content->script,
        ];

        return response()->json($response, 200);
    }

    public function formCampaignJsonContent(Request $request)
    {
        $inputs = $request->all();
        // Log::info($inputs);
        // Log::info('<------------------------------------------------------------------->');
        $campaign_id = $inputs['id'];
        $campaign_type = $inputs['type'];

        $json = CampaignJsonContent::find($campaign_id);
        $current_state = $json->toArray(); //for logging

        if ($inputs['form']['id'] == '') {
            $inputs['form']['id'] = 'cmp-'.$campaign_id.'-form';
        }

        $json->script = $inputs['script'];
        unset($inputs['script']);
        $json->json = json_encode($inputs);
        $json->save();

        //Action Logger: Add Campaign Stack Form Builder
        $this->logger->log(3, 372, $campaign_id, 'Update stack content via form builder', $current_state, $json->toArray(), [], null);

        // $response = [
        //     'stack'     => $template,
        //     'form_id'   => $form_id,
        //     'js'        => $js
        // ];
        return $inputs;
    }

    /* CAMPAIGN CREATIVES */
    /**
     * Will return campaign's creative combinations that is compatible with data tables server side processing
     */
    public function getCampaignJsonCreative(Request $request): JsonResponse
    {
        $inputs = $request->all();
        // Log::info($inputs);
        $creativesData = [];

        // $start = $inputs['start'];
        // $length = $inputs['length'];

        $columns = [
            0 => 'id',
        ];

        if (isset($inputs['order'])) {
            $order_by = $columns[$inputs['order'][0]['column']];
            $order_dir = $inputs['order'][0]['dir'];
        } else {
            $order_by = 'created_at';
            $order_dir = 'desc';
        }
        $total_count = CampaignCreative::where('campaign_id', $inputs['campaign_id'])->count();
        $creatives = CampaignCreative::where('campaign_id', $inputs['campaign_id'])
            ->orderBy($order_by, $order_dir);

        // if($length>1) $creatives = $creatives->skip($start)->take($length);

        $creatives = $creatives->get();

        $campaign = Campaign::find($inputs['campaign_id']);

        foreach ($creatives as $creative) {
            $id = $creative->id;

            $idCol = '<span>'.$id.'</span><div style="margin-bottom: 50px;"></div>';

            $idCol .= '<input id="cmpCrtv-'.$id.'-weight" name="weight" value="'.$creative->weight.'" class="form-control spinner cmpCreativeField" data-id="'.$id.'">';

            if (is_null($creative->image) || $creative->image == '') {
                $image = url('images/img_unavailable.jpg');
            } else {
                $image = $creative->image;
            }

            $imageCol =
                '<div><div class="row">
                    <div class="col-md-12">
                        <div class="creativeIconPreview">
                            <img id="cmpCrtv-'.$id.'-img-preview" src="'.$creative->image.'"/>
                        </div>
                    </div>
                    <div id="cmpCrtv-'.$id.'-imgLinkDiv" class="col-md-12 form_div" style="display:none">
                        <label for="image">Image URL:</label>
                        <input class="form-control this_field cmpCreativeField campaignCreativeImageLink" name="image" type="text" id="cmpCrtv-'.$id.'-img" data-id="'.$id.'" value="'.$creative->image.'" style="width:100%">
                    </div>
                </div></div>';
            $previewLink = config('constants.JSON_PATH_URL').'/preview_json.php?id='.$inputs['campaign_id'].'&type='.$campaign->campaign_type.'&creative='.$id;
            $actionCol = '<button id="cmpCrtv-'.$id.'-edit-button" class="btn btn-default editCampaignCreative" type="button" data-id="'.$id.'" data-type="edit"><span class="glyphicon glyphicon-pencil"></span></button>
                            <button id="cmpCrtv-'.$id.'-save-button" class="btn btn-primary saveCampaignCreative" type="button" data-id="'.$id.'" style="display:none"><i class="fa fa-floppy-o" aria-hidden="true"></i></button>
                            <a id="cmpCrtv-'.$id.'-preview-button" href="'.$previewLink.'" target="_blank" class="previewCampaignCreative btn btn-default pull-right" data-id="'.$id.'"><span class="glyphicon glyphicon-eye-open"></span></a>
                            <button id="cmpCrtv-'.$id.'-delete-button" class="btn btn-default deleteCampaignCreative" type="button" data-id="'.$id.'"><span class="glyphicon glyphicon-trash"></span></button>';
            $hiddenDataCol = '<input type="hidden" id="cmpCrtv-'.$id.'-weight-original" name="original_weight" value="'.$creative->weight.'">
                              <textarea id="cmpCrtv-'.$id.'-desc-original" class="hidden" name="original_description">'.$creative->description.'</textarea>
                              <input type="hidden" id="cmpCrtv-'.$id.'-img-original" name="original_image" value="'.$creative->image.'">';
            $data = [
                $idCol,
                '<textarea id="cmpCrtv-'.$id.'-desc" class="stackCreativeDesc form-control this_field cmpCreativeField" required="true" name="cmpCrtv-'.$id.'-desc">'.$creative->description.'</textarea>',
                $imageCol,
                $actionCol.$hiddenDataCol,
            ];
            array_push($creativesData, $data);
        }

        // Log::info($creativesData);

        $responseData = [
            'draw' => intval($inputs['draw']),   // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            'recordsTotal' => $total_count,  // total number of records
            'recordsFiltered' => count($creatives), // total number of records after searching, if there is no searching then totalFiltered = totalData
            'data' => $creativesData,
            'canEdit' => $this->canEdit,
            'canDelete' => $this->canDelete,
            'canAdd' => $total_count < 4 ? true : false,
        ];

        return response()->json($responseData, 200);
    }

    public function contentTagChecking($stack)
    {
        // \Log::info($stack);
        $errors = [];
        $self_closing = ['area', 'base', 'br', 'col', 'embed', 'hr', 'img', 'input', 'link', 'meta', 'param', 'source', 'track', 'wbr'];
        $exclude = array_merge($self_closing, ['html', 'body', 'head']);

        // create new DOMDocument
        $document = new \DOMDocument('1.0', 'UTF-8');

        // set error level
        $internalErrors = libxml_use_internal_errors(true);

        $content_stack = $stack;
        $content_stack = $this->getSampleVal($content_stack, []);
        $content_stack = preg_replace('~<!--(.*?)-->~s', '', $content_stack);
        $content_stack = str_replace('-->', '', $content_stack);
        $content_stack = str_replace('<--', '', $content_stack);

        $newStr = '';
        $commentTokens = [T_COMMENT];

        if (defined('T_DOC_COMMENT')) {
            $commentTokens[] = T_DOC_COMMENT;
        } // PHP 5
        if (defined('T_ML_COMMENT')) {
            $commentTokens[] = T_ML_COMMENT;
        }  // PHP 4

        $tokens = token_get_all($content_stack);
        foreach ($tokens as $token) {
            if (is_array($token)) {
                if (in_array($token[0], $commentTokens)) {
                    continue;
                }
                $token = $token[1];
            }
            $newStr .= $token;
        }
        $content_stack = $newStr;
        // \Log::info($content_stack);
        $hasCode = false;
        $phpStartCount = substr_count($content_stack, '<?php') + substr_count($content_stack, '<?=');
        //\Log::info($phpStartCount);
        $phpEndCount = substr_count($content_stack, '?>');
        //\Log::info($phpEndCount);

        if ($phpStartCount != $phpEndCount) {
            $hasCode = true;
            $errors[] = 'PHP Parsing error. Check PHP tags.';
        }

        /*if(strpos($content_stack, '<?php') !== false) {
            try {
                ob_start();
                eval('?>'.$content_stack);
                $content_stack = ob_get_contents();
                ob_end_clean();
            } catch (\Throwable $e) {
                $hasCode = true;
                $errors[] = 'PHP parsing error.';
                $errors[] = $e;
                // \Log::info('EVAL ERROR');
                // \Log::info($e);
            }
        }*/

        // load HTML
        $document->loadHTML($content_stack);

        // Restore error level
        libxml_use_internal_errors($internalErrors);

        $xpath = new \DOMXpath($document);

        $nodes = $xpath->query('//*');

        //$names = array();
        $elements = [];
        $removedPCode = false;
        foreach ($nodes as $node) {
            if (! in_array($node->nodeName, $exclude)) {
                if ($hasCode && ! $removedPCode) {
                    //This is to remove additional error "Missing closing tag for <p>" when a php parsing error code exists
                    $removedPCode = true;
                } elseif ($node->nodeName == 'p') {
                    if (in_array('div', array_keys($elements))) {
                        if (! isset($elements[$node->nodeName])) {
                            $elements[$node->nodeName] = 0;
                        }
                        $elements[$node->nodeName] += 1;
                    }
                } else {
                    if (! isset($elements[$node->nodeName])) {
                        $elements[$node->nodeName] = 0;
                    }
                    $elements[$node->nodeName] += 1;
                }
            }
            // \Log::info($node->nodeName);
            //$names[] = $node->nodeName;
        }

        // \Log::info('-----------');
        foreach ($elements as $element => $count) {
            // \Log::info($element.' '.$count.': '.substr_count($content_stack, "<$element").' - '.substr_count($content_stack, "</$element>"));
            if (substr_count($content_stack, "</$element>") < $count) {
                $errors[] = "Missing closing tag for &lt;$element&gt;";
            }
        }
        //return [];
        return $errors;
    }

    public function getSampleVal($html, $values = [])
    {

        $affiliate_id = isset($values['affiliate_id']) ? $values['affiliate_id'] : 1;

        return $this->getValues($html, [
            '[VALUE_AFFILIATE_ID]' => $affiliate_id,
            'CD[VALUE_REV_TRACKER]' => 'CD'.$affiliate_id,
            '[VALUE_REV_TRACKER]' => $affiliate_id,
            '[VALUE_EMAIL]' => 'test@test.com',
            '[VALUE_S1]' => '1',
            '[VALUE_S2]' => '2',
            '[VALUE_S3]' => '3',
            '[VALUE_S4]' => '4',
            '[VALUE_S5]' => '5',
            '[VALUE_FIRST_NAME]' => 'John',
            '[VALUE_LAST_NAME]' => 'Doe',
            '[VALUE_ZIP]' => '10001',
            '[VALUE_IP]' => '127.0.0.1',
            '[VALUE_ADDRESS1]' => '123 Test St.',
            '[VALUE_PATH_ID]' => '1',
            '[VALUE_AGE]' => '24',
            '[VALUE_BIRTHDATE]' => '1994-03-16',
            '[VALUE_BIRTHDATE_MDY]' => '03-16-1994',
            '[VALUE_DOBDAY]' => '16',
            '[VALUE_DOBMONTH]' => '03',
            '[VALUE_DOBYEAR]' => '1994',
            '[VALUE_ETHNICITY]' => 'Caucasian',
            '[VALUE_GENDER]' => 'M',
            '[VALUE_TITLE]' => 'Title/Honorific (Mr, Ms, etc.)',
            '[VALUE_GENDER_FULL]' => 'Male',
            '[VALUE_STATE]' => 'NY',
            '[VALUE_CITY]' => 'New York',
            '[VALUE_ZIP]' => '10001',
            '[VALUE_PHONE]' => '7898797897',
            '[VALUE_PHONE1]' => '789',
            '[VALUE_PHONE2]' => '879',
            '[VALUE_PHONE3]' => '7897',
            '[VALUE_DATE_TIME]' => '03/16/2020 17:56:01',
            '[VALUE_TODAY]' => '03/16/2020)',
            '[VALUE_TODAY_MONTH]' => '03',
            '[VALUE_TODAY_DAY]' => '16',
            '[VALUE_TODAY_YEAR]' => '2020',
            '[VALUE_TODAY_HOUR]' => '17',
            '[VALUE_TODAY_MIN]' => '56',
            '[VALUE_TODAY_SEC]' => '01',
            '[VALUE_PUB_TIME]' => '2020-03-16 17:56:01',
            '[DETECT_OS]' => 'Windows',
            '[DETECT_OS_VER]' => 'Windows 10',
            '[DETECT_BROWSER]' => 'Chrome',
            '[DETECT_BROWSER_VER]' => '58.0.3029.96',
            '[DETECT_USER_AGENT]' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.96 Safari/537.36',
            '[DETECT_DEVICE]' => 'Mobile',
            'src="../' => 'src="'.config('constants.LEAD_REACTOR_PATH'),
            'href="../' => 'href="'.config('constants.LEAD_REACTOR_PATH'),
        ]);
    }

    public function getValues($html, $values)
    {
        foreach ($values as $short_code => $value) {
            if (strpos($html, $short_code) !== false) {
                $html = str_replace($short_code, $value, $html);
            }
        }

        return $html;
    }
}
