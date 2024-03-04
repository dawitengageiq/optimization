<?php

namespace App\Http\Controllers;

use App\Affiliate;
use App\AffiliateReport;
use App\AffiliateRevenueTracker;
use App\Campaign;
use App\Events\UserActionEvent;
use App\HandPAffiliateReport;
use App\Helpers\AffiliateReportExcelGeneratorHelper;
use App\InternalIframeAffiliateReport;
use App\Jobs\GenerateAffiliateExcelReport;
use App\RevenueTrackerCakeStatistic;
use App\RevenueTrackerWebsiteViewStatistic;
use Cache;
use Carbon\Carbon;
use Curl\Curl;
use DB;
use ErrorException;
use File;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Log;
use Maatwebsite\Excel\Facades\Excel;
use Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AffiliateReportController extends Controller
{
    public function __construct()
    {
        if (config('app.type') != 'reports') {
            $this->middleware('auth');
            $this->middleware('admin');
        }
    }

    public function getHandPStats(Request $request)
    {
        $inputs = $request->all();
        $affiliate_type = $inputs['affiliate_type'];
        // $searchValue = $inputs['search']['value'];

        $allStats = HandPAffiliateReport::handPAffiliateRevenueStats($inputs);
        $stats = $allStats->get();

        $affs = $stats->map(function ($st) {
            return $st->affiliate_id;
        });
        $affiliates = Affiliate::whereIn('id', $affs)->pluck('company', 'id');

        $responseData = [
            'records' => $stats,
            'affiliates' => $affiliates,
        ];

        return response()->json($responseData, 200);
    }

    public function getHandPSubIDStats(Request $request)
    {
        $inputs = $request->all();
        // $searchValue = $inputs['search']['value'];
        //\Log::info($inputs);
        $allStats = HandPAffiliateReport::handPAffiliateSubIDRevenueStats($inputs);
        $stats = $allStats->get();

        $affs = $stats->map(function ($st) {
            return $st->affiliate_id;
        });
        $affiliates = Affiliate::whereIn('id', $affs)->pluck('company', 'id');

        $responseData = [
            'records' => $stats,
            'affiliates' => $affiliates,
        ];

        return response()->json($responseData, 200);
    }

    public function getSubIDStats(Request $request)
    {
        $inputs = $request->all();
        // $searchValue = $inputs['search']['value'];

        $allStats = HandPAffiliateReport::handPSubIDCampaignRevenueStats($inputs);
        $stats = $allStats->get();

        $cmps = $stats->map(function ($st) {
            return $st->campaign_id;
        });
        $campaigns = Campaign::whereIn('id', $cmps)->pluck('name', 'id');

        $responseData = [
            'records' => $stats,
            'campaigns' => $campaigns,
        ];

        return response()->json($responseData, 200);
    }

    public function getAffiliateStats(Request $request)
    {
        // \DB::connection('secondary')->enableQueryLog();
        // \DB::enableQueryLog();
        $inputs = $request->all();
        // $connection = config('app.type') == 'reports' ? 'mysql' : 'secondary';
        $allStats = RevenueTrackerCakeStatistic::affiliateBreakdown($inputs);

        $stats = $allStats->get();

        $affiliate_type = $inputs['affiliate_type'];

        $affs = $stats->map(function ($st) {
            return $st->affiliate_id;
        });
        $affiliates = Affiliate::whereIn('id', $affs)->pluck('company', 'id');

        // \Log::info(\DB::connection('secondary')->getQueryLog());
        // \Log::info(\DB::getQueryLog());

        $responseData = [
            'records' => $stats,
            'affiliates' => $affiliates,
        ];

        return response()->json($responseData, 200);
    }

    public function getSubIDBreakdown($params, $forDownload = false)
    {
        // \DB::connection('secondary')->enableQueryLog();
        // \DB::enableQueryLog();
        $connection = config('app.type') == 'reports' ? 'mysql' : 'secondary';
        $date = [];
        if (isset($params['period'])) {
            if ($params['period'] == 'none' && (! empty($params['start_date']) && ! empty($params['end_date']))) {
                $date['from'] = $params['start_date'];
                $date['to'] = $params['end_date'];
            } else {
                $date = AffiliateReport::getSnapShotPeriodRange($params['period']);
            }
        } else {
            $date = AffiliateReport::getSnapShotPeriodRange('none');
            if (! empty($params['start_date']) && ! empty($params['end_date'])) {
                $date['from'] = $params['start_date'];
                $date['to'] = $params['end_date'];
            }
        }
        $dateFrom = $date['from'];
        $dateTo = $date['to'];

        $cpQuery = "SELECT revenue_tracker_id,s1,s2,s3,s4,s5,sum(clicks) as clicks,sum(payout) AS payout,created_at FROM revenue_tracker_cake_statistics WHERE created_at BETWEEN '$dateFrom' AND '$dateTo' AND revenue_tracker_id= $params[revenue_tracker_id]";
        $lrQuery = "SELECT revenue_tracker_id,s1,s2,s3,s4,s5,sum(lead_count) as leads,sum(revenue) AS revenue,created_at FROM affiliate_reports WHERE created_at BETWEEN '$dateFrom' AND '$dateTo' AND revenue_tracker_id= $params[revenue_tracker_id] ";

        $gb = [];
        $where_str = '';
        if (! isset($params['sib_s1']) || (isset($params['sib_s1']) && $params['sib_s1'] == 'true')) {
            $gb[] = 's1';
            $where_str .= ' AND t1.s1=t2.s1';
        }
        if (! isset($params['sib_s2']) || (isset($params['sib_s2']) && $params['sib_s2'] == 'true')) {
            $gb[] = 's2';
            $where_str .= ' AND t1.s2=t2.s2';
        }
        if (! isset($params['sib_s3']) || (isset($params['sib_s3']) && $params['sib_s3'] == 'true')) {
            $gb[] = 's3';
            $where_str .= ' AND t1.s3=t2.s3';
        }
        if (! isset($params['sib_s4']) || (isset($params['sib_s4']) && $params['sib_s4'] == 'true')) {
            $gb[] = 's4';
            $where_str .= ' AND t1.s4=t2.s4';
        }
        $gb_str = count($gb) > 0 ? 'GROUP BY '.implode(',', $gb) : '';

        $orderBy_str = '';
        if ($forDownload) {
            $orderBy_str = 'ORDER BY t1.s1 ASC';
        }

        $query = "SELECT t1.*, t2.leads, t2.revenue FROM ($cpQuery $gb_str) AS t1 LEFT JOIN ($lrQuery $gb_str) AS t2 ON t1.revenue_tracker_id = t2.revenue_tracker_id $where_str WHERE t1.created_at BETWEEN '$dateFrom' AND '$dateTo' and t1.revenue_tracker_id = $params[revenue_tracker_id] $orderBy_str";

        //Log::info($query);
        $stats = DB::connection($connection)->select($query);

        // \Log::info(\DB::connection('secondary')->getQueryLog());
        // \Log::info(\DB::getQueryLog());

        return $stats;
    }

    public function subIDStats(Request $request): JsonResponse
    {
        $inputs = $request->all();

        $stats = $this->getSubIDBreakdown($inputs);

        $responseData = [
            'records' => $stats,
        ];

        return response()->json($responseData, 200);
    }

    public function downloadSubIDStats(Request $request)
    {
        $inputs = $request->all();
        $connection = config('app.type') == 'reports' ? 'mysql' : 'secondary';
        // DB::enableQueryLog();

        $stats = $this->getSubIDBreakdown($inputs, false);

        $reportTitle = 'AffiliateReport_'.$inputs['revenue_tracker_id'].'_SubID_Report';
        $sheetTitle = $inputs['period'] == 'none' ? $inputs['start_date'].'_'.$inputs['end_date'] : $inputs['period'];
        Excel::create($reportTitle, function ($excel) use ($sheetTitle, $stats, $inputs) {
            $excel->sheet($sheetTitle, function ($sheet) use ($stats, $inputs) {
                $rowNumber = 1;

                //Set auto size for sheet
                $sheet->setAutoSize(true);

                //set up the title header row
                $sheet->appendRow([
                    $inputs['affiliate_id'].'/CD'.$inputs['revenue_tracker_id'],
                ]);

                //style the headers
                $sheet->cells("A$rowNumber:K$rowNumber", function ($cells) {
                    // Set font
                    $cells->setFont([
                        'size' => '12',
                        'bold' => true,
                    ]);

                    $cells->setAlignment('left');
                    $cells->setValignment('center');
                    $cells->setBackground('#FFFF00');
                });
                $sheet->mergeCells("A$rowNumber:K$rowNumber");
                $rowNumber++;

                //set up the title header row
                $sheet->appendRow([
                    'S1',
                    'S2',
                    'S3',
                    'S4',
                    'S5',
                    'Clicks',
                    'Payout',
                    'Leads',
                    'Revenue',
                    'We Get',
                    'Margin',
                ]);

                //style the headers
                $sheet->cells("A$rowNumber:K$rowNumber", function ($cells) {
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

                foreach ($stats as $stat) {
                    $rowNumber++;

                    $revenue = $stat->revenue != '' ? number_format($stat->revenue, 2, '.', '') : '0.0';
                    $clicks = $stat->clicks != '' ? $stat->clicks : '0';
                    $revenue = $stat->revenue != '' ? number_format($stat->revenue, 2, '.', '') : '0.0';
                    $payout = $stat->payout != '' ? number_format($stat->payout, 3, '.', '') : '0.0';
                    $leads = $stat->leads != '' ? $stat->leads : '0';
                    $weGet = number_format($revenue - $payout, 2, '.', '');
                    $margin = ($weGet == 0 || $revenue == 0) ? 0 : number_format(($weGet / $revenue), 2, '.', '');
                    //set up the title header row
                    $sheet->appendRow([
                        $inputs['sib_s1'] == 'true' ? $stat->s1 : '',
                        $inputs['sib_s2'] == 'true' ? $stat->s2 : '',
                        $inputs['sib_s3'] == 'true' ? $stat->s3 : '',
                        $inputs['sib_s4'] == 'true' ? $stat->s4 : '',
                        $stat->s5,
                        $clicks,
                        $payout,
                        $leads,
                        $revenue,
                        $weGet,
                        $margin,
                    ]);
                }

                $lastRowNumber = $rowNumber;

                $rowNumber++;

                $sheet->appendRow([
                    '',
                    '',
                    '',
                    '',
                    'Total',
                    "=SUM(F2:F$lastRowNumber)",
                    "=SUM(G2:G$lastRowNumber)",
                    "=SUM(H2:H$lastRowNumber)",
                    "=SUM(I2:I$lastRowNumber)",
                    "=I$rowNumber-G$rowNumber",
                    "=J$rowNumber/I$rowNumber",
                ]);

                //style the headers
                $sheet->cells("E$rowNumber:K$rowNumber", function ($cells) {
                    // Set font
                    $cells->setFont([
                        'size' => '12',
                        'bold' => true,
                    ]);

                    $cells->setAlignment('right');
                    $cells->setValignment('center');
                });

                $sheet->setColumnFormat([
                    "G2:G$rowNumber" => '$0.000',
                    "I2:I$rowNumber" => '$0.00',
                    "J2:J$rowNumber" => '$0.00',
                    "K2:K$rowNumber" => '0.00%',
                ]);
            });
        })->store('xlsx', storage_path('downloads'));

        $file_path = storage_path('downloads').'/'.$reportTitle.'.xlsx';

        if (file_exists($file_path)) {
            return response()->download($file_path, $reportTitle.'.xlsx', [
                'Content-Length: '.filesize($file_path),
            ]);
        } else {
            exit("Requested file $file_path  does not exist on our server!");
        }

        return response()->json($responseData, 200);
    }

    public function getIframeWebsiteStats(Request $request): JsonResponse
    {

        $inputs = $request->all();
        $searchValue = $inputs['search']['value'];

        $allStats = RevenueTrackerWebsiteViewStatistic::iframeWebsiteRevenueStatistics($inputs);
        $allStatCount = RevenueTrackerWebsiteViewStatistic::iframeWebsiteRevenueStatistics($inputs)->get()->count();
        $totalFiltered = $allStatCount;
        //Log::info('all_stat_count: '.$allStatCount);
        //Log::info('sql: '.RevenueTrackerCakeStatistic::websiteRevenueStatisticsForServerside($inputs)->toSql());

        if ($inputs['length'] != -1) {
            $stats = $allStats->skip($inputs['start'])->take($inputs['length'])->get();
        } else {
            //GET ALL STATS
            $stats = $allStats->get();
        }

        $statData = [];

        $total_passovers = 0;
        $total_payout = 0.0;
        $total_leads = 0;
        $total_revenue = 0.0;
        $total_weget = 0.0;

        foreach ($stats as $stat) {
            //Log::info($stat);

            $revenue = $stat->revenue != null ? floatval($stat->revenue) : 0.0;
            $weGet = $stat->we_get != null ? floatval($stat->we_get) : 0.0;
            $margin = $stat->margin != null ? number_format(floatval($stat->margin), 2).'%' : 'N/A';
            $payout = $stat->payout != null ? floatval($stat->payout) : 0.0;

            $websiteName = '<span class="revenue-tracker" data-id="'.$stat->affiliate_id.'" data-rtid="'.$stat->revenue_tracker_id.'" data-webname="'.$stat->website.'">'.$stat->website.'</span>';

            $data = [
                'website_name' => $websiteName,
                'passovers' => $stat->passovers,
                'payout' => '$ '.number_format($payout, 2),
                'leads' => $stat->leads,
                'revenue' => '$ '.number_format($revenue, 2),
                'we_get' => '$ '.number_format($weGet, 2),
                'margin' => $margin,
            ];

            $total_passovers = $total_passovers + intval($stat->passovers);
            $total_payout = $total_payout + floatval($stat->payout);
            $total_leads = $total_leads + intval($stat->leads);
            $total_revenue = $total_revenue + floatval($revenue);
            $total_weget = $total_weget + $weGet;
            //$total_margin = $total_margin + $margin;

            array_push($statData, $data);
        }

        if ($total_revenue == 0) {
            $totalMarginText = 'N/A';
        } else {
            $total_margin = (($total_revenue - $total_payout) / $total_revenue) * 100.00;
            $totalMarginText = number_format($total_margin, 2).'%';
        }

        if (! empty($searchValue) || $searchValue != '') {
            $totalFiltered = $allStatCount;
        }

        $responseData = [
            'draw' => intval($inputs['draw']),   // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            'recordsTotal' => $allStatCount,  // total number of records
            'recordsFiltered' => $totalFiltered, // total number of records after searching, if there is no searching then totalFiltered = totalData
            'data' => $statData,   // total data array
            'totalPassovers' => $total_passovers,
            'totalPayout' => '$ '.number_format($total_payout, 2),
            'totalLeads' => $total_leads,
            'totalRevenue' => '$ '.number_format($total_revenue, 2),
            'totalWeGet' => '$ '.number_format($total_weget, 2),
            'totalMargin' => $totalMarginText,
        ];

        return response()->json($responseData, 200);
    }

    public function getWebsiteStats(Request $request)
    {
        // \DB::connection('secondary')->enableQueryLog();
        // \DB::enableQueryLog();
        $inputs = $request->all();

        $allStats = RevenueTrackerCakeStatistic::websiteRevenueStatisticsForServersideRevised($inputs);

        $stats = $allStats->get();

        $rts = $stats->map(function ($st) {
            return $st->revenue_tracker_id;
        });
        $websites = AffiliateRevenueTracker::whereIn('revenue_tracker_id', $rts)->pluck('website', 'revenue_tracker_id');

        // \Log::info(\DB::connection('secondary')->getQueryLog());
        // \Log::info(\DB::getQueryLog());
        $responseData = [
            'records' => $stats,
            'websites' => $websites,
        ];

        return response()->json($responseData, 200);
    }

    public function revenueTrackerStats(Request $request)
    {
        $inputs = $request->all();
        // $searchValue = $inputs['search']['value'];
        // Log::info($request->all());
        // DB::enableQueryLog();
        $allStats = AffiliateReport::getReports($inputs)->where(function ($q) {
            $q->where('lead_count', '!=', 0)
                ->orWhere('revenue', '!=', 0);
        });
        $stats = $allStats->get();
        // Log::info(DB::getQueryLog());
        $cids = $stats->map(function ($st) {
            return $st->campaign_id;
        });
        $campaigns = Campaign::whereIn('id', $cids)->pluck('name', 'id');

        $responseData = [
            'records' => $stats,
            'campaigns' => $campaigns,
        ];

        return response()->json($responseData, 200);
    }

    public function iframeRevenueTrackerStats(Request $request): JsonResponse
    {
        $inputs = $request->all();
        $searchValue = $inputs['search']['value'];

        $allStats = InternalIframeAffiliateReport::getReports($inputs)->with('campaign');
        $allStatCount = InternalIframeAffiliateReport::getReports($inputs)->get()->count();
        $totalFiltered = $allStatCount;

        if ($inputs['length'] != -1) {
            $stats = $allStats->skip($inputs['start'])->take($inputs['length'])->get();
        } else {
            //GET ALL STATS
            $stats = $allStats->get();
        }

        $statData = [];

        $total_leads = 0;
        $total_revenue = 0.0;

        foreach ($stats as $stat) {
            $revenue = $stat->revenue != null ? floatval($stat->revenue) : 0.0;
            $leadCount = $stat->lead_count != null ? floatval($stat->lead_count) : 0.0;

            //$campaignName = '<span class="campaign-name" data-id="'.$stat->affiliate_id.'" data-rtid="'.$stat->revenue_tracker_id.'" data-webname="'.$stat->website.'">'.$stat->website.'</span>';
            $data = [
                'campaign' => $stat->campaign->name,
                'leads' => $leadCount,
                'revenue' => '$ '.number_format($revenue, 2),
            ];

            $total_leads = $total_leads + $leadCount;
            $total_revenue = $total_revenue + $revenue;

            array_push($statData, $data);
        }

        if (! empty($searchValue) || $searchValue != '') {
            $totalFiltered = $allStatCount;
        }

        $responseData = [
            'draw' => intval($inputs['draw']),   // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            'recordsTotal' => $allStatCount,  // total number of records
            'recordsFiltered' => $totalFiltered, // total number of records after searching, if there is no searching then totalFiltered = totalData
            'data' => $statData,   // total data array
            'totalLeads' => $total_leads,
            'totalRevenue' => '$ '.number_format($total_revenue, 2),
        ];

        return response()->json($responseData, 200);
    }

    /**
     * Uploading of reports sheet
     */
    public function uploadReports(Request $request): JsonResponse
    {
        $file = $request->file('file');

        $originalExtension = $file->getClientOriginalExtension();
        $originalFilename = '';
        $uploadSuccess = true;
        $date = '';
        $rev_trackers = [];

        $responseData = [
            'status' => 'upload_successful',
            'message' => 'Reports Sheet Uploaded Successful!',
        ];

        if ($originalExtension == 'xls' ||
            $originalExtension == 'xlsx' ||
            $originalExtension == 'csv') {
            if ($originalExtension == 'xls') {
                $originalFilename = basename($file->getClientOriginalName(), '.xls');
            } elseif ($originalExtension == 'xlsx') {
                $originalFilename = basename($file->getClientOriginalName(), '.xlsx');
            } elseif ($originalExtension == 'csv') {
                $originalFilename = basename($file->getClientOriginalName(), '.csv');
            }

            $fullFilename = $originalFilename.'_'.Carbon::today()->toDateString().'.'.$originalExtension;

            if (Storage::exists('uploads/'.$fullFilename)) {
                //delete the old and replace with new
                Storage::delete('uploads/'.$fullFilename);
            }

            //save the file to local storage
            Storage::disk('local')->put($fullFilename, File::get($file));

            //copy the uploaded file to uploads
            Storage::move($fullFilename, 'uploads/'.$fullFilename);

            //check if the user used the uploader file.
            $isEngageIQReportUploader = strpos($originalFilename, config('constants.ENGAGE_IQ_REPORT_EXCEL_UPLOADER'));

            if (is_bool($isEngageIQReportUploader) == false) {
                //$originalFilename = config('constants.ENGAGE_IQ_REPORT_EXCEL_UPLOADER');

                //get the file path of spreadsheet file to be read
                $filePath = storage_path('app/uploads').'/'.$fullFilename;
                $reader = Excel::load($filePath, function ($reader) {
                })->all();

                $nonExistingCampaign = '';

                //will contain problems during parsing
                $errorMsg = '';

                foreach ($reader as $sheet) {
                    $sheetTitle = $sheet->getTitle();
                    $sheetTitleArray = explode('-', $sheetTitle);
                    $campaignID = $sheetTitleArray[0];
                    $campaign = Campaign::find($campaignID);
                    if ($campaign != null) {
                        foreach ($sheet as $row) {
                            if (! isset($row['date']) || ! isset($row['publisher'])) {
                                continue;
                            }

                            if ($row['publisher'] === '' || $row['publisher'] === null) {
                                //do not include rows that don't have publisher id
                                continue;
                            }

                            $date = $row['date'];

                            if (is_string($row['date']) && $row['date'] === '') {
                                //do not include rows that don't have date
                                continue;
                            } else {
                                $date = Carbon::parse($row['date']);
                            }

                            //check if there is revenue column
                            if (! isset($row['revenue'])) {
                                $responseData = [
                                    'status' => 'file_problem',
                                    'message' => 'There campaigns in the report sheet that do not have revenue column!',
                                ];

                                return response()->json($responseData, 200);
                            }

                            $revenue = 0.0;

                            if (is_numeric($row['revenue']) && $row['revenue'] !== null) {
                                $revenue = floatval($row['revenue']);
                            }

                            //get the revenue tracker and removed the CD prefix
                            $revenueTrackerID = str_replace('CD', '', $row['publisher']);

                            Log::info('EngageIQ Report Sheet');
                            Log::info("revenue_tracker_id: $revenueTrackerID");

                            //get the revenue tracker
                            $tracker = AffiliateRevenueTracker::where('revenue_tracker_id', '=', $revenueTrackerID)->first();

                            $s1 = isset($row['s1']) ? $row['s1'] : '';
                            $s2 = isset($row['s2']) ? $row['s2'] : '';
                            $s3 = isset($row['s3']) ? $row['s3'] : '';
                            $s4 = isset($row['s4']) ? $row['s4'] : '';
                            // $s5 = isset($row['s5']) ? $row['s5'] : '';
                            $s5 = '';

                            if ($tracker != null && $revenue > 0) {
                                $rev_trackers[] = $tracker->revenue_tracker_id;
                                // if(!isset($rev_trackers[$tracker->revenue_tracker_id])) {
                                //     $rev_trackers[] = $tracker->revenue_tracker_id;
                                // }

                                $affiliateReport = AffiliateReport::firstOrNew([
                                    'affiliate_id' => $tracker->affiliate_id,
                                    'revenue_tracker_id' => $tracker->revenue_tracker_id,
                                    'campaign_id' => $campaign->id,
                                    'created_at' => $date,
                                    's1' => $s1,
                                    's2' => $s2,
                                    's3' => $s3,
                                    's4' => $s4,
                                    's5' => $s5,
                                ]);

                                $affiliateReport->lead_count = 0;
                                $affiliateReport->revenue = $revenue;

                                try {
                                    $affiliateReport->save();
                                    Log::info("affiliate_id: $affiliateReport->affiliate_id");
                                    Log::info("revenue_tracker_id: $affiliateReport->revenue_tracker_id");
                                    Log::info("campaign_id: $campaign->id");
                                    Log::info("revenue: $revenue");
                                } catch (QueryException $e) {
                                    Log::info($e->getMessage());
                                    Log::info('affiliate_id: '.$affiliateReport->affiliate_id);
                                    Log::info('revenue_tracker_id: '.$affiliateReport->revenue_tracker_id);
                                    Log::info("campaign_id: $campaign->id");
                                    Log::info("revenue: $revenue");
                                }
                            }
                        }
                    } else {
                        $nonExistingCampaign = $nonExistingCampaign.$campaignID.',';
                    }
                }

                if (! empty($nonExistingCampaign)) {
                    //this means there are missing campaign IDs
                    $nonExistingCampaign = trim($nonExistingCampaign, ',');

                    $responseData = [
                        'status' => 'upload_warning',
                        'message' => "The report sheet was processed but there are campaigns that do not exist. ($nonExistingCampaign)",
                    ];
                }
                $rev_trackers = array_unique($rev_trackers);
                // Log::info('------------');
                // Log::info($rev_trackers);
                if (count($rev_trackers) > 0) {
                    // DB::enableQueryLog();
                    $affRevTrackers = AffiliateRevenueTracker::whereIn('revenue_tracker_id', $rev_trackers)
                        ->select(['affiliate_id', 'revenue_tracker_id', 'campaign_id'])->get();
                    $rts = [];
                    foreach ($affRevTrackers as $art) {
                        $rts[$art->revenue_tracker_id] = [
                            'a' => $art->affiliate_id,
                            'c' => $art->campaign_id,
                        ];
                    }

                    // Log::info($rts);

                    $has_data = RevenueTrackerCakeStatistic::where('created_at', '=', $date)
                        ->whereIn('revenue_tracker_id', $rev_trackers)
                        ->pluck('revenue_tracker_id')->toArray();
                    // Log::info($has_data);

                    $rows = [];
                    foreach ($rts as $rev_tracker => $rdata) {
                        if (! in_array($rev_tracker, $has_data) && $rdata['c'] != 0) {
                            $rows[] = [
                                'affiliate_id' => $rdata['a'],
                                'revenue_tracker_id' => $rev_tracker,
                                'cake_campaign_id' => $rdata['c'],
                                'created_at' => $date,
                                's1' => '',
                                's2' => '',
                                's3' => '',
                                's4' => '',
                                's5' => '',
                                'type' => 1,
                                'clicks' => 0,
                                'payout' => 0,
                            ];
                        }
                    }

                    if (count($rows) > 0) {
                        Log::info('Rev Trackers No Revenue');
                        Log::info($rows);
                    }

                    $chunks = array_chunk($rows, 1000);
                    foreach ($chunks as $chunk) {
                        try {
                            $connection = config('app.type') == 'reports' ? 'mysql' : 'secondary';
                            DB::connection($connection)->table('revenue_tracker_cake_statistics')->insert($chunk);
                        } catch (QueryException $e) {
                            Log::info('Cake No Clicks Saving Error!');
                            Log::info($e->getMessage());
                        }
                    }

                    $process = new \App\Helpers\CleanAffiliateReportHelper($date);
                    $process->clean();
                }
            } else {
                $responseData = [
                    'status' => 'upload_fail',
                    'message' => 'Please upload the EngageIQ uploader template!',
                ];

                $uploadSuccess = false;
            }
        } else {
            $responseData = [
                'status' => 'upload_fail',
                'message' => 'Invalid File Format! Please upload only XLS/x or CSV formats.',
            ];

            $uploadSuccess = false;
        }

        $user = auth()->user();

        $userLogMessage = '';

        if (! $uploadSuccess) {
            if (isset($fullFilename)) {
                $userLogMessage = "File upload attempt. Affiliate reports file $fullFilename file upload failed!";
            } else {
                $userLogMessage = 'File upload attempt. Affiliate reports file upload failed wrong file format!';
            }
        } else {
            $userLogMessage = "File upload attempt. Affiliate reports file $fullFilename file upload success! Generating new excel report";

            $from = Carbon::parse($date)->toDateString();
            // $to = Carbon::parse($date)->addDay()->toDateString();
            $to = $from;
            if (config('app.type') != 'reports') {
                $reports_url = config('app.reports_url');
                $url = $reports_url.'regenerateAffiliateReportExcel';
                $inputs = $request->all();
                $curl = new Curl();
                $curl->get($url, [
                    'to' => $to,
                    'from' => $from,
                ]);
                if ($curl->error) {
                    $response = $curl->error_code;
                } else {
                    $response = $curl->response;
                    $response = json_decode($response);
                }
                Log::info($response);
            } else {
                //Delete Existing Excel Report and Regenerate
                $excel = new AffiliateReportExcelGeneratorHelper($from, $to, 1);
                $fileNameToDownload = $excel->getFullFileName();
                $filePathToDownload = storage_path('downloads')."/$fileNameToDownload";

                // Log::info($from);
                // Log::info($to);
                if (file_exists($filePathToDownload)) {
                    unlink($filePathToDownload);
                }
                $job = (new GenerateAffiliateExcelReport($from, $to, 1));
                dispatch($job);
            }
        }

        event(new UserActionEvent([
            'section_id' => null,
            'sub_section_id' => null,
            'user_id' => $user->id,
            'change_severity' => 1,
            'summary' => $userLogMessage,
            'old_value' => null,
            'new_value' => null,
        ]));

        return response()->json($responseData, 200);
    }

    public function regenerateAffiliateReportExcel(Request $request)
    {
        $from = $request->from;
        $to = $request->to;
        //Delete Existing Excel Report and Regenerate
        $excel = new AffiliateReportExcelGeneratorHelper($from, $to, 1);
        $fileNameToDownload = $excel->getFullFileName();
        $filePathToDownload = storage_path('downloads')."/$fileNameToDownload";

        // Log::info($from);
        // Log::info($to);
        if (file_exists($filePathToDownload)) {
            unlink($filePathToDownload);
        }
        $job = (new GenerateAffiliateExcelReport($from, $to, 1));
        dispatch($job);
    }

    /**
     * Generate of H and P reports
     */
    public function generateHandPAffiliateReportsXLS(Request $request, $snapshot_period): JsonResponse
    {
        $inputs = $request->all();

        $key = "2-$snapshot_period".$inputs['start_date'].'-'.$inputs['end_date'];
        $params['period'] = $snapshot_period;
        $params['start_date'] = $request->start_date;
        $params['end_date'] = $request->end_date;

        $handpAffiliateStats = HandPAffiliateReport::handPAffiliateStats($params)->get();

        $cids = $handpAffiliateStats->map(function ($st) {
            return $st->campaign_id;
        });
        $campaigns = Campaign::whereIn('id', $cids)->pluck('name', 'id');

        $aids = $handpAffiliateStats->map(function ($st) {
            return $st->affiliate_id;
        });
        $affiliates = Affiliate::whereIn('id', $aids)->pluck('company', 'id');

        //check if there are h and p types
        if (count($handpAffiliateStats) <= 0) {
            //remove if there is cached generated download
            //Cache::forget($key);

            $responseData = [
                'status' => 'generate_failed',
                'message' => 'There are no data to generate!',
                'key' => '',
                'file' => '',
            ];

            return response()->json($responseData, 200);
        }

        $dateRange = [];

        //if period is none then use the date range provided
        if ($snapshot_period == 'none' && (! empty($inputs['start_date']) && ! empty($inputs['end_date']))) {
            $dateRange['from'] = Carbon::parse($inputs['start_date'])->toDateString();
            $dateRange['to'] = Carbon::parse($inputs['end_date'])->toDateString();
        } else {
            $dateRange = AffiliateReport::getSnapShotPeriodRange($snapshot_period);
        }

        $title = 'handp_affiliate_reports('.$dateRange['from'].')';
        $sheetTitle = $dateRange['from'].'_'.$dateRange['to'];

        $arrangedStatCollections = collect([]);
        $publisherCompany = [];

        //aggregate the collection
        foreach ($handpAffiliateStats as $stat) {
            $publisher = "$stat->affiliate_id/S1=$stat->s1";
            $affiliate_name = isset($affiliates[$stat->affiliate_id]) ? $affiliates[$stat->affiliate_id] : '';
            $campaign_name = isset($campaigns[$stat->campaign_id]) ? $campaigns[$stat->campaign_id] : '';
            $data = collect([
                'affiliate_id' => $stat->affiliate_id,
                'company' => $affiliate_name,
                'campaign' => $campaign_name,
                'leads' => $stat->leads,
                'payout' => $stat->payout,
                'revenue' => $stat->revenue,
            ]);

            if (! isset($arrangedStatCollections[$publisher])) {
                //create new set and insert the data collection
                $dataSet = collect([]);
                $dataSet->push($data);
                $arrangedStatCollections->put($publisher, $dataSet);
            } else {
                $arrangedStatCollections[$publisher]->push($data);
            }

            //publisher - company mapping
            $publisherCompany[$publisher] = [
                'affiliate_id' => $stat->affiliate_id,
                'publisher_name' => $affiliate_name,
            ];
        }

        Excel::create($title, function ($excel) use ($sheetTitle, $arrangedStatCollections, $publisherCompany) {
            $excel->sheet($sheetTitle, function ($sheet) use ($arrangedStatCollections, $publisherCompany) {

                $rowNumber = 0;
                $keys = $arrangedStatCollections->keys();

                //Set auto size for sheet
                $sheet->setAutoSize(true);

                $totalsMap = collect([]);

                foreach ($keys as $collectionKey) {
                    //set up the title header row
                    $sheet->appendRow([
                        'Publisher', 'Campaign', 'Leads', 'Payout', 'Revenue',
                    ]);

                    $rowNumber++;

                    $this->setCellStyle($sheet, "A$rowNumber:E$rowNumber", 12, true, 'center', 'center', '#FF0000');

                    $handpAffiliateReports = $arrangedStatCollections[$collectionKey];

                    $beginMerge = $rowNumber + 1;

                    foreach ($handpAffiliateReports as $report) {
                        $sheet->appendRow([
                            $collectionKey, $report['campaign'], $report['leads'], $report['payout'], $report['revenue'],
                        ]);

                        $rowNumber++;
                    }

                    $endMerge = $rowNumber;

                    //compute the totals
                    $sheet->appendRow([
                        '', 'Total', "=SUM(C$beginMerge:C$endMerge)", "=SUM(D$beginMerge:D$endMerge)", "=SUM(E$beginMerge:E$endMerge)",
                    ]);

                    $mergedResultRow = $endMerge + 1;

                    //map the cell total to a certain affiliate
                    $totalsData = collect([
                        'publisher' => $collectionKey,
                        'publisher_name' => $publisherCompany[$collectionKey]['publisher_name'],
                        'revenue' => "=E$mergedResultRow",
                        'leads' => "=C$mergedResultRow",
                        'we_pay' => "=D$mergedResultRow",
                    ]);

                    //$totalsMap->push($totalsData);
                    //resolve the affiliate_id of collection key
                    $affiliateID = $publisherCompany[$collectionKey]['affiliate_id'];

                    if (! isset($totalsMap[$affiliateID])) {
                        //create new set and insert the data collection
                        $dataSet = collect([]);
                        $dataSet->push($totalsData);
                        $totalsMap->put($affiliateID, $dataSet);
                    } else {
                        $totalsMap[$affiliateID]->push($totalsData);
                    }

                    $rowNumber++;

                    //apply color to totals
                    $this->setCellStyle($sheet, "C$rowNumber", null, false, null, null, '#7EC0EE');
                    $this->setCellStyle($sheet, "D$rowNumber", null, false, null, null, '#FFFF00');
                    $this->setCellStyle($sheet, "E$rowNumber", null, false, null, null, '#00FF00');
                    //style the total field
                    $this->setCellStyle($sheet, "B$rowNumber", 12, true, 'right', 'center', null);

                    $sheet->appendRow([
                        '', '', '', '', '',
                    ]);

                    $rowNumber++;

                    //merge the publisher cells
                    $range = "A$beginMerge:A$endMerge";
                    $sheet->mergeCells($range);

                    //set the alignment to top center
                    $sheet->cell('A'.$beginMerge, function ($cell) {
                        $cell->setAlignment('center');
                        $cell->setValignment('top');
                    });

                    $this->setCellStyle($sheet, "A$beginMerge", null, true, 'center', 'top', null);
                }

                $sheet->appendRow([
                    'REVENUE/COST', '', '', '', '', '',
                ]);

                $rowNumber++;

                //merge the revenue/cost header
                $range = "A$rowNumber:F$rowNumber";
                $sheet->mergeCells($range);

                //apply color for revenue/cost
                $this->setCellStyle($sheet, $range, null, true, 'center', 'center', '#0000FF');

                $sheet->appendRow([
                    'Publisher', 'Publisher Name', 'Revenue', 'Leads', 'We Pay', '% Margin',
                ]);

                $rowNumber++;

                $marginPercentageStartRow = $rowNumber + 1;

                //apply color for Revenue, Leads, We pay, and % margin header
                $this->setCellStyle($sheet, "A$rowNumber", 12, true, 'center', 'center', null);
                $this->setCellStyle($sheet, "B$rowNumber", 12, true, 'center', 'center', null);
                $this->setCellStyle($sheet, "C$rowNumber", 12, true, 'center', 'center', '#00FF00');
                $this->setCellStyle($sheet, "D$rowNumber", 12, true, 'center', 'center', '#7EC0EE');
                $this->setCellStyle($sheet, "E$rowNumber", 12, true, 'center', 'center', '#FFFF00');
                $this->setCellStyle($sheet, "F$rowNumber", 12, true, 'center', 'center', '#FFA500');

                $totalsCellRow = [];

                foreach ($totalsMap as $key => $data) {
                    $rowNumber++;

                    $sectionBegin = $rowNumber;
                    $lastPublisherName = '';

                    $sheet->appendRow([
                        '', '', '', '', '', '',
                    ]);

                    $this->setCellStyle($sheet, "A$rowNumber:F$rowNumber", 12, true, 'left', 'center', '#948A54');

                    foreach ($data as $totalData) {
                        $rowNumber++;

                        /*
                        $sheet->appendRow([
                            $key, $totalData['publisher_name'], $totalData['revenue'], $totalData['leads'], $totalData['we_pay'], "=(C$rowNumber-E$rowNumber)/C$rowNumber"
                        ]);
                        */

                        $sheet->appendRow([
                            $totalData['publisher'], $totalData['publisher_name'], $totalData['revenue'], $totalData['leads'], $totalData['we_pay'], "=(C$rowNumber-E$rowNumber)/C$rowNumber",
                        ]);

                        $lastPublisherName = $totalData['publisher_name'];
                    }

                    //fill in the header
                    $this->setCellValue($sheet, "B$sectionBegin", $lastPublisherName);
                    //fill in the totals in the header
                    $startCell = $sectionBegin + 1;
                    $this->setCellValue($sheet, "C$sectionBegin", "=SUM(C$startCell:C$rowNumber)");
                    $this->setCellValue($sheet, "D$sectionBegin", "=SUM(D$startCell:D$rowNumber)");
                    $this->setCellValue($sheet, "E$sectionBegin", "=SUM(E$startCell:E$rowNumber)");
                    $this->setCellValue($sheet, "F$sectionBegin", "=(C$sectionBegin-E$sectionBegin)/C$sectionBegin");

                    if (! isset($totalsCellRow['revenue'])) {
                        $dataArray = [];
                        array_push($dataArray, "C$sectionBegin");
                        $totalsCellRow['revenue'] = $dataArray;
                    } else {
                        array_push($totalsCellRow['revenue'], "C$sectionBegin");
                    }

                    if (! isset($totalsCellRow['leads'])) {
                        $dataArray = [];
                        array_push($dataArray, "D$sectionBegin");
                        $totalsCellRow['leads'] = $dataArray;
                    } else {
                        array_push($totalsCellRow['leads'], "D$sectionBegin");
                    }

                    if (! isset($totalsCellRow['we_pay'])) {
                        $dataArray = [];
                        array_push($dataArray, "E$sectionBegin");
                        $totalsCellRow['we_pay'] = $dataArray;
                    } else {
                        array_push($totalsCellRow['we_pay'], "E$sectionBegin");
                    }
                }

                $sheet->appendRow([
                    '', '', '', '', '', '',
                ]);

                $rowNumber++;

                //total revenue formula
                $cellCodes = '';
                foreach ($totalsCellRow['revenue'] as $cellCode) {
                    $cellCodes = $cellCodes."$cellCode,";
                }
                $cellCodes = trim($cellCodes, ',');
                $revenueFormula = "=SUM($cellCodes)";

                //total leads formula
                $cellCodes = '';
                foreach ($totalsCellRow['leads'] as $cellCode) {
                    $cellCodes = $cellCodes."$cellCode,";
                }

                $cellCodes = trim($cellCodes, ',');
                $leadsFormula = "=SUM($cellCodes)";

                //total we pay formula
                $cellCodes = '';
                foreach ($totalsCellRow['we_pay'] as $cellCode) {
                    $cellCodes = $cellCodes."$cellCode,";
                }

                $cellCodes = trim($cellCodes, ',');
                $wePayFormula = "=SUM($cellCodes)";

                $rowNumber++;

                $sheet->appendRow([
                    '', 'Total', $revenueFormula, $leadsFormula, $wePayFormula, "=(C$rowNumber-E$rowNumber)/C$rowNumber",
                ]);

                $this->setCellStyle($sheet, "A$rowNumber:F$rowNumber", 12, true, 'right', 'center', '#ffc000');

                //formatting of margin percentage
                $sheet->setColumnFormat([
                    "F$marginPercentageStartRow:F$rowNumber" => '00.00%',
                ]);
                Log::info("F$marginPercentageStartRow:F$rowNumber");

                $rowNumber++;

                $sheet->appendRow([
                    '', '', '', '', '', '',
                ]);

                $rowNumber++;

                $sheet->appendRow([
                    '', '', '', '', '', '',
                ]);

                $rowNumber++;

                $sheet->appendRow([
                    'Profit', '', '', '', '', '',
                ]);

                $rowNumber++;

                $totalRowNumber = $rowNumber - 4;
                $profitFirstRowNumber = $rowNumber - 1;

                $sheet->appendRow([
                    "=C$totalRowNumber-E$totalRowNumber", '', '', '', '', '',
                ]);

                //apply design to profit section
                $this->setCellStyle($sheet, "A$profitFirstRowNumber:A$rowNumber", 12, true, 'left', 'center', '#ffc000');

            });
        })->store('xls', storage_path('downloads'));

        // $file_path = storage_path('downloads').'/'.$title.'.xls';

        //cache the file path to be downloaded later
        // Cache::put($key, $file_path, 10);

        $responseData = [
            'status' => 'generate_successful',
            'message' => 'Reports Sheet is generated successfully and you can download it now!',
            'key' => $key,
            'file' => $title.'.xls',
        ];

        return response()->json($responseData, 200);
    }

    public function setCellValue($sheet, $cellCode, $value)
    {
        $sheet->cell($cellCode, function ($cell) use ($value) {
            // manipulate the cell
            $cell->setValue($value);
        });
    }

    public function setCellStyle($sheet, $cellRange, $fontSize, $bold, $alignment, $vAlignment, $backgroundColor)
    {
        //style the headers
        $sheet->cells($cellRange, function ($cells) use ($fontSize, $bold, $alignment, $vAlignment, $backgroundColor) {
            $font['bold'] = $bold;

            if ($fontSize != null) {
                $font['size'] = $fontSize;
            }

            // Set font
            $cells->setFont($font);

            if ($alignment != null) {
                $cells->setAlignment($alignment);
            }

            if ($vAlignment != null) {
                $cells->setValignment($vAlignment);
            }

            if ($backgroundColor != null) {
                $cells->setBackground($backgroundColor);
            }
        });
    }

    /*
    public function downloadHandPAffiliateReportXLS(Request $request, $snapshot_period)
    {
        $filePathToDownload = '';

        $inputs = $request->all();

        $key = "2-$snapshot_period".$inputs['start_date'].'-'.$inputs['end_date'];

        //If there is no cached report then generate it then download it
        if(!Cache::has($key))
        {
            //execute download
            $this->generateHandPAffiliateReportsXLS($request, $snapshot_period);
            $filePathToDownload = Cache::get($key);
        }
        else
        {
            //this means there is cached report just download it.
            $filePathToDownload = Cache::get($key);
        }

        //if period is none then use the date range provided
        if($snapshot_period=='none' && (!empty($inputs['start_date']) && !empty($inputs['end_date'])))
        {
            $dateRange['from'] = Carbon::parse($inputs['start_date']);
            $dateRange['to'] = Carbon::parse($inputs['end_date']);
        }
        else
        {
            $dateRange = AffiliateReport::getSnapShotPeriodRange($snapshot_period);
        }

        //$dateRange = AffiliateReport::getSnapShotPeriodRange($snapshot_period);
        $title = 'handp_affiliate_reports('.$dateRange['from']->toDateString().')';

        if(file_exists($filePathToDownload))
        {
            return response()->download($filePathToDownload,$title.'.xls',[
                'Content-Length: '.filesize($filePathToDownload)
            ]);
        }
        else
        {
            exit("Requested file $filePathToDownload  does not exist on our server!");
        }
    }
    */

    /**
     * Generating of iframe affiliate reports
     */
    public function generateIframeAffiliateReportXLS(Request $request, $snapshot_period): JsonResponse
    {
        ini_set('max_execution_time', 300);
        $inputs = $request->all();

        $key = "1-$snapshot_period".$inputs['start_date'].'-'.$inputs['end_date'];

        $params = [];
        $params['period'] = $snapshot_period;
        $params['start_date'] = $inputs['start_date'];
        $params['end_date'] = $inputs['end_date'];
        $revenueTrackerStatistics = RevenueTrackerWebsiteViewStatistic::revenueStatistics($params)->get();

        //check if there are h and p types
        if (count($revenueTrackerStatistics) <= 0) {
            //remove if there is cached generated download
            //Cache::forget($key);

            $responseData = [
                'status' => 'generate_failed',
                'message' => 'There are no data to generate!',
                'key' => '',
                'file' => '',
            ];

            return response()->json($responseData, 200);
        }

        $params = [];
        $params['period'] = $snapshot_period;
        $params['start_date'] = $inputs['start_date'];
        $params['end_date'] = $inputs['end_date'];
        $dateRange = [];

        //if period is none then use the date range provided
        if ($snapshot_period == 'none' && (! empty($inputs['start_date']) && ! empty($inputs['end_date']))) {
            $dateRange['from'] = Carbon::parse($inputs['start_date']);
            $dateRange['to'] = Carbon::parse($inputs['end_date']);
        } else {
            $dateRange = AffiliateReport::getSnapShotPeriodRange($snapshot_period);
        }

        $title = 'internal_iframe_affiliate_reports('.$dateRange['from']->toDateString().')';
        $sheetTitle = $dateRange['from']->toDateString().'_'.$dateRange['to']->toDateString();

        //get all revenue trackers from the affiliate reports base on date period date.
        $affiliateReportsRevenueTrackers = InternalIframeAffiliateReport::allRevenueTracker($params)->get();

        Excel::create($title, function ($excel) use ($sheetTitle, $affiliateReportsRevenueTrackers, $revenueTrackerStatistics, $snapshot_period, $inputs) {
            $excel->sheet($sheetTitle, function ($sheet) use ($affiliateReportsRevenueTrackers, $revenueTrackerStatistics, $snapshot_period, $inputs) {
                $rowNumber = 1;
                $firstDataRowNumber = 1;

                $publishersRevenueCells = [];

                foreach ($affiliateReportsRevenueTrackers as $tracker) {
                    //Set auto size for sheet
                    $sheet->setAutoSize(true);

                    //set up the title header row
                    $sheet->appendRow([
                        'Publisher', 'Campaign', 'Leads', 'Revenue',
                    ]);

                    //style the headers
                    $sheet->cells("A$rowNumber:D$rowNumber", function ($cells) {
                        // Set font
                        $cells->setFont([
                            'size' => '12',
                            'bold' => true,
                        ]);

                        $cells->setAlignment('center');
                        $cells->setValignment('center');
                        $cells->setBackground('#FF0000');
                    });

                    $rowNumber++;

                    //get all the affiliate reports for the particular revenue tracker
                    $params['period'] = $snapshot_period;
                    $params['revenue_tracker_id'] = $tracker->revenue_tracker_id;
                    $params['start_date'] = $inputs['start_date'];
                    $params['end_date'] = $inputs['end_date'];
                    $revenueTrackerAffiliateReportStats = InternalIframeAffiliateReport::getRevenueTrackerAffiliateReports($params)->with('campaign')->get();
                    $begin = $rowNumber;

                    foreach ($revenueTrackerAffiliateReportStats as $stat) {
                        $sheet->appendRow([
                            $stat->revenue_tracker_id, $stat->campaign->name, $stat->lead_count, $stat->revenue,
                        ]);

                        $rowNumber++;
                    }

                    //append total row for total revenue
                    $total_formula = '=SUM( D'.$begin.':D'.($rowNumber - 1).')';
                    $totalLeadsFormula = '=SUM( C'.$begin.':C'.($rowNumber - 1).')';
                    $sheet->appendRow([
                        '', 'Total', $totalLeadsFormula, $total_formula,
                    ]);

                    $publishersRevenueCells[$tracker->revenue_tracker_id] = [
                        'leads_formula' => '=$C$'.$rowNumber,
                        'revenue_formula' => '=$D$'.$rowNumber,
                    ];

                    //style the total field
                    $sheet->cells("B$rowNumber:B$rowNumber", function ($cells) {
                        // Set font
                        $cells->setFont([
                            'size' => '12',
                            'bold' => true,
                        ]);

                        $cells->setAlignment('right');
                        $cells->setValignment('center');
                    });

                    //style the total value field
                    $sheet->cells("C$rowNumber:D$rowNumber", function ($cells) {
                        $cells->setAlignment('right');
                        $cells->setValignment('center');
                        $cells->setBackground('#00FF00');
                    });

                    $rowNumber++;

                    //add extra row for separation
                    $sheet->appendRow([
                        '', '', '', '',
                    ]);

                    $rowNumber++;

                    //merge the cells in publisher column
                    $end = count($revenueTrackerAffiliateReportStats) + $begin - 1;
                    $range = "A$begin:A$end";
                    $sheet->mergeCells($range);

                    //set the alignment to middle
                    $sheet->cell('A'.$begin, function ($cell) {
                        $cell->setAlignment('center');
                        $cell->setValignment('top');
                    });
                }

                //for the revenue/cost
                $sheet->appendRow([
                    'REVENUE/COST', '', '', '', '', '', '',
                ]);

                //merge the columns of the REVENUE/COST row
                $sheet->mergeCells("A$rowNumber:G$rowNumber");

                //style the REVENUE/COST header
                $sheet->cells("A$rowNumber:G$rowNumber", function ($cells) {
                    // Set font
                    $cells->setFont([
                        'size' => '12',
                        'bold' => true,
                    ]);

                    $cells->setAlignment('center');
                    $cells->setValignment('center');
                    $cells->setBackground('#0000FF');
                });

                $rowNumber++;

                $sheet->appendRow([
                    'Publisher', 'Publisher Name', 'Leads', 'Revenue', 'Passovers', 'We Pay', '% Margin',
                ]);

                //style the header below the REVENUE/COST header
                $sheet->cells("A$rowNumber:G$rowNumber", function ($cells) {
                    // Set font
                    $cells->setFont([
                        'size' => '12',
                        'bold' => true,
                    ]);

                    $cells->setAlignment('center');
                    $cells->setValignment('center');
                    $cells->setBackground('#FFFF00');
                });

                $rowNumber++;

                $noBreakDown = [];
                $withBreakDown = [];

                //for the revenue/cost
                foreach ($revenueTrackerStatistics as $stat) {
                    //check if current stat can be broken down
                    $params = [];
                    $params['period'] = $snapshot_period;
                    $params['affiliate_id'] = $stat->affiliate_id;
                    $params['start_date'] = $inputs['start_date'];
                    $params['end_date'] = $inputs['end_date'];

                    $breakDownStats = RevenueTrackerWebsiteViewStatistic::iframeWebsiteRevenueStatistics($params)->get();
                    Log::info('affiliate_id: '.$stat->affiliate_id);
                    Log::info('count: '.count($breakDownStats));
                    Log::info($params);

                    if (count($breakDownStats) <= 1) {
                        //segregate the stats that don't have any breakdowns
                        array_push($noBreakDown, $stat);
                    } else {
                        $data['parent'] = $stat;
                        $data['child'] = $breakDownStats;

                        array_push($withBreakDown, $data);
                    }
                }

                $marginPercentageStartRow = $rowNumber;

                foreach ($noBreakDown as $nbkstat) {
                    try {
                        //'Publisher', 'Publisher Name', 'Revenue', 'Passovers', 'We Pay', '% Margin'
                        $sheet->appendRow([
                            "$nbkstat->affiliate_id/$nbkstat->revenue_tracker_id",
                            $nbkstat->company,
                            $publishersRevenueCells[$nbkstat->revenue_tracker_id]['leads_formula'],
                            $publishersRevenueCells[$nbkstat->revenue_tracker_id]['revenue_formula'],
                            $nbkstat->passovers,
                            $nbkstat->payout,
                            "=(D$rowNumber - F$rowNumber)/D$rowNumber",
                        ]);

                        $rowNumber++;
                    } catch (ErrorException $e) {
                        Log::info('Missing Publisher: '.$e->getMessage());
                    }
                }

                foreach ($withBreakDown as $wbkstat) {
                    $sheet->appendRow([
                        '',
                        $wbkstat['parent']->company,
                        '',
                        $wbkstat['parent']->revenue,
                        $wbkstat['parent']->passovers,
                        $wbkstat['parent']->payout,
                        "=(D$rowNumber - F$rowNumber)/D$rowNumber",
                    ]);

                    //style the header below the REVENUE/COST header
                    $sheet->cells("A$rowNumber:G$rowNumber", function ($cells) {
                        $cells->setAlignment('center');
                        $cells->setValignment('center');
                        $cells->setBackground('#A8A8A8');
                    });

                    $sumUpRowToChange = $rowNumber;

                    $rowNumber++;

                    $sumUpBegin = $rowNumber;

                    foreach ($wbkstat['child'] as $cwbkstat) {

                        try {
                            //'Publisher', 'Publisher Name', 'Revenue', 'Passovers', 'We Pay', '% Margin'
                            $sheet->appendRow([
                                "$cwbkstat->affiliate_id/$cwbkstat->revenue_tracker_id",
                                $wbkstat['parent']->company,
                                $publishersRevenueCells[$cwbkstat->revenue_tracker_id]['leads_formula'],
                                $publishersRevenueCells[$cwbkstat->revenue_tracker_id]['revenue_formula'],
                                $cwbkstat->passovers,
                                $cwbkstat->payout,
                                "=(D$rowNumber - F$rowNumber)/D$rowNumber",
                            ]);

                            $rowNumber++;
                        } catch (ErrorException $e) {
                            Log::info('Missing Publisher: '.$e->getMessage());
                        }
                    }

                    $sheet->cell("C$sumUpRowToChange", function ($cell) use ($sumUpBegin, $rowNumber) {
                        // manipulate the cell
                        $end = $rowNumber - 1;
                        $cell->setValue("=SUM(C$sumUpBegin:C$end)");
                    });

                    $sheet->cell("D$sumUpRowToChange", function ($cell) use ($sumUpBegin, $rowNumber) {
                        // manipulate the cell
                        $end = $rowNumber - 1;
                        $cell->setValue("=SUM(D$sumUpBegin:D$end)");
                    });

                    $sheet->cell("E$sumUpRowToChange", function ($cell) use ($sumUpBegin, $rowNumber) {
                        // manipulate the cell
                        $end = $rowNumber - 1;
                        $cell->setValue("=SUM(E$sumUpBegin:E$end)");
                    });

                    $sheet->cell("F$sumUpRowToChange", function ($cell) use ($sumUpBegin, $rowNumber) {
                        // manipulate the cell
                        $end = $rowNumber - 1;
                        $cell->setValue("=SUM(F$sumUpBegin:F$end)");
                    });
                }

                //formatting of margin percentage
                $sheet->setColumnFormat([
                    'G'.$marginPercentageStartRow.':G'.$rowNumber => '00.00%',
                ]);

            });

        })->store('xls', storage_path('downloads'));

        // $file_path = storage_path('downloads').'/'.$title.'.xls';

        //cache the file path to be downloaded later
        // Cache::put($key, $file_path, 10);

        $responseData = [
            'status' => 'generate_successful',
            'message' => 'Reports Sheet is generated successfully and you can download it now!',
            'key' => $key,
            'file' => $title.'.xls',
        ];

        return response()->json($responseData, 200);
    }

    /**
     * Downloading of iframe reports
     */
    public function downloadIframeAffiliateReportXLS(Request $request, $snapshot_period): BinaryFileResponse
    {
        $filePathToDownload = '';

        $inputs = $request->all();

        $key = "1-$snapshot_period".$inputs['start_date'].'-'.$inputs['end_date'];

        //If there is no cached report then generate it then download it
        if (! Cache::has($key)) {
            //execute download
            $this->generateIframeAffiliateReportXLS($request, $snapshot_period);
            $filePathToDownload = Cache::get($key);
        } else {
            //this means there is cached report just download it.
            $filePathToDownload = Cache::get($key);
        }

        //if period is none then use the date range provided
        if ($snapshot_period == 'none' && (! empty($inputs['start_date']) && ! empty($inputs['end_date']))) {
            $dateRange['from'] = Carbon::parse($inputs['start_date']);
            $dateRange['to'] = Carbon::parse($inputs['end_date']);
        } else {
            $dateRange = AffiliateReport::getSnapShotPeriodRange($snapshot_period);
        }

        $title = 'internal_iframe_affiliate_reports('.$dateRange['from']->toDateString().')';

        if (file_exists($filePathToDownload)) {
            return response()->download($filePathToDownload, $title.'.xls', [
                'Content-Length: '.filesize($filePathToDownload),
            ]);
        } else {
            exit("Requested file $filePathToDownload  does not exist on our server!");
        }
    }

    public function generateAffiliateReportXLS(Request $request, $affiliate_type, $snapshot_period): JsonResponse
    {
        if (config('app.type') != 'reports') {
            $response = $this->curlToGenerateAffiliateReportXLS($request, $affiliate_type, $snapshot_period);
            $responseData = json_decode($response);

            return response()->json($responseData, 200);
        } else {
            $inputs = $request->all();
            $params = [];
            $params['affiliate_type'] = $affiliate_type;
            $params['period'] = $snapshot_period;
            $params['start_date'] = $inputs['start_date'];
            $params['end_date'] = $inputs['end_date'];
            $key = "$params[affiliate_type]-$params[period]:$params[start_date]$params[end_date]";
            $dateRange = [];

            //if period is none then use the date range provided
            if ($snapshot_period == 'none' && (! empty($inputs['start_date']) && ! empty($inputs['end_date']))) {
                $dateRange['from'] = Carbon::parse($inputs['start_date']);
                $dateRange['to'] = Carbon::parse($inputs['end_date']);
            } else {
                $dateRange = AffiliateReport::getSnapShotPeriodRange($snapshot_period);
            }

            $excel = new AffiliateReportExcelGeneratorHelper($dateRange['from']->toDateString(), $dateRange['to']->toDateString(), $affiliate_type);

            $fileNameToDownload = $excel->getFullFileName();
            $filePathToDownload = storage_path('downloads')."/$fileNameToDownload";

            $nsbFileName = $excel->getNSBFullFileName();

            $fileChecker = file_exists($filePathToDownload);
            $nsbFileChecker = file_exists(storage_path('downloads')."/$nsbFileName");

            // $fileChecker = config('app.type') == 'reports' ? file_exists($filePathToDownload) : Storage::disk('reports')->has($fileNameToDownload);
            // $nsbFileChecker = config('app.type') == 'reports' ? file_exists(storage_path('downloads')."/$nsbFileName") : Storage::disk('reports')->has($nsbFileName);

            if ($fileChecker) {
                $responseData = [
                    'status' => 'generate_successful',
                    'message' => 'Reports Sheet was generated successfully and you can download it now!',
                    'key' => $key,
                    'file' => $fileNameToDownload,
                    'hasSubidBreakdown' => $nsbFileChecker,
                    'nsb_file' => $nsbFileName,
                ];
            } else {
                if ($excel->noSimilarRunningJob()) {
                    $job = (new GenerateAffiliateExcelReport($dateRange['from']->toDateString(), $dateRange['to']->toDateString(), $affiliate_type));
                    dispatch($job);
                }

                $responseData = [
                    'status' => 'generating_start',
                    'message' => 'Reports is currently being generated. We will send an email to admin for the report',
                    'key' => $key,
                    'file' => $fileNameToDownload,
                    'hasSubidBreakdown' => $nsbFileChecker,
                    'nsb_file' => $nsbFileName,
                ];
            }

            return response()->json($responseData, 200);
        }
    }

    public function curlToGenerateAffiliateReportXLS(Request $request, $affiliate_type, $snapshot_period)
    {
        $reports_url = config('app.reports_url');
        $url = $reports_url.'generateAffiliateReportXLS/'.$affiliate_type.'/'.$snapshot_period;
        $inputs = $request->all();
        $curl = new Curl();
        $curl->get($url, $inputs);
        if ($curl->error) {
            return $curl->error_code;
        } else {
            return $curl->response;
        }
    }

    /**
     * Downloading of generated file.
     */
    public function downloadAffiliateReportXLS(Request $request): BinaryFileResponse
    {
        $inputs = $request->all();
        $fileNameToDownload = $inputs['file_name_dl'];
        $filePathToDownload = storage_path('downloads')."/$fileNameToDownload";

        $disk = config('app.type') == 'reports' ? 'downloads' : 'reports';

        if (Storage::disk($disk)->has($fileNameToDownload)) {
            $filecontent = Storage::disk($disk)->get($fileNameToDownload);

            return response()->make($filecontent, '200', [
                'Content-Type' => 'application/octet-stream',
                'Content-Disposition' => 'attachment; filename="'.$fileNameToDownload.'"',
            ]);
        } else {
            exit("Requested file $filePathToDownload  does not exist on our server!");
        }

        // if(config('app.type') == 'reports') {
        //     if(Storage::disk('main')->has($fileNameToDownload)) {
        //         $filecontent = Storage::disk('main')->get($fileNameToDownload);
        //         return response()->make($filecontent, '200', array(
        //                 'Content-Type' => 'application/octet-stream',
        //                 'Content-Disposition' => 'attachment; filename="'.$fileNameToDownload.'"'
        //             ));
        //     }else {
        //         exit("Requested file $filePathToDownload  does not exist on our server!");
        //     }
        // }else {
        //     $filePathToDownload = storage_path('downloads')."/$fileNameToDownload";

        //     if(file_exists($filePathToDownload))
        //     {
        //         return response()->download($filePathToDownload, $fileNameToDownload,[
        //             'Content-Length: '.filesize($filePathToDownload)
        //         ]);
        //     }
        //     else
        //     {
        //         exit("Requested file $filePathToDownload  does not exist on our server!");
        //     }
        // }

    }

    public function getIframeAffiliateStats(Request $request): JsonResponse
    {
        $inputs = $request->all();
        $searchValue = $inputs['search']['value'];

        $allStats = RevenueTrackerWebsiteViewStatistic::revenueStatistics($inputs);
        $allStatCount = RevenueTrackerWebsiteViewStatistic::revenueStatistics($inputs)->get()->count();
        $totalFiltered = $allStatCount;
        // Log::info('all_stat_count: '.$allStatCount);

        if ($inputs['length'] != -1) {
            $stats = $allStats->skip($inputs['start'])->take($inputs['length'])->get();
        } else {
            //GET ALL STATS
            $stats = $allStats->get();
        }

        $statData = [];

        $total_passovers = 0;
        $total_payout = 0.0;
        $total_leads = 0;
        $total_revenue = 0.0;
        $total_weget = 0.0;
        $total_margin = 0.0;

        foreach ($stats as $stat) {
            $revenue = $stat->revenue != null ? floatval($stat->revenue) : 0.0;
            $weGet = $stat->we_get != null ? floatval($stat->we_get) : 0.0;
            $payout = $stat->payout != null ? floatval($stat->payout) : 0.0;

            if (($revenue == null && $payout == null) || ($revenue == 0.0 && $payout > 0.0)) {
                $marginText = 'N/A';
            } else {
                $margin = (($revenue - $payout) / $revenue) * 100.00;
                $marginText = number_format(floatval($margin), 2).'%';
            }

            $aff_name = '<span class="ar_aff_id" data-id="'.$stat->affiliate_id.'">'.$stat->company.'</span>';

            $data = [
                'affiliate' => $aff_name,
                'passovers' => $stat->passovers,
                'payout' => '$ '.number_format($payout, 2),
                'leads' => $stat->leads,
                'revenue' => '$ '.number_format($revenue, 2),
                'we_get' => '$ '.number_format($weGet, 2),
                'margin' => $marginText,
            ];

            $total_passovers = $total_passovers + intval($stat->passovers);
            $total_payout = $total_payout + floatval($stat->payout);
            $total_leads = $total_leads + intval($stat->leads);
            $total_revenue = $total_revenue + floatval($revenue);
            $total_weget = $total_weget + $weGet;
            //$total_margin = $total_margin + $margin;

            array_push($statData, $data);
        }

        if ($total_revenue == 0) {
            $totalMarginText = 'N/A';
        } else {
            $total_margin = (($total_revenue - $total_payout) / $total_revenue) * 100.00;
            $totalMarginText = number_format($total_margin, 2).'%';
        }

        if (! empty($searchValue) || $searchValue != '') {
            $totalFiltered = $allStatCount;
        }

        $responseData = [
            'draw' => intval($inputs['draw']),   // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            'recordsTotal' => $allStatCount,  // total number of records
            'recordsFiltered' => $totalFiltered, // total number of records after searching, if there is no searching then totalFiltered = totalData
            'data' => $statData,   // total data array
            'totalPassovers' => $total_passovers,
            'totalPayout' => '$ '.number_format($total_payout, 2),
            'totalLeads' => $total_leads,
            'totalRevenue' => '$ '.number_format($total_revenue, 2),
            'totalWeGet' => '$ '.number_format($total_weget, 2),
            'totalMargin' => $totalMarginText,
        ];

        return response()->json($responseData, 200);
    }

    public function test()
    {
        return 'Success';
    }
}
