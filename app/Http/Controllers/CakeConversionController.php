<?php

namespace App\Http\Controllers;

use App\CakeConversion;
use Illuminate\Http\Request;

class CakeConversionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return array
     */
    public function index(Request $request)
    {
        $inputs = $request->all();
        $param = $inputs['search']['value'];
        $start = $inputs['start'];
        $length = $inputs['length'];

        $conversions = CakeConversion::all();
        $totalFiltered = $conversions->count();
        $conversionsData = [];

        $cakeConversions = CakeConversion::searchCakeConversions($inputs);
        $cakeConversionsWithLimit = $cakeConversions;

        if ($length > 1) {
            $cakeConversionsWithLimit = $cakeConversionsWithLimit->take($length)->skip($start);
        }

        $cakeConversionsWithLimit = $cakeConversionsWithLimit->get();

        foreach ($cakeConversionsWithLimit as $conversion) {
            $data = [];

            //create the id HTML
            $idHTML = '<span id="cnv-'.$conversion->id.'-id">'.$conversion->id.'</span>';
            array_push($data, $idHTML);

            //create the offer id HTML
            $offerIDHTML = '<span id="cnv-'.$conversion->id.'-offer_id">'.$conversion->offer_id.'</span>';
            array_push($data, $offerIDHTML);

            //create the offer name HTML
            $offerNameHTML = '<span id="cnv-'.$conversion->id.'-offer_name">'.$conversion->offer_name.'</span>';
            array_push($data, $offerNameHTML);

            //create the campaign id HTML
            $campaignIDHTML = '<span id="cnv-'.$conversion->id.'-campaign_id">'.$conversion->campaign_id.'</span>';
            array_push($data, $campaignIDHTML);

            //create the sub id 5
            $subID = '<span id="cnv-'.$conversion->id.'-sub_id_5" class=".fix-width-cell">'.$conversion->sub_id_5.'</span>';
            array_push($data, $subID);

            //create the conversion date HTML
            $conversionDate = '<span id="cnv-'.$conversion->id.'-conversion_date">'.$conversion->conversion_date.'</span>';
            array_push($data, $conversionDate);

            //create action button HTML
            $allDetails = htmlspecialchars(json_encode($conversion));
            $viewAllDetails = '<button class="btn btn-primary glyphicon glyphicon-info-sign all-details-button" data-details="'.$allDetails.'" title="View all the details"></button><br>';
            array_push($data, $viewAllDetails);

            array_push($conversionsData, $data);
        }

        if (! empty($param) || $param != '') {
            $totalFiltered = $cakeConversions->count();
        }

        $responseData = [
            'draw' => intval($inputs['draw']),   // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            'recordsTotal' => $conversions->count(),  // total number of records
            'recordsFiltered' => $totalFiltered, // total number of records after searching, if there is no searching then totalFiltered = totalData
            'data' => $conversionsData,   // total data array
        ];

        return $responseData;
    }

    /**
     * @return mixed
     */
    public function getConversionsEmailOfferID(Request $request)
    {
        $inputs = $request->all();

        return CakeConversion::findConversionsEmailOfferID($inputs)->get();
    }

    public function getConversionsByAffiliateOfferS4(Request $request)
    {
        $inputs = $request->all();

        return CakeConversion::findConversionsAffiliateOfferS4($inputs)->get();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show(int $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(int $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, int $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(int $id)
    {
        //
    }
}
