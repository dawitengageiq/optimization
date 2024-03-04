<?php

namespace App\Http\Controllers;

use App\LeadDataAdvArchive;
use App\LeadDataCsvArchive;
use App\LeadMessageArchive;
use App\LeadSentResultArchive;
use Illuminate\Http\JsonResponse;

class LeadArchiveController extends Controller
{
    /**
     * Function of getting the details
     */
    public function getLeadDetails($lead_id): JsonResponse
    {
        $data = [];
        $data['leadDataADV'] = null;
        $data['leadDataCSV'] = null;
        $data['leadMessage'] = null;
        $data['leadSentResult'] = null;

        $leadDataADV = LeadDataAdvArchive::select('id', 'value')->where('id', '=', $lead_id)->first();
        $leadDataCSV = LeadDataCsvArchive::select('id', 'value')->where('id', '=', $lead_id)->first();
        $leadMessage = LeadMessageArchive::select('id', 'value')->where('id', '=', $lead_id)->first();
        $leadSentResult = LeadSentResultArchive::select('id', 'value')->where('id', '=', $lead_id)->first();

        if ($leadDataADV !== null) {
            $data['leadDataADV'] = $leadDataADV;
        }

        if ($leadDataCSV !== null) {
            $data['leadDataCSV'] = $leadDataCSV;
        }

        if ($leadMessage !== null) {
            $data['leadMessage'] = $leadMessage;
        }

        if ($leadSentResult !== null) {
            $data['leadSentResult'] = $leadSentResult;
        }

        return response()->json($data);
    }
}
