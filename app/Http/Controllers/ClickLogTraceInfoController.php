<?php

namespace App\Http\Controllers;

use App\ClickLogTraceInfo;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Excel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ClickLogTraceInfoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('admin.click_logs_report');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function get(Request $request): JsonResponse
    {
        $inputs = $request->all();
        // \Log::info($inputs);
        // \DB::enableQueryLog();
        session()->put('click_log_trace_info_input', $inputs);

        $start = $inputs['start'];
        $length = $inputs['length'];

        $columns = [
            'id',
            'click_date',
            'click_id',
            'email',
            'affiliate_id',
            'revenue_tracker_id',
            'ip',
            'is_dbprepoped',
            'reg_count',
            'first_entry_rev_id',
            'last_entry_rev_id',
        ];
        //Date From and To
        if (isset($inputs['date_range']) && $inputs['date_range'] != '') {
            //Date Range
            switch ($inputs['date_range']) {
                case 'yesterday':
                    $inputs['date_from'] = Carbon::yesterday()->toDateString();
                    $inputs['date_to'] = Carbon::yesterday()->toDateString();
                    break;
                case 'week':
                    $inputs['date_from'] = Carbon::now()->startOfWeek()->toDateString();
                    $inputs['date_to'] = Carbon::now()->endOfWeek()->toDateString();
                    break;
                case 'month':
                    $inputs['date_from'] = Carbon::now()->startOfMonth()->toDateString();
                    $inputs['date_to'] = Carbon::now()->endOfMonth()->toDateString();
                    break;
                case 'last_month':
                    $inputs['date_from'] = Carbon::now()->subMonth()->startOfMonth()->toDateString();
                    $inputs['date_to'] = Carbon::now()->subMonth()->endOfMonth()->toDateString();
                    break;
            }
        } else {
            //get the date if no date_from and date_to
            $inputs['date_from'] = $inputs['date_from'] != '' ? $inputs['date_from'] : Carbon::yesterday()->toDateString();
            $inputs['date_to'] = $inputs['date_to'] != '' ? $inputs['date_to'] : $inputs['date_from'];
        }

        $totalFiltered = ClickLogTraceInfo::search($inputs)->pluck('id')->count();
        $leads = ClickLogTraceInfo::search($inputs)
            ->select(DB::RAW('id, email, click_date, click_id, affiliate_id, revenue_tracker_id, ip, is_dbprepoped, reg_count, first_entry_rev_id, first_entry_timestamp, last_entry_rev_id, last_entry_timestamp'))
            ->orderBy($columns[$inputs['order'][0]['column']], $inputs['order'][0]['dir'])
            ->offset($start)->limit($length)
            ->get();
        // \Log::info(\DB::getQueryLog());
        $responseData = [
            'draw' => intval($inputs['draw']),
            'recordsTotal' => ClickLogTraceInfo::count(),
            'recordsFiltered' => $totalFiltered,
            'data' => $leads,
            'offset' => $start,
            'length' => $length,
        ];

        return response()->json($responseData, 200);
    }

    public function download(): BinaryFileResponse
    {
        $inputs = session()->get('click_log_trace_info_input');

        $start = $inputs['start'];
        $length = $inputs['length'];

        $columns = [
            'id',
            'click_date',
            'click_id',
            'email',
            'affiliate_id',
            'revenue_tracker_id',
            'ip',
            'is_dbprepoped',
            'reg_count',
            'first_entry_rev_id',
            'last_entry_rev_id',
        ];
        //Date From and To
        if (isset($inputs['date_range']) && $inputs['date_range'] != '') {
            //Date Range
            switch ($inputs['date_range']) {
                case 'yesterday':
                    $inputs['date_from'] = Carbon::yesterday()->toDateString();
                    $inputs['date_to'] = Carbon::yesterday()->toDateString();
                    break;
                case 'week':
                    $inputs['date_from'] = Carbon::now()->startOfWeek()->toDateString();
                    $inputs['date_to'] = Carbon::now()->endOfWeek()->toDateString();
                    break;
                case 'month':
                    $inputs['date_from'] = Carbon::now()->startOfMonth()->toDateString();
                    $inputs['date_to'] = Carbon::now()->endOfMonth()->toDateString();
                    break;
                case 'last_month':
                    $inputs['date_from'] = Carbon::now()->subMonth()->startOfMonth()->toDateString();
                    $inputs['date_to'] = Carbon::now()->subMonth()->endOfMonth()->toDateString();
                    break;
            }
        } else {
            //get the date if no date_from and date_to
            $inputs['date_from'] = $inputs['date_from'] != '' ? $inputs['date_from'] : Carbon::yesterday()->toDateString();
            $inputs['date_to'] = $inputs['date_to'] != '' ? $inputs['date_to'] : $inputs['date_from'];
        }

        $leads = ClickLogTraceInfo::search($inputs)
            ->select(DB::RAW('id, email, click_date, click_id, affiliate_id, revenue_tracker_id, ip, is_dbprepoped, reg_count, first_entry_rev_id, first_entry_timestamp, last_entry_rev_id, last_entry_timestamp'))
            ->orderBy($columns[$inputs['order'][0]['column']], $inputs['order'][0]['dir'])
            ->get();

        $title = 'ClickLogTraceInfo_'.Carbon::now()->toDateString();

        Excel::store(function ($excel) use ($title, $leads) {
            $excel->sheet($title, function ($sheet) use ($leads) {

                // Set auto size for sheet
                $sheet->setAutoSize(true);

                $headers = ['No.', 'Click Date', 'Click ID', 'Email', 'Affiliate ID', 'RevTracker', 'IP Address', 'DB Prepoped', 'No. of Registration', '1st Entry', 'Last Entry'];

                //set up the title header row
                $sheet->appendRow($headers);

                //style the headers
                $sheet->cells('A1:K1', function ($cells) {

                    // Set font
                    $cells->setFont([
                        'size' => '12',
                        'bold' => true,
                    ]);

                    $cells->setAlignment('center');
                    $cells->setValignment('center');
                });

                $c = 1;

                foreach ($leads as $l) {
                    $sheet->appendRow([
                        $c++,
                        $l->click_date,
                        $l->click_id,
                        $l->email,
                        $l->affiliate_id,
                        $l->revenue_tracker_id,
                        $l->ip,
                        $l->is_dbprepoped == 1 ? 'Yes' : 'No',
                        $l->reg_count,
                        $l->first_entry_rev_id == 0 ? '' : $l->first_entry_rev_id.' - '.$l->first_entry_timestamp,
                        $l->last_entry_rev_id == 0 ? '' : $l->last_entry_rev_id.' - '.$l->last_entry_timestamp,
                    ]);
                }
            });
        }, $title.'.xls', 'downloads');

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
