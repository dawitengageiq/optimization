<?php

namespace App\Http\Controllers;

use App\ZipCode;
use Cache;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Log;

class ZipCodeController extends Controller
{
    /**
     * DataTable server side get/post function
     */
    public function index(Request $request): JsonResponse
    {
        $inputs = $request->all();
        // Log::info($inputs);
        $recordsTotal = Cache::remember('zipListCount', 60 * 24 * 60, function () {
            return ZipCode::count();
        });

        $totalFiltered = $recordsTotal;
        $zipMasterData = [];

        $param = $inputs['search']['value'];
        $start = $inputs['start'];
        $length = $inputs['length'];

        $columns = [
            0 => 'zip',
            1 => 'city',
            2 => 'state',
        ];

        $selectSQL = 'SELECT zip, city, state FROM zip_codes ';
        $whereSQL = '';

        //where
        if (! empty($param)) {
            $whereSQL = $whereSQL."WHERE zip LIKE '%".$param."%'";
            $whereSQL = $whereSQL." OR city LIKE '%".$param."%'";
            $whereSQL = $whereSQL." OR state LIKE '%".$param."%'";
        }

        $orderSQL = ' ORDER BY '.$columns[$inputs['order'][0]['column']].' '.$inputs['order'][0]['dir'];

        $limitSQL = '';
        if ($length > 1) {
            $limitSQL = ' LIMIT '.$start.','.$length;
        }

        $sqlWithLimit = $selectSQL.$whereSQL.$orderSQL.$limitSQL;
        $sqlWithoutLimit = $selectSQL.$whereSQL.$orderSQL;

        // Log::info('SQL with limit: '.$sqlWithLimit);
        // Log::info('SQL without limit: '.$sqlWithoutLimit);

        $zips = DB::select($sqlWithLimit);

        foreach ($zips as $zip) {
            $data = [];

            //create the zip html
            $zipHTML = '<span id="zip-'.$zip->zip.'-code">'.$zip->zip.'</span>';
            array_push($data, $zipHTML);

            //create the zip html
            $cityHTML = '<span id="zip-'.$zip->zip.'-city">'.$zip->city.'</span>';
            array_push($data, $cityHTML);

            //create the state html
            $stateHTML = '<span id="zip-'.$zip->zip.'-state">'.$zip->state.'</span>';
            array_push($data, $stateHTML);

            array_push($zipMasterData, $data);
        }

        if (! empty($param) || $param != '') {
            //$totalFiltered = count($affiliatesData);
            $totalFiltered = count(DB::select($sqlWithoutLimit));
        }

        $responseData = [
            'draw' => intval($inputs['draw']),   // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            'recordsTotal' => $recordsTotal,  // total number of records
            'recordsFiltered' => $totalFiltered, // total number of records after searching, if there is no searching then totalFiltered = totalData
            'data' => $zipMasterData,   // total data array
        ];

        return response()->json($responseData, 200);
    }
}
