<?php

namespace App\Http\Controllers;

use App\AffiliateRevenueTracker;
use App\Campaign;
use App\CampaignTypeOrder;
use App\Commands\GetUserActionPermission;
use App\Http\Requests\RevenueTrackerRequest;
use App\Http\Services;
use App\MixedCoregCampaignOrder;
use Bus;
use Carbon\Carbon;
use DB;
use Excel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Log;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class RevenueTrackerController extends Controller
{
    protected $canEdit = false;

    protected $canDelete = false;

    protected $user;

    protected $logger;

    public function __construct(Services\UserActionLogger $logger)
    {
        //get the user
        $user = auth()->user();

        $this->canEdit = Bus::dispatch(new GetUserActionPermission($user, 'use_edit_revenue_trackers'));
        $this->canDelete = Bus::dispatch(new GetUserActionPermission($user, 'use_delete_revenue_trackers'));
        $this->user = $user;
        $this->logger = $logger;
    }

    /**
     * Server side data table processing for revenue trackers
     */
    public function index(Request $request): JsonResponse
    {
        $inputs = $request->all();
        $revenueTrackersData = [];

        $param = $inputs['search']['value'];
        $start = $inputs['start'];
        $length = $inputs['length'];

        $columns = [
            0 => 'website',
            1 => 'company',
            2 => 'campaign_id',
            3 => 'offer_id',
            4 => 'revenue_tracker_id',
            5 => 's1',
            6 => 's2',
            7 => 's3',
            8 => 's4',
            9 => 's5',
            10 => 'subid_breakdown',
        ];

        session()->put('revenue_trackers_input', $inputs);

        $revenueTrackers = AffiliateRevenueTracker::searchRevenueTrackers($param, $start, $length, $columns[$inputs['order'][0]['column']], $inputs['order'][0]['dir'])
            ->select(DB::raw('affiliate_revenue_trackers.*, affiliates.company, revTracker.company as revenue_tracker_name'))
            // ->with('campaignTypeOrders')
            ->get();

        $totalFiltered = AffiliateRevenueTracker::searchRevenueTrackers($param, null, null, null, null)->count();

        $subid_status = config('constants.UNI_STATUS2');

        foreach ($revenueTrackers as $tracker) {
            $data = [];

            // //for id html
            // $idHTML = '<span id="trk-'.$tracker->id.'-id">'.$tracker->id.'</span>';
            // array_push($data,$idHTML);

            //for website html
            $websiteHTML = '<span id="trk-'.$tracker->id.'-web">'.$tracker->website.'</span>';
            array_push($data, $websiteHTML);

            //create the company html
            $affiliateHTML = '<span id="trk-'.$tracker->id.'-aff" data-affiliate="'.$tracker->affiliate_id.'">'.$tracker->company.' ('.$tracker->affiliate_id.')'.'</span>';
            array_push($data, $affiliateHTML);

            //create the campaign id html
            $campaignIDHTML = '<span id="trk-'.$tracker->id.'-cmp">'.$tracker->campaign_id.'</span>';
            array_push($data, $campaignIDHTML);

            //create the offer id html
            $offerIDHTML = '<span id="trk-'.$tracker->id.'-ofr">'.$tracker->offer_id.'</span>';
            array_push($data, $offerIDHTML);

            //create the revenue tracker id html
            $revenueTrackerIDHTML = '<span id="trk-'.$tracker->id.'-rti" data-revenue_tracker_name="'.$tracker->revenue_tracker_name." ($tracker->revenue_tracker_id)".'">'.$tracker->revenue_tracker_id.'</span>';
            array_push($data, $revenueTrackerIDHTML);

            //create the s1 html
            $s1HTML = '<span id="trk-'.$tracker->id.'-s1">'.$tracker->s1.'</span>';
            array_push($data, $s1HTML);

            //create the s2 html
            $s2HTML = '<span id="trk-'.$tracker->id.'-s2">'.$tracker->s2.'</span>';
            array_push($data, $s2HTML);

            //create the s3 html
            $s3HTML = '<span id="trk-'.$tracker->id.'-s3">'.$tracker->s3.'</span>';
            array_push($data, $s3HTML);

            //create the s4 html
            $s4HTML = '<span id="trk-'.$tracker->id.'-s4">'.$tracker->s4.'</span>';
            array_push($data, $s4HTML);

            //create the s5 html
            $s5HTML = '<span id="trk-'.$tracker->id.'-s5">'.$tracker->s5.'</span>';
            array_push($data, $s5HTML);

            //create the subid breakdown html
            $sidbHTML = $subid_status[$tracker->subid_breakdown];
            array_push($data, $sidbHTML);

            //mixed coreg campaign reordering
            $mixedCoregDetails = '<input type="hidden" id="trk-'.$tracker->id.'-mixed_coreg_order_by" value="'.$tracker->mixed_coreg_order_by.'" />';
            $mixedCoregDetails .= '<input type="hidden" id="trk-'.$tracker->id.'-mixed_coreg_order_status" value="'.$tracker->mixed_coreg_order_status.'" />';
            $mixedCoregDetails .= '<input type="hidden" id="trk-'.$tracker->id.'-mixed_coreg_campaign_views" value="'.$tracker->mixed_coreg_campaign_views.'" />';
            $mixedCoregDetails .= '<input type="hidden" id="trk-'.$tracker->id.'-mixed_coreg_campaign_limit" value="'.$tracker->mixed_coreg_campaign_limit.'" />';

            $campaignOrderHiddenInputs = null;

            $editButton = '';
            $deleteButton = '';

            $details = '<textarea id="trk-'.$tracker->id.'-details" class="hidden">'.json_encode($tracker).'</textarea>';

            if ($this->canEdit) {
                $editButton = '<button class="editTracker btn-actions btn btn-primary" title="Edit" data-id="'.$tracker->id.'"><span class="glyphicon glyphicon-pencil"></span></button>';
                $editButton .= '<button class="trackerCampaignType btn-actions btn btn-info" title="Campaign Type" data-id="'.$tracker->revenue_tracker_id.'" data-rt_id="'.$tracker->id.'"><span class="glyphicon glyphicon-th-list"></span></button>';
                $editButton .= '<button class="mixedCoregCampaignOrdering btn-actions btn btn-success" title="Mixed Coreg" data-id="'.$tracker->revenue_tracker_id.'" data-rt_id="'.$tracker->id.'"><span class="glyphicon glyphicon-th-list"></span></button>';
            }

            if ($this->canDelete) {
                $deleteButton = '<button class="deleteTracker btn-actions btn btn-danger" title="Delete" data-id="'.$tracker->id.'"><span class="glyphicon glyphicon-trash"></span></button>';
            }

            array_push($data, $mixedCoregDetails.$campaignOrderHiddenInputs.$editButton.$deleteButton.$details);

            array_push($revenueTrackersData, $data);
        }

        $responseData = [
            'draw' => intval($inputs['draw']),   // for every request/draw by client side , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            'recordsTotal' => AffiliateRevenueTracker::count(),  // total number of records
            'recordsFiltered' => $totalFiltered, // total number of records after searching, if there is no searching then totalFiltered = totalData
            'data' => $revenueTrackersData,   // total data array
        ];

        return response()->json($responseData, 200);
    }

    /**
     * Store a newly created revenue tracker resource in storage.
     */
    public function store(RevenueTrackerRequest $request): JsonResponse
    {
        // \Log::info($request->all());
        $crg_limit = $request->input('crg_limit') == null ? null : $request->input('crg_limit');
        $ext_limit = $request->input('ext_limit') == null ? null : $request->input('ext_limit');
        $lnk_limit = $request->input('lnk_limit') == null ? null : $request->input('lnk_limit');

        $tracker = new AffiliateRevenueTracker;
        $tracker->affiliate_id = $request->input('affiliate');
        $tracker->campaign_id = $request->input('campaign');
        $tracker->offer_id = $request->input('offer');
        $tracker->revenue_tracker_id = $request->input('revenue_tracker');
        $tracker->s1 = $request->input('s1');
        $tracker->s2 = $request->input('s2');
        $tracker->s3 = $request->input('s3');
        $tracker->s4 = $request->input('s4');
        $tracker->s5 = $request->input('s5');
        $tracker->note = $request->input('notes');
        $tracker->tracking_link = $request->input('link');
        $tracker->crg_limit = $crg_limit;
        $tracker->ext_limit = $ext_limit;
        $tracker->lnk_limit = $lnk_limit;
        $tracker->path_type = $request->input('type');
        $tracker->website = $request->input('website');
        $tracker->fire_at = $request->input('fire') != null && $request->input('fire') > 0 ? $request->input('fire') : null;
        $tracker->pixel = $request->input('pixel');
        $tracker->order_type = $request->input('order_type');
        $tracker->pixel_header = $request->input('pixel_header');
        $tracker->pixel_body = $request->input('pixel_body');
        $tracker->pixel_footer = $request->input('pixel_footer');
        // $tracker->exit_page_id =  $request->input('exit_page') != '' ? $request->input('exit_page') : null;
        $tracker->subid_breakdown = 1;
        $tracker->sib_s1 = 1;
        $tracker->sib_s2 = 1;
        $tracker->sib_s3 = 1;
        $tracker->sib_s4 = 1;
        $tracker->report_subid_breakdown_status = 1;
        $tracker->rsib_s1 = 1;
        $tracker->rsib_s2 = 1;
        $tracker->rsib_s3 = 1;
        $tracker->rsib_s4 = 1;
        $tracker->new_subid_breakdown_status = $request->input('new_subid_breakdown_status') != 'null' && $request->input('new_subid_breakdown_status') != '' ? $request->input('new_subid_breakdown_status') : null;
        $tracker->nsib_s1 = $request->input('nsib_s1') != '' ? $request->input('nsib_s1') : null;
        $tracker->nsib_s2 = $request->input('nsib_s2') != '' ? $request->input('nsib_s2') : null;
        $tracker->nsib_s3 = $request->input('nsib_s3') != '' ? $request->input('nsib_s3') : null;
        $tracker->nsib_s4 = $request->input('nsib_s4') != '' ? $request->input('nsib_s4') : null;
        $tracker->save();

        //Action Logger: Add Revenue Tracker
        // $this->logger->logger(7, null, $tracker->id, null, $tracker);
        $value_mask = [
            'fire_at' => config('constants.PAGE_FIRE_PIXEL'),
            'order_type' => config('constants.PATH_ORDER_TYPE'),
            // 'exit_page_id' => Campaign::where('id', $tracker->exit_page_id)->pluck('name', 'id'),
            'path_type' => config('constants.PATH_TYPES'),
            'subid_breakdown' => config('constants.UNI_STATUS2'),
            'new_subid_breakdown_status' => config('constants.UNI_STATUS2'),
            'nsib_s1' => config('constants.UNI_STATUS2'),
            'nsib_s2' => config('constants.UNI_STATUS2'),
            'nsib_s3' => config('constants.UNI_STATUS2'),
            'nsib_s4' => config('constants.UNI_STATUS2'),
        ];
        $key_mask = [
            'fire_at' => 'affiliate pixel fires at',
            'pixel' => 'affiliate pixel',
            'nsib_s1' => 'new sub id breakdown status for s1',
            'nsib_s2' => 'new sub id breakdown status for s2',
            'nsib_s3' => 'new sub id breakdown status for s3',
            'nsib_s4' => 'new sub id breakdown status for s4',
        ];
        $this->logger->log(7, null, $tracker->revenue_tracker_id, null, null, $tracker->toArray(), $key_mask, $value_mask);

        $responseData = [
            'id' => $tracker->id,
            'canEdit' => $this->canEdit,
            'canDelete' => $this->canDelete,
        ];

        return response()->json($responseData, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @return mixed
     */
    public function update(RevenueTrackerRequest $request)
    {
        $id = $request->input('this_id');

        $crg_limit = $request->input('crg_limit') == null ? null : $request->input('crg_limit');
        $ext_limit = $request->input('ext_limit') == null ? null : $request->input('ext_limit');
        $lnk_limit = $request->input('lnk_limit') == null ? null : $request->input('lnk_limit');

        $tracker = AffiliateRevenueTracker::find($id);

        $current_state = $tracker->toArray(); //For Logging

        $new_rev_tracker = $request->input('revenue_tracker');
        $old_rev_tracker = $tracker->revenue_tracker_id;

        // if($old_rev_tracker != $new_rev_tracker) {
        //     CampaignTypeOrder::where('revenue_tracker_id', $old_rev_tracker)->update(['revenue_tracker_id' => $new_rev_tracker]);
        // }

        $tracker->website = $request->input('website');
        $tracker->affiliate_id = $request->input('affiliate');
        $tracker->campaign_id = $request->input('campaign');
        $tracker->offer_id = $request->input('offer');
        $tracker->revenue_tracker_id = $new_rev_tracker;
        $tracker->s1 = $request->input('s1');
        $tracker->s2 = $request->input('s2');
        $tracker->s3 = $request->input('s3');
        $tracker->s4 = $request->input('s4');
        $tracker->s5 = $request->input('s5');
        $tracker->note = $request->input('notes');
        $tracker->tracking_link = $request->input('link');
        $tracker->crg_limit = $crg_limit;
        $tracker->ext_limit = $ext_limit;
        $tracker->lnk_limit = $lnk_limit;
        $tracker->path_type = $request->input('type');
        $tracker->fire_at = $request->input('fire') != null && $request->input('fire') > 0 ? $request->input('fire') : null;
        $tracker->pixel = $request->input('pixel');
        $tracker->order_type = $request->input('order_type');
        $tracker->pixel_header = $request->input('pixel_header');
        $tracker->pixel_body = $request->input('pixel_body');
        $tracker->pixel_footer = $request->input('pixel_footer');
        // $tracker->exit_page_id          =  $request->input('exit_page') != '' ? $request->input('exit_page') : null;
        // $tracker->subid_breakdown = $request->input('subid_breakdown');
        $tracker->new_subid_breakdown_status = $request->input('new_subid_breakdown_status') != 'null' && $request->input('new_subid_breakdown_status') != '' ? $request->input('new_subid_breakdown_status') : null;
        $tracker->nsib_s1 = $request->input('nsib_s1') != '' ? $request->input('nsib_s1') : null;
        $tracker->nsib_s2 = $request->input('nsib_s2') != '' ? $request->input('nsib_s2') : null;
        $tracker->nsib_s3 = $request->input('nsib_s3') != '' ? $request->input('nsib_s3') : null;
        $tracker->nsib_s4 = $request->input('nsib_s4') != '' ? $request->input('nsib_s4') : null;

        $tracker->save();

        //Action Logger: Edit Revenue Tracker
        $value_mask = [
            'fire_at' => config('constants.PAGE_FIRE_PIXEL'),
            'order_type' => config('constants.PATH_ORDER_TYPE'),
            // 'exit_page_id' => Campaign::whereIn('id',[$current_state['exit_page_id'], $tracker->exit_page_id])->pluck('name', 'id'),
            'path_type' => config('constants.PATH_TYPES'),
            'subid_breakdown' => config('constants.UNI_STATUS2'),
            'new_subid_breakdown_status' => config('constants.UNI_STATUS2'),
            'nsib_s1' => config('constants.UNI_STATUS2'),
            'nsib_s2' => config('constants.UNI_STATUS2'),
            'nsib_s3' => config('constants.UNI_STATUS2'),
            'nsib_s4' => config('constants.UNI_STATUS2'),
        ];

        $key_mask = [
            'fire_at' => 'affiliate pixel fires at',
            'pixel' => 'affiliate pixel',
            'nsib_s1' => 'new sub id breakdown status for s1',
            'nsib_s2' => 'new sub id breakdown status for s2',
            'nsib_s3' => 'new sub id breakdown status for s3',
            'nsib_s4' => 'new sub id breakdown status for s4',
        ];
        $this->logger->log(7, null, $tracker->revenue_tracker_id, null, $current_state, $tracker->toArray(), $key_mask, $value_mask);

        return $tracker->id;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        $tracker = AffiliateRevenueTracker::find($request->input('id'));

        //Action Logger: Delete Rev Tracker
        $value_mask = [
            'fire_at' => config('constants.PAGE_FIRE_PIXEL'),
            'order_type' => config('constants.PATH_ORDER_TYPE'),
            // 'exit_page_id' => Campaign::where('campaign_type', 6)->pluck('name', 'id'),
            'path_type' => config('constants.PATH_TYPES'),
        ];
        $key_mask = [
            'fire_at' => 'affiliate pixel fires at',
            'pixel' => 'affiliate pixel',
        ];
        $this->logger->log(7, null, $tracker->revenue_tracker_id, null, $tracker->toArray(), null, $key_mask, $value_mask);

        $tracker->delete();
    }

    public function campaignOrderDetails($id): JsonResponse
    {
        // $campaign_orders = CampaignTypeOrder::where('revenue_tracker_id', $id)->pluck('campaign_id_order','campaign_type_id');
        $reference_dates = [];
        $campaign_orders = [];
        $campaign_types = CampaignTypeOrder::where('revenue_tracker_id', $id)->get();
        foreach ($campaign_types as $type) {
            $campaign_orders[$type->campaign_type_id] = $type->campaign_id_order;
            $reference_dates[$type->campaign_type_id] = $type->reorder_reference_date;
        }
        // Log::info($campaign_orders);
        if (count($campaign_orders) == 0) {
            $campaign_orders = null;
        }
        if (count($reference_dates) == 0) {
            $reference_dates = null;
        }

        $responseData = [
            'campaignOrder' => $campaign_orders,
            'referenceDate' => $reference_dates,
        ];

        return response()->json($responseData, 200);
    }

    public function mixedCoregCampaignOrderDetails($id)
    {
        //$campaign_orders = CampaignTypeOrder::where('revenue_tracker_id', $id)->pluck('campaign_id_order','campaign_type_id');
        $mixedCoregCampaignOrder = MixedCoregCampaignOrder::where('revenue_tracker_id', $id)
            ->with(['affiliateRevenueTracker' => function ($query) {
                return $query->select('revenue_tracker_id', 'mixed_coreg_recurrence', 'mixed_coreg_daily');
            }])->first();

        $campaignOrders = null;
        $referenceDate = null;
        $daily = [
            'recurence' => 'views',
            'daily_time' => '00:00:00',
        ];

        if ($mixedCoregCampaignOrder) {
            $campaignOrders = $mixedCoregCampaignOrder->campaign_id_order;
            $referenceDate = $mixedCoregCampaignOrder->reorder_reference_date;
            $daily = [
                'recurence' => $mixedCoregCampaignOrder->affiliateRevenueTracker->mixed_coreg_recurrence,
                'daily_time' => $mixedCoregCampaignOrder->affiliateRevenueTracker->mixed_coreg_daily,
            ];
        }

        $responseData = [
            'campaignOrder' => $campaignOrders,
            'reorderReferenceDate' => $referenceDate,
            'daily' => $daily,
        ];

        return response()->json($responseData, 200);
    }

    public function updateCampaignOrder(Request $request): JsonResponse
    {
        $inputs = $request->all();
        // Log::info($inputs);
        // die();
        $id = $inputs['rev_tracker_id'];
        $art_id = $inputs['this_id'];
        $reorder = isset($inputs['reorder']) ? $inputs['reorder'] : 0;
        $new_ref_date = Carbon::now();
        $change_ref_date = false;

        $tracker = AffiliateRevenueTracker::find($art_id);

        $current_state = $tracker->toArray(); //For Logging

        //create the campaign_type_order records
        $campaignTypes = config('constants.CAMPAIGN_TYPES');

        foreach ($inputs['campaign_type_order'] as $type => $order) {
            $campaignTypeOrder = CampaignTypeOrder::firstOrNew([
                'revenue_tracker_id' => $id,
                'campaign_type_id' => $type,
            ]);

            if (! $campaignTypeOrder->exists) {
                $cur_order_state = null;
            } else {
                $cur_order_state = $campaignTypeOrder->toArray();
            }

            $campaignTypeOrder->campaign_id_order = $order == '' ? '[]' : $order;

            if (($campaignTypeOrder->reorder_reference_date == '' || $campaignTypeOrder->reorder_reference_date == null || $campaignTypeOrder->reorder_reference_date == '0000-00-00 00:00:00') || ($reorder == 1 && ($tracker->order_status != $reorder))) {
                $campaignTypeOrder->reorder_reference_date = $new_ref_date;
                $change_ref_date = true;
            }

            $campaignTypeOrder->save();

            //User Action Logging: Update Campaign Order Details
            $summary = 'Set campaign order for campaign type: '.$campaignTypes[$type];
            $this->logger->log(7, 72, $id, $summary, $cur_order_state, $campaignTypeOrder->toArray());
        }

        // if($reorder==1 && ($tracker->order_status!=$reorder))
        // {
        //     CampaignTypeOrder::where('revenue_tracker_id', $id)->update(['reorder_reference_date' => $new_ref_date]);
        //     $change_ref_date = true;
        // }

        $tracker->order_by = $inputs['order_by'];
        $tracker->order_status = $reorder;
        $tracker->views = $inputs['views'];
        $tracker->save();

        $response = [
            'id' => $art_id,
            'change_ref_date' => $change_ref_date,
            'tracker' => $tracker,
        ];

        //User Action Logging: Update Campaign Order Details in AffiliateRevenueTracker Table
        $value_mask = [
            'order_by' => config('constants.CAMPAIGN_ORDER_DIRECTION'),
            'order_status' => ['Disabled', 'Enabled'],
        ];
        $key_mask = [
            'order_status' => 'reorder',
        ];
        $this->logger->log(7, 72, $tracker->revenue_tracker_id, null, $current_state, $tracker->toArray(), $key_mask, $value_mask);

        return response()->json($response, 200);
    }

    public function updateMixedCoregCampaignOrder(Request $request)
    {
        $inputs = $request->all();

        // Log::info($inputs);
        $id = $inputs['mixed_coreg_rev_tracker_id'];
        $art_id = $inputs['this_id'];

        //Log::info('$id: '.$id);
        //Log::info('$art_id: '.$art_id);

        $mixedCoregReorder = isset($inputs['mixed_coreg_reorder']) ? $inputs['mixed_coreg_reorder'] : 0;
        $new_ref_date = Carbon::now()->toDateTimeString();

        $tracker = AffiliateRevenueTracker::find($art_id);

        $current_state = $tracker->toArray(); //For Logging

        $mixedCoregCampaignOrder = MixedCoregCampaignOrder::firstOrNew([
            'revenue_tracker_id' => $id,
        ]);

        //For Logging
        $current_mc_state = null;
        if ($mixedCoregCampaignOrder->exists) {
            $current_mc_state = $mixedCoregCampaignOrder->toArray();
        }

        // Log::info($tracker);
        if (array_key_exists('mixed_coreg_daily', $inputs)) {
            $time = explode(' ', $inputs['mixed_coreg_daily']);
            $hourMinute = explode(':', $time[0]);
            if (strtolower($time[1]) == 'pm') {
                $hourMinute[0] = (int) $hourMinute[0] + 12;
            }
            if ($hourMinute[0] == 24) {
                $hourMinute[0] = '00';
            }
            $tracker->mixed_coreg_daily = $hourMinute[0].':'.$hourMinute[1];
        }
        if (array_key_exists('mixed_coreg_views', $inputs)) {
            $tracker->mixed_coreg_campaign_views = $inputs['mixed_coreg_views'];
        }
        if (array_key_exists('mixed_coreg_recurrence', $inputs)) {
            $tracker->mixed_coreg_recurrence = $inputs['mixed_coreg_recurrence'];
        }

        $tracker->mixed_coreg_order_by = $inputs['mixed_coreg_order_by'];
        $tracker->mixed_coreg_campaign_limit = $inputs['mixed_coreg_limit'];

        // Log::info("Mixed Coreg Reorder Status: $mixedCoregReorder");
        // Log::info("Current Coreg Reorder Status: $tracker->mixed_coreg_order_status");

        $mixedCoregCampaignOrder->campaign_id_order = $inputs['mixed_coreg_campaign_order'] == '' ? '[]' : $inputs['mixed_coreg_campaign_order'];

        if (empty($mixedCoregCampaignOrder->reorder_reference_date) || $mixedCoregCampaignOrder->reorder_reference_date == '0000-00-00 00:00:00') {
            $mixedCoregCampaignOrder->reorder_reference_date = $new_ref_date;
        }

        if ($mixedCoregReorder == 1 && ($tracker->mixed_coreg_order_status != $mixedCoregReorder)) {
            $mixedCoregCampaignOrder->reorder_reference_date = $new_ref_date;
        }

        $tracker->mixed_coreg_order_status = $mixedCoregReorder;
        $tracker->save();

        // Log::info($mixedCoregCampaignOrder);

        $mixedCoregCampaignOrder->save();

        //User Action Logging: Update Mixed Coreg Order
        $value_mask = [
            'mixed_coreg_order_by' => config('constants.CAMPAIGN_ORDER_DIRECTION'),
            'mixed_coreg_order_status' => ['Disabled', 'Enabled'],
        ];
        $key_mask = [
            'mixed_coreg_order_status' => 'reorder',
        ];

        $new_state = $tracker->toArray();
        $new_state['mixed_coreg_daily'] = Carbon::parse($tracker->mixed_coreg_daily)->toTimeString();
        $this->logger->log(7, 73, $id, null, $current_state, $new_state, $key_mask, $value_mask);
        $this->logger->log(7, 73, $id, 'Set mixed-coreg campaign order', $current_mc_state, $mixedCoregCampaignOrder->toArray(), null, null);

        return $art_id;
    }

    public function download(): BinaryFileResponse
    {
        $inputs = session()->get('revenue_trackers_input');
        $param = $inputs['search']['value'];

        $columns = [
            0 => 'website',
            1 => 'company',
            2 => 'campaign_id',
            3 => 'offer_id',
            4 => 'revenue_tracker_id',
            5 => 's1',
            6 => 's2',
            7 => 's3',
            8 => 's4',
            9 => 's5',
            10 => 'subid_breakdown',
        ];

        $revenueTrackers = AffiliateRevenueTracker::searchRevenueTrackers($param, null, null, $columns[$inputs['order'][0]['column']], $inputs['order'][0]['dir'])
            ->select(DB::raw('affiliate_revenue_trackers.*, affiliates.company, revTracker.company as revenue_tracker_name'))
            ->get();

        $title = 'RevenueTrackers_'.Carbon::now()->toDateString();
        $subid_status = config('constants.UNI_STATUS2');

        Excel::create($title, function ($excel) use ($title, $revenueTrackers, $subid_status) {
            $excel->sheet($title, function ($sheet) use ($revenueTrackers, $subid_status) {

                // Set auto size for sheet
                $sheet->setAutoSize(true);

                $headers = ['Website', 'Affiliate', 'Campaign ID', 'Offer ID', 'Revenue Tracker ID', 'S1', 'S2', 'S3', 'S4', 'S5', 'Sub ID Breakdown', 'Path Type', 'Order Type', 'Tracking Link', 'Landing URL', 'Co-reg Limit', 'External Limit', 'Link Out Limit', 'Affiliate Pixel', 'Affiliate Pixel Fires at', 'Header Pixel', 'Body Pixel', 'Footer Pixel', 'Notes'];

                //set up the title header row
                $sheet->appendRow($headers);

                //style the headers
                $sheet->cells('A1:W1', function ($cells) {

                    // Set font
                    $cells->setFont([
                        'size' => '12',
                        'bold' => true,
                    ]);

                    $cells->setAlignment('center');
                    $cells->setValignment('center');
                });

                $path_types = config('constants.PATH_TYPES');
                $order_types = config('constants.PATH_ORDER_TYPE');
                $fire_at = config('constants.PAGE_FIRE_PIXEL');

                foreach ($revenueTrackers as $tracker) {
                    // $landing_url = '';
                    // if($tracker->tracking_link != '') {
                    //     $ch = curl_init();
                    //     curl_setopt($ch, CURLOPT_URL, $tracker->tracking_link);
                    //     curl_setopt($ch, CURLOPT_HEADER, true);
                    //     curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                    //     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    //     $a = curl_exec($ch);
                    //     $landing_url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
                    // }
                    // Log::info($tracker->website.' - '.$landing_url);

                    $sheet->appendRow([
                        $tracker->website,
                        $tracker->affiliate_id,
                        $tracker->campaign_id,
                        $tracker->offer_id,
                        $tracker->revenue_tracker_id,
                        $tracker->s1,
                        $tracker->s2,
                        $tracker->s3,
                        $tracker->s4,
                        $tracker->s5,
                        $subid_status[$tracker->subid_breakdown],
                        isset($path_types[$tracker->path_type]) ? $path_types[$tracker->path_type] : '',
                        isset($order_types[$tracker->order_type]) ? $order_types[$tracker->order_type] : '',
                        $tracker->tracking_link,
                        $tracker->landing_url,
                        $tracker->crg_limit,
                        $tracker->ext_limit,
                        $tracker->lnk_limit,
                        $tracker->pixel,
                        $tracker->fire_at != null && $tracker->fire_at > 0 ? $fire_at[$tracker->fire_at] : '',
                        $tracker->pixel_header,
                        $tracker->pixel_body,
                        $tracker->pixel_footer,
                        $tracker->notes,
                    ]);
                }

            });
        })->store('xls', storage_path('downloads'));

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

    public function getRevenueTrackersWithExitPageDataTable(Request $request): JsonResponse
    {
        $inputs = $request->all();
        // Log::info($inputs);
        $revenueTrackersData = [];

        $columns = [
            '',
            'revenue_tracker_id',
            'affiliate_id',
        ];

        $inputs['order_col'] = $columns[$inputs['order'][0]['column']];
        $inputs['order_dir'] = $inputs['order'][0]['dir'];
        // DB::enableQueryLog();
        $revenueTrackers = AffiliateRevenueTracker::GetRevenueTrackersWithExitPage($inputs, $justForCount = false)
            ->select(DB::raw('affiliate_revenue_trackers.id, affiliate_revenue_trackers.affiliate_id, affiliates.company, affiliate_revenue_trackers.revenue_tracker_id'))
            ->get();

        $totalCount = AffiliateRevenueTracker::GetRevenueTrackersWithExitPage($inputs, true)->count();
        // Log::info(DB::getQueryLog());
        foreach ($revenueTrackers as $tracker) {
            $data = [
                '<input name="revenue_tracker_id[]" type="checkbox" value="'.$tracker->id.'" class="eprt-checkbox">',
                $tracker->revenue_tracker_id,
                $tracker->affiliate_id.' - '.$tracker->company,
            ];

            array_push($revenueTrackersData, $data);
        }

        $responseData = [
            'draw' => intval($inputs['draw']),   // for every request/draw by client side , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            'recordsTotal' => $totalCount,  // total number of records
            'recordsFiltered' => $totalCount, // total number of records after searching, if there is no searching then totalFiltered = totalData
            'data' => $revenueTrackersData,   // total data array
        ];

        return response()->json($responseData, 200);
    }

    public function changeRevTrackerExitPage(Request $request)
    {
        $inputs = $request->all();

        // $cur_exit_page = AffiliateRevenueTracker::whereIn('id', $inputs['revenue_tracker_id'])->pluck('exit_page_id', 'id')->toArray(); //For Logging
        $cur_exit_page = [];
        $rev_tracker_ids = [];
        //For Logging
        $affRevTracks = AffiliateRevenueTracker::whereIn('id', $inputs['revenue_tracker_id'])->select('exit_page_id', 'id', 'revenue_tracker_id')->get();
        foreach ($affRevTracks as $rev_tracker) {
            $cur_exit_page[$rev_tracker->id] = $rev_tracker->exit_page_id;
            $rev_tracker_ids[$rev_tracker->id] = $rev_tracker->revenue_tracker_id;
        }

        $exit_page_id = $inputs['exit_page_id'] == '' ? null : $inputs['exit_page_id'];
        AffiliateRevenueTracker::whereIn('id', $inputs['revenue_tracker_id'])->update(['exit_page_id' => $exit_page_id]);

        //Action Logger
        $value_mask = ['exit_page_id' => Campaign::where('campaign_type', 6)->pluck('name', 'id')];
        foreach ($inputs['revenue_tracker_id'] as $rt_id) {
            $old_value['exit_page_id'] = null;
            if (isset($cur_exit_page[$rt_id])) {
                $old_value['exit_page_id'] = $cur_exit_page[$rt_id];
            }
            $this->logger->log(7, 71, $rev_tracker_ids[$rt_id], null, $old_value, ['exit_page_id' => $exit_page_id], null, $value_mask);
        }

    }
}
