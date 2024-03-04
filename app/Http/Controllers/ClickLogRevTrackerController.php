<?php

namespace App\Http\Controllers;

use App\AffiliateRevenueTracker;
use App\ClickLogRevTrackers;
use App\Http\Services;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClickLogRevTrackerController extends Controller
{
    protected $logger;

    protected $user;

    public function __construct(Services\UserActionLogger $logger)
    {
        //get the user
        $user = auth()->user();

        $this->user = $user;
        $this->logger = $logger;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $inputs = $request->all();

        $param = $inputs['search']['value'];
        $start = $inputs['start'];
        $length = $inputs['length'];

        $columns = [
            'revenue_tracker_id',
            'created_at',
        ];

        $revenueTrackers = ClickLogRevTrackers::searchModel($param, $start, $length, $columns[$inputs['order'][0]['column']], $inputs['order'][0]['dir'])
            ->get();

        $totalFiltered = ClickLogRevTrackers::searchModel($param, null, null, null, null)->count();

        $responseData = [
            'draw' => intval($inputs['draw']),
            'recordsTotal' => ClickLogRevTrackers::count(),
            'recordsFiltered' => $totalFiltered,
            'data' => $revenueTrackers,
        ];

        return response()->json($responseData, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $rev_tracker_id = $request->id;

        $tracker = AffiliateRevenueTracker::find($rev_tracker_id);

        $revenue_tracker_id = $tracker->revenue_tracker_id;

        $clickLogCheck = ClickLogRevTrackers::where('revenue_tracker_id', $revenue_tracker_id)->count();
        if ($clickLogCheck > 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'Source already exists.',
            ], 200);
        } else {
            $source = new ClickLogRevTrackers;
            $source->revenue_tracker_id = $revenue_tracker_id;
            $source->affiliate_id = $tracker->affiliate_id;
            $source->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Source added.',
            ], 200);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $tracker = ClickLogRevTrackers::find($request->input('id'));

        $this->logger->log(74, null, $tracker->revenue_tracker_id, null, $tracker->toArray(), null, [], []);

        $tracker->delete();
    }
}
