<?php

namespace App\Http\Services;

use App\Lead;
use App\LeadArchive;
use Carbon\Carbon;
use Excel;
use File;

class Creative
{
    /**
     * Load the  needed configuration
     */
    public function __construct()
    {
        //
    }

    /*
     * Fetch the statistics for creative per requests
     * The data will be used for datatables
     *
     * @return Json
     */
    public function getStatistics($request)
    {
        // Request parameters
        $inputs = $request->all();
        session()->put('revenue_leads_input', $inputs);

        // Get all Leads
        $allLeads = $this->allLeads($inputs);
        //$leads = $allLeads->skip($inputs['start'])->take($inputs['length'])->get();
        $leads = $allLeads->get();

        /* FOR COUNT */
        $leadsCount = count($allLeads->get());

        // Pre Variables
        $leadData = [];
        $totalLeadCount = $totalCost = $totalRevenue = $totalProfit = 0;
        foreach ($leads as $lead) {
            //data for datatable
            array_push($leadData, $this->setData($lead, $this->groupByColumnCaseData($lead, $request->input('group_by_column'))));

            $totalLeadCount += $lead->lead_count;
            $totalCost += $lead->lead_status == 1 ? $lead->cost : 0;
            $totalRevenue += $lead->lead_status == 1 ? $lead->revenue : 0;
            $totalProfit += $lead->lead_status == 1 ? $lead->revenue - $lead->cost : 0;

        }

        return response()->json([
            'draw' => intval($inputs['draw']),   // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            'recordsTotal' => $leadsCount,  // total number of records
            'recordsFiltered' => $leadsCount, // total number of records after searching, if there is no searching then totalFiltered = totalData
            'data' => $leadData,   // total data array
            'totalLeadCount' => $totalLeadCount,
            'totalCost' => $totalCost,
            'totalRevenue' => $totalRevenue,
            'totalProfit' => $totalProfit,
        ], 200);
    }

    public function getReport($request)
    {
        // Request parameters
        $inputs = session()->get('revenue_leads_input');

        // Get all Leads
        $leads = $this->allLeads($inputs)->get();

        // Excel file name
        $title = 'RevenueLeads_'.Carbon::now()->toDateString();

        // Create
        Excel::create($title, function ($excel) use ($title, $leads) {

            $excel->sheet($title, function ($sheet) use ($leads) {

                // Set auto size for sheet
                $sheet->setAutoSize(true);

                //set up the title header row
                $sheet->appendRow(['Lead ID', 'Campaign', 'Affiliate', 'Lead Email', 'Received', 'Payout', 'Lead Date', 'Lead Updated', 'Time Interval', 'Status']);

                //style the headers
                $sheet->cells('A1:O1', function ($cells) {
                    // Set font
                    $cells->setFont([
                        'size' => '12',
                        'bold' => true,
                    ]);

                    $cells->setAlignment('center');
                    $cells->setValignment('center');
                });

                foreach ($leads as $lead) {
                    $sheet->appendRow($this->appendOnSheetRow($lead));
                }
            });
        })->store('xls', storage_path('downloads'));

        $filepath = storage_path('downloads').'/'.$title.'.xls';

        if (file_exists($filepath)) {
            return response()->download($filepath, $title.'.xls', ['Content-Length: '.filesize($filepath)]);
        }

        exit('Requested file does not exist on our server!');
    }

    private function appendOnSheetRow($lead)
    {

        $campaign = $lead->campaign;

        $date1 = new \DateTime($lead->created_at);
        $date2 = new \DateTime($lead->updated_at);
        $interval = $date1->diff($date2);

        return [
            $lead->id,
            $campaign->name.'('.$campaign->id.')',
            $lead->affiliate_id,
            $lead->lead_email,
            $lead->received,
            $lead->payout,
            $lead->created_at,
            $lead->updated_at,
            $interval->format('%H:%I:%S'),
        ];
    }

    /*
     * Fetch all leads
     *
     * @return Object $allLeads
     */
    private function allLeads($inputs)
    {
        if (isset($inputs['table'])) {
            if ($inputs['table'] == 'leads') {
                return Lead::getRevenueStats($inputs);
            }

            return LeadArchive::getRevenueStats($inputs);
        }

        return Lead::getRevenueStats($inputs);
    }

    /*
     * Set the data by group
     *
     * @return Array $return
     */
    public function groupByColumnCaseData($lead, $case)
    {
        $return = [
            'campaign_id' => '',
            'creative_id' => '',
            'affiliate_id' => '',
        ];

        switch ($case) {
            case 'campaign':
                $return['campaign_id'] = $lead->campaign_name;
                break;
            case 'creative_id':
                $return['creative_id'] = $lead->creative_id;
                break;
            case 'affiliate':
                $return['affiliate_id'] = $lead->affiliate_id;
                break;
            default:
                $return['campaign_id'] = $lead->campaign_name;
                $return['creative_id'] = $lead->creative_id;
                $return['affiliate_id'] = $lead->affiliate_id;
                $return['s1'] = $lead->s1;
                $return['s2'] = $lead->s2;
                $return['s3'] = $lead->s3;
                $return['s4'] = $lead->s4;
                $return['s5'] = $lead->s5;
                break;
        }

        return $return;
    }

    /*
     * Set the variables needed to an array
     *
     * @return Array $data
     */
    private function setData($lead, $groupByColumnData)
    {
        $data = [
            $lead->lead_date,
            $groupByColumnData['campaign_id'],
            $groupByColumnData['creative_id'],
            $groupByColumnData['affiliate_id'],
            $lead->lead_count,
            $lead->lead_status == 1 ? $lead->cost : 0,
            $lead->lead_status == 1 ? $lead->revenue : 0,
            $lead->lead_status == 1 ? $lead->revenue - $lead->cost : 0,
        ];

        return $data;
    }
}
