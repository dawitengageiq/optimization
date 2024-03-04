<?php

namespace App\Http\Controllers;

use App\PrepopStatistic;
use Cache;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Log;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PrepopStatisticsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $inputs = $request->all();
        /*
        Log::info('group_by: '.$inputs['group_by']);
        Log::info('date_from: '.$inputs['date_from']);
        Log::info('date_to: '.$inputs['date_to']);
        Log::info('predefined_date: '.$inputs['predefined_date']);
        Log::info('affiliate_id: '.$inputs['affiliate_id']);
        */

        $columns = [
            // datatable column index  => database column name
            0 => 'created_at',
            1 => 'affiliate_id',
            2 => 'revenue_tracker_id',
            3 => 's1',
            4 => 's2',
            5 => 's3',
            6 => 's4',
            7 => 's5',
            8 => 'total_clicks',
            9 => 'prepop_count',
            10 => 'no_prepop_count',
            11 => 'prepop_with_errors_count',
            12 => 'no_prepop_percentage',
            13 => 'prepop_with_errors_percentage',
            14 => 'profit_margin',
        ];

        //$paramSearch = $inputs['search']['value'];
        $start = $inputs['start'];
        $length = $inputs['length'];

        $groupBy = $inputs['group_by'];
        $dateFrom = $inputs['date_from'];
        $dateTo = $inputs['date_to'];
        $predefinedDate = $inputs['predefined_date'];
        $affiliateID = $inputs['affiliate_id'];

        $orderCol = $columns[$inputs['order'][0]['column']];
        $orderDir = $inputs['order'][0]['dir'];

        //Log::info("predefined_date: $predefinedDate");
        //Log::info("orderColumn: $orderCol");
        //Log::info("orderDir: $orderDir");

        //determine the right value for date from and date to
        if (! empty($predefinedDate) || $predefinedDate != '') {
            switch ($predefinedDate) {
                case 'yesterday':

                    $dateFrom = Carbon::now()->subDay()->toDateString();
                    $dateTo = Carbon::now()->subDay()->toDateString();

                    break;

                case 'week':

                    $dateFrom = Carbon::now()->startOfWeek()->toDateString();
                    $dateTo = Carbon::now()->endOfWeek()->toDateString();

                    break;

                case 'month':

                    $dateFrom = Carbon::now()->startOfMonth()->toDateString();
                    $dateTo = Carbon::now()->endOfMonth()->toDateString();

                    break;
            }
        } else {
            if (empty($dateFrom)) {

                $dateFrom = $dateTo;
            } elseif (empty($dateTo)) {
                $dateTo = $dateFrom;
            }
        }

        $params = [
            'affiliate_id' => $affiliateID,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'group_by' => $groupBy,
            'order_column' => $orderCol,
            'order_direction' => $orderDir,
            's1' => $inputs['s1'],
            's2' => $inputs['s2'],
            's3' => $inputs['s3'],
            's4' => $inputs['s4'],
            's5' => $inputs['s5'],
            'sib_s1' => $inputs['sib_s1'],
            'sib_s2' => $inputs['sib_s2'],
            'sib_s3' => $inputs['sib_s3'],
            'sib_s4' => $inputs['sib_s4'],
        ];

        $prepopStatsBaseQuery = PrepopStatistic::getStatistics($params);
        $prepopStatistics = $prepopStatsBaseQuery;

        $allRecords = $prepopStatistics->get();
        $totalRecords = count($allRecords);
        $totalFiltered = $totalRecords;

        $prepopStatisticsWithLimit = $prepopStatsBaseQuery;
        //$prepopStatisticsWithOutLimit = $prepopStatsBaseQuery;

        //cache the current selection
        $reportTitle = 'prepop_'.$dateFrom.'_'.$dateTo;
        //Cache::put('latest_prepop_queried_stats_title',$reportTitle, 300);
        //cache the data to be downloaded
        //Cache::put('latest_prepop_queried_stats', $prepopStatisticsWithOutLimit->get(), 300);

        if ($length > 1) {
            $prepopStatisticsWithLimit->skip($start)->take($length);
        }

        $prepopStats = $prepopStatisticsWithLimit->get();
        $prepopStatsData = [];

        $dateRange = $dateFrom.' - '.$dateTo;

        foreach ($prepopStats as $stat) {
            switch ($groupBy) {
                case 'affiliate_id':
                    $date = $dateRange;
                    $affiliate_id = $stat->affiliate_id;
                    $revenue_tracker_id = '';
                    $s1 = '';
                    $s2 = '';
                    $s3 = '';
                    $s4 = '';
                    $s5 = '';
                    break;
                case 'revenue_tracker_id':
                    $date = $dateRange;
                    $affiliate_id = '';
                    $revenue_tracker_id = $stat->revenue_tracker_id;
                    $s1 = '';
                    $s2 = '';
                    $s3 = '';
                    $s4 = '';
                    $s5 = '';
                    break;
                case 's1':
                    $date = $dateRange;
                    $affiliate_id = '';
                    $revenue_tracker_id = '';
                    $s1 = $stat->s1;
                    $s2 = '';
                    $s3 = '';
                    $s4 = '';
                    $s5 = '';
                    break;
                case 's2':
                    $date = $dateRange;
                    $affiliate_id = '';
                    $revenue_tracker_id = '';
                    $s1 = '';
                    $s2 = $stat->s2;
                    $s3 = '';
                    $s4 = '';
                    $s5 = '';
                    break;
                case 's3':
                    $date = $dateRange;
                    $affiliate_id = '';
                    $revenue_tracker_id = '';
                    $s1 = '';
                    $s2 = '';
                    $s3 = $stat->s3;
                    $s4 = '';
                    $s5 = '';
                    break;
                case 's4':
                    $date = $dateRange;
                    $affiliate_id = '';
                    $revenue_tracker_id = '';
                    $s1 = '';
                    $s2 = '';
                    $s3 = '';
                    $s4 = $stat->s4;
                    $s5 = '';
                    break;
                case 's5':
                    $date = $dateRange;
                    $affiliate_id = '';
                    $revenue_tracker_id = '';
                    $s1 = $stat->s1;
                    $s2 = '';
                    $s3 = '';
                    $s4 = '';
                    $s5 = $stat->s5;
                    break;
                default:
                    $date = $stat->created_at;
                    $affiliate_id = $stat->affiliate_id;
                    $revenue_tracker_id = $stat->revenue_tracker_id;
                    $s1 = $inputs['sib_s1'] == 'true' ? $stat->s1 : '';
                    $s2 = $inputs['sib_s2'] == 'true' ? $stat->s2 : '';
                    $s3 = $inputs['sib_s3'] == 'true' ? $stat->s3 : '';
                    $s4 = $inputs['sib_s4'] == 'true' ? $stat->s4 : '';
                    $s5 = $stat->s5;
                    break;
            }

            $data = [
                'date' => $date,
                'affiliate_id' => $affiliate_id,
                'revenue_tracker_id' => $revenue_tracker_id,
                's1' => $s1,
                's2' => $s2,
                's3' => $s3,
                's4' => $s4,
                's5' => $s5,
                'total_clicks' => $stat->total_clicks,
                'prepopped' => $stat->prepop_count,
                'not_prepopped' => $stat->no_prepop_count,
                'prepopped_with_errors' => $stat->prepop_with_errors_count,
                'percentage_unprepopped' => number_format($stat->no_prepop_percentage * 100.00, 2).'%',
                'percentage_prepopped_with_errors' => number_format($stat->prepop_with_errors_percentage * 100.00, 2).'%',
                'profit_margin' => number_format($stat->profit_margin * 100.00, 2).'%',
            ];

            array_push($prepopStatsData, $data);
        }

        $responseData = [
            'draw' => intval($inputs['draw']),   // for every request/draw by client side , they send a number as a parameter, when they receive a response/data they first check the draw number, so we are sending same number in draw.
            'recordsTotal' => $totalRecords,  // total number of records
            'recordsFiltered' => $totalFiltered, // total number of records after searching, if there is no searching then totalFiltered = totalData
            'data' => $prepopStatsData,   // total data array
            //"grouping" => $groupBy,
            //"date_range" => urlencode($dateFrom.' - '.$dateTo)
            'date_from' => urlencode($dateFrom),
            'date_to' => urlencode($dateTo),
            'predefined_date' => $predefinedDate,
            'affiliate_id' => $affiliateID,
            'group_by' => $groupBy,
            'report_title' => $reportTitle,
        ];

        return response()->json($responseData, 200);
    }

    /**
     *  Prepop Statistics download
     */
    public function downloadPrepopStatisticsReport(Request $request): BinaryFileResponse
    {
        $inputs = $request->all();
        // \Log::info($inputs);
        $grouping = '';
        $dateRange = '';

        if (isset($inputs['group_by'])) {
            $grouping = $inputs['group_by'];
        }

        if (isset($inputs['date_from']) && isset($inputs['date_to'])) {
            $dateRange = $inputs['date_from'].' - '.$inputs['date_to'];
        }

        //get the cached records
        //$reportTitle = Cache::get('latest_prepop_queried_stats_title');
        //$latestPrepopQueriedStats = Cache::get('latest_prepop_queried_stats');
        $reportTitle = isset($inputs['report_title']) ? $inputs['report_title'] : null;

        //$paramSearch = $inputs['search']['value'];
        $groupBy = $inputs['group_by'];
        $dateFrom = $inputs['date_from'];
        $dateTo = $inputs['date_to'];
        $predefinedDate = $inputs['predefined_date'];
        $affiliateID = $inputs['affiliate_id'];

        //determine the right value for date from and date to
        if (! empty($predefinedDate) || $predefinedDate != '') {
            switch ($predefinedDate) {
                case 'yesterday':

                    $dateFrom = Carbon::now()->subDay()->toDateString();
                    $dateTo = Carbon::now()->subDay()->toDateString();

                    break;

                case 'week':

                    $dateFrom = Carbon::now()->startOfWeek()->toDateString();
                    $dateTo = Carbon::now()->endOfWeek()->toDateString();

                    break;

                case 'month':

                    $dateFrom = Carbon::now()->startOfMonth()->toDateString();
                    $dateTo = Carbon::now()->endOfMonth()->toDateString();

                    break;
            }
        } else {
            if (empty($dateFrom)) {

                $dateFrom = $dateTo;
            } elseif (empty($dateTo)) {
                $dateTo = $dateFrom;
            }
        }

        $params = [
            'affiliate_id' => $affiliateID,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'group_by' => $groupBy,
            's1' => $inputs['s1'],
            's2' => $inputs['s2'],
            's3' => $inputs['s3'],
            's4' => $inputs['s4'],
            's5' => $inputs['s5'],
            'sib_s1' => $inputs['sib_s1'],
            'sib_s2' => $inputs['sib_s2'],
            'sib_s3' => $inputs['sib_s3'],
            'sib_s4' => $inputs['sib_s4'],
        ];

        // \DB::enableQueryLog();
        $latestPrepopQueriedStats = PrepopStatistic::getStatistics($params)->get();
        // Log::info(\DB::getQueryLog());
        if ($reportTitle && $latestPrepopQueriedStats) {
            $sheetTitle = $reportTitle;

            Excel::create($reportTitle, function ($excel) use ($sheetTitle, $latestPrepopQueriedStats, $grouping, $dateRange, $inputs) {
                $excel->sheet($sheetTitle, function ($sheet) use ($latestPrepopQueriedStats, $grouping, $dateRange, $inputs) {
                    $rowNumber = 1;
                    //$firstDataRowNumber = 1;

                    //Set auto size for sheet
                    $sheet->setAutoSize(true);

                    //set up the title header row
                    $sheet->appendRow([
                        'Date',
                        'Affiliate ID',
                        'Revenue Tracker ID',
                        'S1',
                        'S2',
                        'S3',
                        'S4',
                        'S5',
                        'Total Clicks',
                        'Prepopped',
                        'Not Prepopped',
                        'Prepopped With Errors',
                        'Percentage Prepoped',
                        'Percentage Not Prepopped',
                        'Percentage: Prepopped With Errors',
                    ]);

                    //style the headers
                    $sheet->cells("A$rowNumber:O$rowNumber", function ($cells) {
                        // Set font
                        $cells->setFont([
                            'size' => '12',
                            'bold' => true,
                        ]);

                        $cells->setAlignment('center');
                        $cells->setValignment('center');
                        $cells->setBackground('#000000');
                        $cells->setFontColor('#ffffff');
                    });

                    //fill in the data
                    foreach ($latestPrepopQueriedStats as $stat) {
                        switch ($grouping) {
                            case 'affiliate_id':
                                $date = $dateRange;
                                $affiliate_id = $stat->affiliate_id;
                                $revenue_tracker_id = '';
                                $s1 = '';
                                $s2 = '';
                                $s3 = '';
                                $s4 = '';
                                $s5 = '';
                                break;
                            case 'revenue_tracker_id':
                                $date = $dateRange;
                                $affiliate_id = '';
                                $revenue_tracker_id = $stat->revenue_tracker_id;
                                $s1 = '';
                                $s2 = '';
                                $s3 = '';
                                $s4 = '';
                                $s5 = '';
                                break;
                            case 's1':
                                $date = $dateRange;
                                $affiliate_id = '';
                                $revenue_tracker_id = '';
                                $s1 = $stat->s1;
                                $s2 = '';
                                $s3 = '';
                                $s4 = '';
                                $s5 = '';
                                break;
                            case 's2':
                                $date = $dateRange;
                                $affiliate_id = '';
                                $revenue_tracker_id = '';
                                $s1 = '';
                                $s2 = $stat->s2;
                                $s3 = '';
                                $s4 = '';
                                $s5 = '';
                                break;
                            case 's3':
                                $date = $dateRange;
                                $affiliate_id = '';
                                $revenue_tracker_id = '';
                                $s1 = '';
                                $s2 = '';
                                $s3 = $stat->s3;
                                $s4 = '';
                                $s5 = '';
                                break;
                            case 's4':
                                $date = $dateRange;
                                $affiliate_id = '';
                                $revenue_tracker_id = '';
                                $s1 = '';
                                $s2 = '';
                                $s3 = '';
                                $s4 = $stat->s4;
                                $s5 = '';
                                break;
                            case 's5':
                                $date = $dateRange;
                                $affiliate_id = '';
                                $revenue_tracker_id = '';
                                $s1 = $stat->s1;
                                $s2 = '';
                                $s3 = '';
                                $s4 = '';
                                $s5 = $stat->s5;
                                break;
                            default:
                                $date = $stat->created_at;
                                $affiliate_id = $stat->affiliate_id;
                                $revenue_tracker_id = $stat->revenue_tracker_id;
                                $s1 = $inputs['sib_s1'] == 'true' ? $stat->s1 : '';
                                $s2 = $inputs['sib_s2'] == 'true' ? $stat->s2 : '';
                                $s3 = $inputs['sib_s3'] == 'true' ? $stat->s3 : '';
                                $s4 = $inputs['sib_s4'] == 'true' ? $stat->s4 : '';
                                $s5 = $stat->s5;
                                break;
                        }

                        // $prepop_percentage = number_format(($stat->prepop_count / $stat->total_clicks) * 100, 2);
                        // $no_prepop_percentage = number_format(($stat->no_prepop_count / $stat->total_clicks) * 100, 2);
                        // $error_percentage = number_format(($stat->prepop_with_errors_count / $stat->total_clicks) * 100, 2);
                        $rowNumber++;
                        //set up the title header row
                        $sheet->appendRow([
                            $date,
                            $affiliate_id,
                            $revenue_tracker_id,
                            $s1,
                            $s2,
                            $s3,
                            $s4,
                            $s5,
                            $stat->total_clicks,
                            $stat->prepop_count,
                            $stat->no_prepop_count,
                            $stat->prepop_with_errors_count,
                            "=(J$rowNumber/I$rowNumber)", //$prepop_percentage,
                            "=(K$rowNumber/I$rowNumber)", //$no_prepop_percentage,
                            "=(L$rowNumber/I$rowNumber)", //$error_percentage,
                            // $stat->profit_margin
                        ]);

                    }

                    //formatting of margin percentage
                    $sheet->setColumnFormat([
                        'M2:M'.$rowNumber => '00.00%',
                    ]);

                    $sheet->setColumnFormat([
                        'N2:N'.$rowNumber => '00.00%',
                    ]);

                    $sheet->setColumnFormat([
                        'O2:O'.$rowNumber => '00.00%',
                    ]);

                    // $sheet->setColumnFormat([
                    //     'P2:O'.$rowNumber => '00.00%'
                    // ]);
                });
            })->store('xls', storage_path('downloads'));

            $file_path = storage_path('downloads').'/'.$reportTitle.'.xls';

            if (file_exists($file_path)) {
                return response()->download($file_path, $reportTitle.'.xls', [
                    'Content-Length: '.filesize($file_path),
                ]);
            } else {
                exit("Requested file $file_path  does not exist on our server!");
            }
        }

        exit('There is problem generating the spreadsheet file please regenerate the data!');
    }

    public function getprepopReportStats(Request $request): JsonResponse
    {
        $inputs = $request->all();
        $columns = [
            0 => 'created_at',
            1 => 'affiliate_id',
            2 => 'revenue_tracker_id',
            3 => 's1',
            4 => 's2',
            5 => 's3',
            6 => 's4',
            7 => 's5',
            8 => 'total_clicks',
            9 => 'prepop_count',
            10 => 'no_prepop_count',
            11 => 'prepop_with_errors_count',
            12 => 'no_prepop_percentage',
            13 => 'prepop_with_errors_percentage',
            14 => 'profit_margin',
        ];

        //$paramSearch = $inputs['search']['value'];
        $groupBy = $inputs['group_by'];
        $dateFrom = $inputs['date_from'];
        $dateTo = $inputs['date_to'];
        $predefinedDate = $inputs['predefined_date'];
        $affiliateID = $inputs['affiliate_id'];

        //determine the right value for date from and date to
        if (! empty($predefinedDate) || $predefinedDate != '') {
            switch ($predefinedDate) {
                case 'yesterday':

                    $dateFrom = Carbon::now()->subDay()->toDateString();
                    $dateTo = Carbon::now()->subDay()->toDateString();

                    break;

                case 'week':

                    $dateFrom = Carbon::now()->startOfWeek()->toDateString();
                    $dateTo = Carbon::now()->endOfWeek()->toDateString();

                    break;

                case 'month':

                    $dateFrom = Carbon::now()->startOfMonth()->toDateString();
                    $dateTo = Carbon::now()->endOfMonth()->toDateString();

                    break;
            }
        } else {
            if (empty($dateFrom)) {

                $dateFrom = $dateTo;
            } elseif (empty($dateTo)) {
                $dateTo = $dateFrom;
            }
        }

        $params = [
            'affiliate_id' => $affiliateID,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'group_by' => $groupBy,
            's1' => $inputs['s1'],
            's2' => $inputs['s2'],
            's3' => $inputs['s3'],
            's4' => $inputs['s4'],
            's5' => $inputs['s5'],
            'sib_s1' => $inputs['sib_s1'],
            'sib_s2' => $inputs['sib_s2'],
            'sib_s3' => $inputs['sib_s3'],
            'sib_s4' => $inputs['sib_s4'],
        ];

        $stats = PrepopStatistic::getStatistics($params)->get();

        $responseData = [
            'records' => $stats,
            'params' => $params,
            'report_title' => 'prepop_'.$dateFrom.'_'.$dateTo,
        ];

        if (isset($inputs['order'])) {
            $responseData['order_column'] = $inputs['order'][0]['column'];
            $responseData['order_dir'] = $inputs['order'][0]['dir'];
        }

        return response()->json($responseData, 200);
    }
}
