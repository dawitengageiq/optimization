<?php

namespace App\Http\Controllers;

use App\LeadUserBanned;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeadUserBannedController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        // \Log::info($request->all());
        // \DB::connection('secondary')->enableQueryLog();
        $inputs = $request->all();
        $revenueTrackersData = [];

        $start = $inputs['start'];
        $length = $inputs['length'];

        $columns = [
            'id',
            'first_name',
            'last_name',
            'email',
            'phone',
        ];

        $banned = LeadUserBanned::searchLeads($inputs, $start, $length, $columns[$inputs['order'][0]['column']], $inputs['order'][0]['dir'])
            ->get();
        // \Log::info(\DB::connection('secondary')->getQueryLog());
        $totalFiltered = LeadUserBanned::searchLeads($inputs, null, null, null, null)->count();

        $data = [];
        foreach ($banned as $b) {
            $details = '<textarea id="bnd-'.$b->id.'-details" class="hidden">'.json_encode($b).'</textarea>';
            $details .= '<button class="editBanned btn-actions btn btn-primary" title="Edit" data-id="'.$b->id.'"><span class="glyphicon glyphicon-pencil"></span></button>';
            $details .= '<button class="deleteBanned btn-actions btn btn-danger" title="Delete" data-id="'.$b->id.'"><span class="glyphicon glyphicon-trash"></span></button>';

            array_push($data, [
                $b->id,
                $b->first_name,
                $b->last_name,
                $b->email,
                $b->phone,
                $details,
            ]);
        }

        $responseData = [
            'draw' => intval($inputs['draw']),   // for every request/draw by client side , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            'recordsTotal' => LeadUserBanned::count(),  // total number of records
            'recordsFiltered' => $totalFiltered, // total number of records after searching, if there is no searching then totalFiltered = totalData
            'data' => $data,   // total data array
        ];

        return response()->json($responseData, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $this->validate($request, [
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required_without_all:phone|email',
            'phone' => 'required_without_all:email|numeric',
        ]);

        $lead = new LeadUserBanned;
        $lead->first_name = $request->first_name;
        $lead->last_name = $request->last_name;
        $lead->email = $request->email;
        $lead->phone = $request->phone;
        $lead->save();

        $responseData = [
            'id' => $lead->id,
        ];

        return response()->json($responseData, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $this->validate($request, [
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'email|required_without_all:phone',
            'phone' => 'numeric|required_without_all:email',
        ]);

        $lead = LeadUserBanned::find($request->id);
        $lead->first_name = $request->first_name;
        $lead->last_name = $request->last_name;
        $lead->email = $request->email;
        $lead->phone = $request->phone;
        $lead->save();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $lead = LeadUserBanned::find($request->id);
        $lead->delete();
    }
}
