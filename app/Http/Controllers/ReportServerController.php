<?php

namespace App\Http\Controllers;

use App\Campaign;
use App\Lead;
use App\LeadArchive;
use Carbon\Carbon;
use DB;
use Excel;
use Illuminate\Http\Request;

class ReportServerController extends Controller
{
    public function downloadSearchedLeadsAdvertiserData(Request $request)
    {
        // DB::enableQueryLog();
        $inputs = $request->all();
        $leads = [];
        // Log::info($inputs);
        $campaign = Campaign::find($inputs['campaign_id']);
        $leadStatus = config('constants.LEAD_STATUS');

        if (count($inputs) > 0) {
            if ((isset($inputs['table']) && $inputs['table'] == 'leads')) {
                // $leads = Lead::searchLeads($inputs)->with('leadMessage','leadDataADV','leadDataCSV','leadSentResult')->get();

                $leads = Lead::searchLeads($inputs)
                    // ->join('campaigns','leads.campaign_id','=','campaigns.id')
                    ->leftJoin('lead_data_advs', 'leads.id', '=', 'lead_data_advs.id')
                    // ->leftJoin('lead_data_csvs','leads.id','=','lead_data_csvs.id')
                    // ->leftJoin('lead_messages','leads.id','=','lead_messages.id')
                    // ->leftJoin('lead_sent_results','leads.id','=','lead_sent_results.id')
                    // ->select(DB::RAW("leads.*, campaigns.name, TIMEDIFF(leads.updated_at,leads.created_at) as time_interval, lead_data_advs.value as advs_val, lead_data_csvs.value as csvs_val, lead_messages.value as msg_val, lead_sent_results.value as sntr_val"))
                    ->select(DB::RAW('leads.*, lead_data_advs.value as advs_val'))
                    ->get();

            } else {
                // $leads = LeadArchive::searchLeads($inputs)->with('leadMessage','leadDataADV','leadDataCSV','leadSentResult')->get();

                $leads = LeadArchive::searchLeads($inputs)
                    // ->join('campaigns','leads_archive.campaign_id','=','campaigns.id')
                    ->leftJoin('lead_data_advs_archive', 'leads_archive.id', '=', 'lead_data_advs_archive.id')
                    ->select(DB::RAW('leads_archive.*, lead_data_advs_archive.value as advs_val'))
                    // ->leftJoin('lead_data_csvs_archive','leads_archive.id','=','lead_data_csvs_archive.id')
                    // ->leftJoin('lead_messages_archive','leads_archive.id','=','lead_messages_archive.id')
                    // ->leftJoin('lead_sent_results_archive','leads_archive.id','=','lead_sent_results_archive.id')
                    // ->select(DB::RAW("leads_archive.*, campaigns.name, TIMEDIFF(leads_archive.updated_at,leads_archive.created_at) as time_interval, lead_data_advs_archive.value as advs_val, lead_data_csvs_archive.value as csvs_val, lead_messages_archive.value as msg_val, lead_sent_results_archive.value as sntr_val"))
                    ->get();
            }
        }

        $title = 'SearchedLeadsAdvertiserData_'.Carbon::now()->toDateString();

        $data = [];
        $params = [];
        foreach ($leads as $lead) {

            if ($lead->advs_val != '') {
                $parts = parse_url($lead->advs_val);
                $query = [];

                if (isset($parts['query'])) {
                    parse_str($parts['query'], $query);
                }

                if (count($query) > 0) {
                    foreach ($query as $key => $val) {
                        if (! in_array($key, $params)) {
                            $params[] = $key;
                        }
                    }

                    $data[] = array_merge(['LEADID' => $lead->id, 'LEADSTATUS' => $leadStatus[$lead->lead_status], 'LEADDATE' => $lead->created_at], $query);
                }

                // \Log::info($query);
                // \Log::info('------');
            }
        }

        // \Log::info('***********************');
        // \Log::info($params);
        // \Log::info('***********************');
        // \Log::info($data);

        // return $leads;

        Excel::create($title, function ($excel) use ($params, $data, $campaign) {
            $header = $campaign->name;

            if (strlen($header) > 31) {
                $header = substr($header, 0, 28);
                $header .= '...';
            }
            $excel->sheet($header, function ($sheet) use ($params, $data) {

                // Set auto size for sheet
                $sheet->setAutoSize(true);

                $headers = array_merge(['Lead ID', 'Status', 'Lead Date'], $params);

                //set up the title header row
                $sheet->appendRow($headers);

                $sheet->row(1, function ($row) {

                    // Set font
                    $row->setFont([
                        'size' => '12',
                        'bold' => true,
                    ]);

                    $row->setAlignment('center');
                    $row->setValignment('center');

                });

                // //style the headers
                // $sheet->cells('A1:U1', function($cells) {

                //     // Set font
                //     $cells->setFont(array(
                //         'size'       => '12',
                //         'bold'       =>  true
                //     ));

                //     $cells->setAlignment('center');
                //     $cells->setValignment('center');
                // });

                foreach ($data as $d) {
                    $row = [$d['LEADID'], $d['LEADSTATUS'], $d['LEADDATE']];
                    foreach ($params as $p) {
                        $row[] = isset($d[$p]) ? $d[$p] : '';
                    }

                    $sheet->appendRow($row);
                }
            });
        })->store('xls', storage_path('downloads'));

        $file_path = storage_path('downloads').'/'.$title.'.xls';

        if (file_exists($file_path)) {
            return response()->download($file_path, $title.'.xls', [
                'Content-Length: '.filesize($file_path),
            ]);
        } else {
            exit('Requested file does not exist on our server!');
        }
    }
}
