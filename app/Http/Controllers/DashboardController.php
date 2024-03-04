<?php

namespace App\Http\Controllers;

use App\CampaignRevenueBreakdown;
use App\OfferGoesDown;
use App\PathSpeed;
use App\Setting;
use Cache;
use Carbon\Carbon;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Log;

class DashboardController extends Controller
{
    public function offerGoesDownStats(Request $request): JsonResponse
    {
        $inputs = $request->all();
        // Log::info($inputs);

        $start = $inputs['start'];
        $length = $inputs['length'];

        $filter = isset($inputs['filter']) && $inputs['filter'] != '' ? $inputs['filter'] : 30;

        $totalRecords = OfferGoesDown::where('revenue', '>', 0)
            ->where('revenue', '>=', $filter)
            ->count();

        $campaigns = OfferGoesDown::leftJoin('campaigns', 'campaigns.id', '=', 'offer_goes_downs.campaign_id')
            ->select(DB::RAW('offer_goes_downs.*, campaigns.name'))
            ->where('revenue', '>', 0)
            ->where('revenue', '>=', $filter)
            ->orderBy('updated_at', 'DESC')
            ->skip($start)->take($length)->get();
        $campaignsData = [];

        foreach ($campaigns as $campaign) {
            $affs = explode(',', $campaign->affiliates);
            if (count($affs) <= 3) {
                $affData = str_replace(',', ', ', $campaign->affiliates);
            } else {
                $morBtn = '<a href="javascript:;"data-container="body" data-toggle="popover" data-placement="bottom" data-content="'.str_replace(',', ', ', str_replace('All,', '', $campaign->affiliates)).'">...</a>';
                $display = $affs[0] == 'All' ? $affs[0] : $affs[0].', '.$affs[1].', '.$affs[2];
                $affData = $display.' '.$morBtn;
            }
            $data = [
                $campaign->date,
                $campaign->name,
                $affData,
                number_format($campaign->revenue, 2),
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

    public function campaignRevenueBreakdown(Request $request, $id): JsonResponse
    {
        // Log::info($id);
        $inputs = $request->all();

        $columns = [
            'created_at',
            'revenue',
            'records',
            'average_revenue',
        ];

        $start = $inputs['start'];
        $length = $inputs['length'];
        // DB::enableQueryLog();
        $totalRecords = CampaignRevenueBreakdown::where('campaign_id', $id)->count();
        $campaigns = CampaignRevenueBreakdown::where('campaign_id', $id)
            ->orderBy($columns[$inputs['order'][0]['column']], $inputs['order'][0]['dir'])
            ->skip($start)->take($length)->get();
        // DB::getQueryLog();
        $campaignsData = [];

        foreach ($campaigns as $campaign) {
            $data = [
                $campaign->created_at,
                number_format($campaign->revenue, 2),
                $campaign->records,
                number_format($campaign->average_revenue, 2),
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

    public function pathSpeed(Request $request, $path): JsonResponse
    {
        // if(Cache::has('monitisPaths')) {
        //    $paths = Cache::get('monitisPaths');
        // }else {
        //     $setting = Setting::where('code', 'monitis_path_ids')->first();
        //     $paths = json_decode($setting->description, true);
        //     $expiresAt = Carbon::now()->addMinutes(25);
        //     Cache::put('monitisPaths', $paths, $expiresAt);
        // }

        $inputs = $request->all();

        $columns = [
            'created_at',
            'up_time',
            'sum_time',
            'avg_time',
        ];

        $start = $inputs['start'];
        $length = $inputs['length'];
        // DB::enableQueryLog();
        $totalRecords = PathSpeed::where('path', $path)->count();
        $stats = PathSpeed::where('path', $path)
            ->orderBy($columns[$inputs['order'][0]['column']], $inputs['order'][0]['dir'])
            ->skip($start)->take($length)->get();
        // DB::getQueryLog();
        $data = [];

        foreach ($stats as $stat) {
            array_push($data, [
                Carbon::parse($stat->created_at)->toDateString(),
                number_format($stat->up_time).' %',
                $stat->sum_time,
                number_format($stat->avg_time),
            ]);
        }

        $responseData = [
            'draw' => intval($inputs['draw']),   // for every request/draw by client side , they send a number as a parameter, when they receive a response/data they first check the draw number, so we are sending same number in draw.
            'recordsTotal' => $totalRecords,  // total number of records
            'recordsFiltered' => $totalRecords, // total number of records after searching, if there is no searching then totalFiltered = totalData
            'data' => $data,   // total data array
        ];

        return response()->json($responseData, 200);
    }
}
