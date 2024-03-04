<?php

namespace App\Http\Controllers;

use App\Affiliate;
use App\AffiliateRevenueTracker;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Log;

/**
 * This Controller is primarily used as ajax remote data provider for Select2 select drop downs.
 *
 * Class SearchController
 */
class SearchController extends Controller
{
    /**
     * Display a listing of the searched resource (revenue trackers).
     */
    public function activeRevenueTrackers(Request $request): JsonResponse
    {
        $inputs = $request->all();

        $term = isset($inputs['term']) ? $inputs['term'] : '';

        $revenueTrackers = Affiliate::select('id', DB::raw('CONCAT(company," (",id,") ") AS name'))
            ->where('status', 1)
            ->where('type', 1)
            ->where(function ($query) use ($term) {

                $query->where('company', 'LIKE', "%$term%")
                    ->orWhere('id', '=', $term);

            })
            ->orderBy('company')
            ->get();

        $responseData = [
            'items' => $revenueTrackers,
        ];

        return response()->json($responseData, 200);
    }

    /**
     * Display a listing of the searched resource (active affiliates).
     */
    public function activeAffiliates(Request $request): JsonResponse
    {
        $inputs = $request->all();
        $term = isset($inputs['term']) ? $inputs['term'] : '';

        $activeAffiliates = Affiliate::select('id', DB::raw('CONCAT(company," (",id,") ") AS name'))
            ->where('status', 1)
            ->where(function ($query) use ($term) {
                $query->where('company', 'LIKE', "%$term%")
                    ->orWhere('id', '=', $term);
            })
            ->orderBy('name', 'asc')
            ->get();

        $responseData = [
            'items' => $activeAffiliates,
        ];

        return response()->json($responseData, 200);
    }

    /**
     * Display a listing of the searched resource (active affiliates).
     */
    public function activeAffiliatesIDName(Request $request): JsonResponse
    {
        $inputs = $request->all();
        $term = isset($inputs['term']) ? $inputs['term'] : '';

        $activeAffiliates = Affiliate::select('id', DB::raw('CONCAT(id, " - ", company) AS name'))
            ->where('status', 1)
            ->where(function ($query) use ($term) {
                $query->where('company', 'LIKE', "%$term%")
                    ->orWhere('id', '=', $term);
            })
            ->orderBy('name', 'asc')
            ->get();

        $responseData = [
            'items' => $activeAffiliates,
        ];

        return response()->json($responseData, 200);
    }

    public function activeAffiliate($id)
    {
        /*
        $affiliate = Affiliate::find($id);

        $responseData = [
        ];
        */
    }

    public function campaignAffiliate(Request $request): JsonResponse
    {
        $inputs = $request->all();
        $term = isset($inputs['term']) ? $inputs['term'] : '';
        $campaign = $inputs['campaign'];

        // DB::enableQueryLog();
        $availableAffiliates =
          Affiliate::leftJoin('affiliate_campaign', function ($join) use ($campaign) {
              $join->on('affiliates.id', '=', 'affiliate_campaign.affiliate_id')
                  ->where('affiliate_campaign.campaign_id', '=', $campaign);
          })->whereNull('affiliate_campaign.id')
              ->where(function ($query) use ($term) {
                  $query->where('company', 'LIKE', "%$term%")
                      ->orWhere('affiliates.id', '=', $term);
              })
              ->select('affiliates.id', DB::raw('CONCAT(affiliates.id, " - ",company) AS name'))
              ->get()->toArray();

        if (strtolower($term) == 'all') {
            array_unshift($availableAffiliates, ['id' => 'ALL', 'name' => 'ALL AVAILABLE AFFILIATES']);
        }
        // Log::info(DB::getQueryLog());
        // Log::info($availableAffiliates);

        $responseData = [
            'items' => $availableAffiliates,
        ];

        return response()->json($responseData, 200);
    }

    public function getRevenueTrackers(Request $request): JsonResponse
    {
        $inputs = $request->all();
        $term = isset($inputs['term']) ? $inputs['term'] : '';

        $actives = AffiliateRevenueTracker::select('id', 'revenue_tracker_id')
            ->where('revenue_tracker_id', 'LIKE', '%'.$term.'%')
            ->orderBy('revenue_tracker_id', 'asc')
            ->get();

        $responseData = [
            'items' => $actives,
        ];

        return response()->json($responseData, 200);
    }

    public function getAvailableRevenueTrackersForExitPage(Request $request): JsonResponse
    {
        $inputs = $request->all();
        // Log::info($inputs);
        $term = isset($inputs['term']) ? $inputs['term'] : '';
        $exit_page = $inputs['exit_page_id'] == '' ? null : $inputs['exit_page_id'];
        $selected = isset($inputs['selected']) ? $inputs['selected'] : null;
        // DB::enableQueryLog();
        $actives = AffiliateRevenueTracker::getAvailableRevTrackersForExitPage($exit_page, $term, $selected)
            ->get();
        // Log::info(DB::getQueryLog());
        $responseData = [
            'items' => $actives,
        ];

        return response()->json($responseData, 200);
    }
}
