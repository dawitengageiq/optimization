<?php

namespace App\Http\Controllers;

use App\Affiliate;
use App\Campaign;
use App\CoregReport;
use Carbon\Carbon;
use DB;
use Email;
use Illuminate\Http\Request;
use Log;
use Maatwebsite\Excel\Facades\Excel;
use PHPExcel_Style_NumberFormat;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CoregReportController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Log::info($request->all());
        $inputs = $request->all();
        $reportsData = [];

        // $param = $inputs['search']['value'];
        $start = $inputs['start'];
        $length = $inputs['length'];

        $columns = [
            'revenue_tracker_id',
            'campaign_id',
            'lf_total',
            'lf_filter_do',
            'lf_admin_do',
            'lf_nlr_do',
            'cost',
            'lr_total',
            'lr_rejected',
            'lr_failed',
            'lr_pending',
            'lr_cap',
            'revenue',
            'we_get',
        ];

        $inputs['lead_date'] = ! isset($inputs['lead_date']) ? Carbon::yesterday()->toDateString() : $inputs['lead_date'];
        $orderCol = $columns[$inputs['order'][0]['column']];
        $orderDir = $inputs['order'][0]['dir'];

        if (! isset($inputs['show_inactive_campaign']) && $inputs['campaign_id'] == '') {
            $inputs['campaign_ids'] = Campaign::where('status', '!=', 0)->pluck('id')->toArray();
        }

        // Log::info($inputs);

        // DB::enableQueryLog();
        $reports = CoregReport::getReport($inputs, $start, $length, $orderCol, $orderDir)
        // ->selectRaw('coreg_reports.*, revenue - cost as we_get, affiliates.company as affiliate_name,CASE WHEN campaigns.name IS NULL THEN CONCAT("OLR Campaign ID: ",campaign_id) ELSE CONCAT(campaigns.name," (",campaign_id,")") END as campaign_name')
            ->selectRaw('coreg_reports.*, revenue - cost as we_get')
            ->get();
        $totalFiltered = CoregReport::getReport($inputs, null, null, null, null)->count();
        // Log::info(DB::getQueryLog());

        $affs = $reports->map(function ($st) {
            return $st->affiliate_id;
        });
        $affiliates = Affiliate::whereIn('id', $affs)->pluck('company', 'id');

        $cmps = $reports->map(function ($st) {
            return $st->campaign_id;
        });
        $campaigns = Campaign::whereIn('id', $cmps)->pluck('name', 'id');

        $inputs['order_col'] = $orderCol;
        $inputs['order_dir'] = $orderDir;
        session()->put('coreg_report_inputs', $inputs);

        $rows = [];
        foreach ($reports as $report) {
            $revCol = '';
            $affiliate_name = $affiliates[$report->affiliate_id];
            $campaign_name = isset($campaigns[$report->campaign_id]) ? $campaigns[$report->campaign_id].'('.$report->campaign_id.')' : 'OLR Campaign ID: '.$report->campaign_id;

            if (($report->olr_only === 0 || $report->olr_only === 1) && $report->revenue_tracker_id === null) {
                $revCol .= 'OLR:<br>';
            }
            if ($report->revenue_tracker_id != '') {
                $revCol .= $report->revenue_tracker_id.'<br>';
            }
            $revCol .= '['.$affiliate_name.'('.$report->affiliate_id.')]';

            $data = [
                $revCol,
                $campaign_name,
                $report->lf_total,
                $report->lf_filter_do,
                $report->lf_admin_do,
                $report->lf_nlr_do,
                sprintf('%.2f', $report->cost),
                $report->lr_total,
                $report->lr_rejected,
                $report->lr_failed,
                $report->lr_pending,
                $report->lr_cap,
                sprintf('%.2f', $report->revenue),
                sprintf('%.2f', $report->we_get),
            ];

            array_push($reportsData, $data);

            $rows[] = $report->source;
        }

        //Get Total Revenue, Cost and Profit
        $total = CoregReport::where('date', $inputs['lead_date'])->selectRaw('SUM(revenue) as revenue, SUM(cost) as cost')->first();
        $total_profit = $total->revenue - $total->cost;

        $responseData = [
            'draw' => intval($inputs['draw']),   // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            'recordsTotal' => CoregReport::count(),  // total number of records
            'recordsFiltered' => $totalFiltered, // total number of records after searching, if there is no searching then totalFiltered = totalData
            'data' => $reportsData,  // total data array
            'date' => Carbon::parse($inputs['lead_date'])->format('m/d/Y'),
            'profit' => sprintf('%0.2f', $total_profit),
            'cost' => sprintf('%0.2f', $total->cost),
            'revenue' => sprintf('%0.2f', $total->revenue),
            'rows' => $rows,
        ];

        return response()->json($responseData, 200);
    }

    /**
     * Create Excel
     *
     * @return \Illuminate\Http\Response
     */
    public function excel()
    {
        $inputs = session()->get('coreg_report_inputs');
        $inputs['excel'] = 1;
        // DB::enableQueryLog();
        $reports = CoregReport::getReport($inputs, null, null, 'we_get', 'desc')
        // ->selectRaw('coreg_reports.*, revenue - cost as we_get, affiliates.company as affiliate_name,CASE WHEN campaigns.name IS NULL THEN CONCAT("OLR Campaign ID: ",campaign_id) ELSE CONCAT(campaigns.name," (",campaign_id,")") END as campaign_name')
            ->selectRaw('coreg_reports.*, revenue - cost as we_get')
            ->get();
        // Log::info(DB::getQueryLog());

        $affs = $reports->map(function ($st) {
            return $st->affiliate_id;
        });
        $affiliates = Affiliate::whereIn('id', $affs)->pluck('company', 'id');

        $cmps = $reports->map(function ($st) {
            return $st->campaign_id;
        });
        $campaigns = Campaign::whereIn('id', $cmps)->pluck('name', 'id');

        $date = Carbon::parse($inputs['lead_date'])->format('m/d/Y');
        $title = 'CoregReport_'.Carbon::parse($inputs['lead_date'])->toDateString();

        Excel::create($title, function ($excel) use ($reports, $inputs, $affiliates, $campaigns) {
            $excel->sheet(Carbon::parse($inputs['lead_date'])->toDateString(), function ($sheet) use ($reports, $affiliates, $campaigns) {

                $totalRows = count($reports) + 3;
                // Set auto size for sheet
                $sheet->setAutoSize(true);

                $sheet->setColumnFormat([
                    'G' => '0.00',
                    'M' => '0.00',
                    'N' => '0.00',
                ]);

                /* TITLE ROW */
                $sheet->appendRow(['Net Profit', '', 'Lead Filter', '', '', '', '', 'Lead Reactor', '', '', '', '', '', '']);
                $sheet->mergeCells('A1:B1');
                $sheet->mergeCells('C1:G1');
                $sheet->mergeCells('H1:M1');
                $sheet->cells('A1:N1', function ($cells) {
                    // Set font
                    $cells->setFont([
                        'size' => '13',
                        'bold' => true,
                    ]);

                    $cells->setAlignment('center');
                    $cells->setValignment('center');
                });

                /* TOTAL ROW */
                $sheet->appendRow([
                    '=SUM(N4:N'.$totalRows.')', //Profit
                    '',
                    '=SUM(G4:G'.$totalRows.')', //Cost
                    '',
                    '',
                    '',
                    '',
                    '=SUM(M4:M'.$totalRows.')', //Revenue
                    '',
                    '',
                    '',
                    '',
                    '',
                    '', //We GET
                ]);
                $sheet->mergeCells('A2:B2');
                $sheet->mergeCells('C2:G2');
                $sheet->mergeCells('H2:M2');
                $sheet->cells('A2:N2', function ($cells) {
                    // Set font
                    $cells->setFont([
                        'size' => '12',
                        'bold' => true,
                    ]);

                    $cells->setAlignment('center');
                    $cells->setValignment('center');
                });

                // $sheet->setColumnFormat([
                //     'K' => PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_USD
                // ]);

                /* HEADER ROW */
                $headers = ['Rev Tracker', 'Campaign', 'Leads Received', 'Filter Drop Off', 'Admin Drop Off', 'NLR Drop Off', 'Cost', 'Leads Received', 'Rejected Drop Off', 'Failed Drop Off', 'Pending/Queued Drop Off', 'Cap Reached Drop Off', 'Revenue', 'We Get'];
                //set up the title header row
                $sheet->appendRow($headers);
                //style the headers
                $sheet->cells('A3:N3', function ($cells) {
                    // Set font
                    $cells->setFont([
                        'size' => '12',
                        'bold' => true,
                    ]);

                    $cells->setAlignment('center');
                    $cells->setValignment('center');
                });

                $row_num = 4; //first row num is 4
                foreach ($reports as $report) {
                    $revCol = '';
                    $affiliate_name = $affiliates[$report->affiliate_id];
                    $campaign_name = isset($campaigns[$report->campaign_id]) ? $campaigns[$report->campaign_id].'('.$report->campaign_id.')' : 'OLR Campaign ID: '.$report->campaign_id;

                    // if(($report->olr_only === 0 || $report->olr_only === 1) && $report->revenue_tracker_id === null) $revCol .= 'OLR: ';

                    if ($report->revenue_tracker_id != '') {
                        $revCol .= $report->revenue_tracker_id.' ';
                    }
                    $revCol .= '['.$affiliate_name.'('.$report->affiliate_id.')]';

                    $sheet->appendRow([
                        $revCol,
                        $campaign_name,
                        $report->lf_total,
                        $report->lf_filter_do,
                        $report->lf_admin_do,
                        $report->lf_nlr_do,
                        $report->cost,
                        $report->lr_total,
                        $report->lr_rejected,
                        $report->lr_failed,
                        $report->lr_pending,
                        $report->lr_cap,
                        $report->revenue,
                        $report->we_get,
                    ]);

                    //Where Source: 1 = OLR; 2 = NLR; 3 = Mixed; 4 = LF
                    $row_color = '#fff';
                    switch ($report->source) {
                        case 1:
                            $row_color = '#dff0d8';
                            break;
                        case 2:
                            $row_color = '#f2dede';
                            break;
                        case 4:
                            $row_color = '#D9EDF7';
                            break;
                        default:break;
                    }

                    // Set black background
                    $sheet->row($row_num++, function ($row) use ($row_color) {
                        $row->setBackground($row_color);
                    });
                }
            });
        })->store('xlsx', storage_path('downloads'));

        $file_path = storage_path('downloads').'/'.$title.'.xlsx';

        // Log::info($file_path);
        if (file_exists($file_path)) {
            return response()->download($file_path, $title.'.xlsx', [
                'Content-Length: '.filesize($file_path),
            ]);
        } else {
            exit('Requested file does not exist on our server!');
        }
    }

    /**
     * Download Excel Report
     */
    public function download(): BinaryFileResponse
    {
        $title = 'CoregReport_'.Carbon::yesterday()->toDateString();
        $file_path = storage_path('downloads').'/'.$title.'.xlsx';

        // $this->excel(); //for testing
        if (! file_exists($file_path)) {
            $this->excel();
        }

        return response()->download($file_path, $title.'.xlsx', [
            'Content-Length: '.filesize($file_path),
        ]);
    }

    /**
     * Email Report
     *
     * @return \Illuminate\Http\Response
     */
    public function email()
    {
        $date = Carbon::yesterday()->format('m/d/Y');
        $title = 'CoregReport_'.Carbon::yesterday()->toDateString();
        $file_path = storage_path('downloads').'/'.$title.'.xls';

        $excelAttachment = $filePath;

        if (! file_exists($file_path)) {
            $this->excel();
        }

        Mail::send('emails.campaign', [], function ($message) use ($excelAttachment, $lr_build) {
            $message->from('admin@engageiq.com', 'Automated Report by '.$lr_build);
            $message->to('monty@engageiq.com', 'Monty Magbanua')->to('karla@engageiq.com', 'Karla Librero')->to('dexter@engageiq.com', 'Dexter Cadia')->to('johneil@engageiq.com', 'Johneil Quijano')->subject('High Rejection Report for Testing');
            $message->attach($excelAttachment);
        });
    }
}
