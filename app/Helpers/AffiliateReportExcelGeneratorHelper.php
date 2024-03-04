<?php

namespace App\Helpers;

use App\Affiliate;
use App\AffiliateReport;
use App\AffiliateRevenueTracker;
use App\AffiliateWebsite;
use App\Campaign;
use App\RevenueTrackerCakeStatistic;
use DB;
use File;
use Illuminate\Http\JsonResponse;
use Log;
use Maatwebsite\Excel\Facades\Excel;
use PHPExcel_Cell_DataType;
use Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AffiliateReportExcelGeneratorHelper
{
    protected $date_from;

    protected $date_to;

    protected $type;

    protected $title;

    protected $nsb_title;

    protected $ext;

    protected $status;

    protected $hasSubIDBreakdown;

    protected $campaigns;

    protected $affiliates;

    protected $disk;

    protected $jsRevTrackers;

    protected $revWebsites;

    public function __construct($dateFrom, $dateTo, $type)
    {
        $this->date_from = $dateFrom;
        $this->date_to = $dateTo;
        $this->type = $type;
        $this->ext = 'xlsx';
        $this->disk = 'downloads';

        if ($this->type == 1) {
            $this->title = 'internal_affiliate_reports('.$dateFrom.' to '.$dateTo.')';
            $this->nsb_title = 'nsb_internal_affiliate_reports('.$dateFrom.' to '.$dateTo.')';
        } else {
            $this->title = 'handp_affiliate_reports('.$dateFrom.' to '.$dateTo.')';
            $this->nsb_title = 'nsb_handp_affiliate_reports('.$dateFrom.' to '.$dateTo.')';
        }

        $this->status = false;

        //Subid Breakdown Checker
        $sidb = AffiliateReport::whereBetween('created_at', [$this->date_from, $this->date_to])
            ->where(function ($query) {
                $query->where('s1', '=', '')->orWhere('s2', '!=', '')->orWhere('s3', '!=', '')->orWhere('s4', '!=', '')->orWhere('s5', '!=', '');
            })->limit(1)->first();
        $this->hasSubIDBreakdown = $sidb ? true : false;

        //Get Campaigns
        $this->campaigns = Campaign::pluck('name', 'id');

        //Get JS PATH Rev Trackers
        $this->jsRevTrackers = AffiliateRevenueTracker::where('offer_id', 1)->where('revenue_tracker_id', '!=', 1)->pluck('revenue_tracker_id')->toArray();

        //Revenue Tracker Website Names
        $this->revWebsites = AffiliateWebsite::where('revenue_tracker_id', '!=', '')->pluck('website_name', 'revenue_tracker_id')->toArray();
        // Log::info($this->revWebsites);
    }

    /**
     * Generating of reports
     *
     * @param  Request  $request
     * @param $affiliate_type
     * @param $snapshot_period
     */
    public function generate(): JsonResponse
    {
        DB::enableQueryLog();
        if (! $this->noSimilarRunningJob()) {
            return;
        }
        $title = $this->title;
        $sheetTitle = $this->date_from.' to '.$this->date_to;
        $params = [];
        $params['affiliate_type'] = $this->type;
        $params['start_date'] = $this->date_from;
        $params['end_date'] = $this->date_to;
        $key = "$params[start_date]$params[end_date]:$params[affiliate_type]";

        //$disk = config('app.type') == 'reports' ? 'downloads' : 'reports';
        $disk = $this->disk;
        Storage::disk($disk)->put($this->getTempFileName(), ''); //Create Temp File

        Log::info('Attempt Affiliate Report Excel Generation with SubId Breakdown: '.$this->getFullFileName());
        $this->subIDBreakdown();
        Log::info('Affiliate Report File Created: '.$this->getFullFileName());

        if ($this->hasSubIDBreakdown) {
            Log::info('Attempt Affiliate Report Excel Generation with Rev Tracker Breakdown: '.$this->getFullFileName());
            $this->revTrackerBreakdown();
            Log::info('Affiliate Report File Created: '.$this->getNSBFullFileName());
        }

        Storage::disk($disk)->delete($this->getTempFileName()); //delete templ file

        $responseData = [
            'status' => 'generate_successful',
            'message' => 'Reports Sheet is generated successfully and you can download it now!',
            'key' => $key,
            'file' => $this->getFullFileName(),
        ];

        $this->status = true;

        Log::info('LOG QRY');
        Log::info(DB::getQueryLog());

        return response()->json($responseData, 200);
    }

    protected function revTrackerBreakdown()
    {
        $title = $this->nsb_title;
        $sheetTitle = $this->date_from.' to '.$this->date_to;
        // DB::enableQueryLog();
        $params = [];
        $params['affiliate_type'] = $this->type;
        $params['start_date'] = $this->date_from;
        $params['end_date'] = $this->date_to;
        $key = "$params[start_date]$params[end_date]:$params[affiliate_type]";

        Log::info('Master');
        Log::info('DB::START');
        $revenueTrackerStatistics = RevenueTrackerCakeStatistic::revTrackerBreakdown($params)->get();
        //Get Affiliate Report Rev Tracker Campaign revenue breakdown
        $affiliateReportsRevenueTrackers = AffiliateReport::getRevTrackerPerCampaignTotalRevenueNoSubIDBreakdown($params)
            ->where(function ($q) {
                $q->where('lead_count', '!=', 0)
                    ->orWhere('revenue', '!=', 0);
            })
            ->get();
        Log::info('DB::END');
        $affiliates = $this->affiliates;
        // Log::info(DB::getQueryLog());
        if (Storage::disk('downloads')->has($this->getNSBFullFileName())) {
            Storage::disk('downloads')->delete($this->getNSBFullFileName());
        } //delete existing file

        Excel::create($title, function ($excel) use ($title, $sheetTitle, $affiliateReportsRevenueTrackers, $revenueTrackerStatistics, $affiliates) {
            $excel->sheet($sheetTitle, function ($sheet) use ($title, $affiliateReportsRevenueTrackers, $revenueTrackerStatistics, $affiliates) {
                $rowNumber = 1;
                $firstDataRowNumber = 1;

                $publishersRevenueCells = [];
                $curPubSubIds = '';
                $begin = 1;
                $c = 0;
                $prev_tracker = false;

                //Set auto size for sheet
                $sheet->setAutoSize(true);

                Log::info('Excel Report Affiliate Revenue Breakdown');
                $affChunks = $affiliateReportsRevenueTrackers->chunk(1000);
                $chunk_counter = 0;

                $style_total = [];
                $style_total_rev = [];
                $style_header = [];

                $cdOneCells = [];

                foreach ($affChunks as $chunk) {
                    Log::info('Chunk: '.++$chunk_counter);
                    $rows = [];

                    foreach ($chunk as $tracker) {

                        $curTrackerCode = "$tracker->affiliate_id:$tracker->revenue_tracker_id";
                        if ($curPubSubIds != $curTrackerCode) {

                            ///////Total Row of Previous Rev Tracker
                            if ($curPubSubIds != '') {

                                if ($prev_tracker->revenue_tracker_id == 1) {
                                    Log::info('Rev Tracker 1');
                                }
                                // Log::info('Total Grp');
                                $end = $rowNumber - 1;
                                //append total row for total revenue
                                $totalRevenueFormula = '=SUM(D'.$begin.':D'.($rowNumber - 1).')';
                                $totalLeadsFormula = '=SUM(C'.$begin.':C'.($rowNumber - 1).')';
                                $rows[] = [
                                    '', 'total', $totalLeadsFormula, $totalRevenueFormula,
                                ];

                                //Row for Basing Payout
                                $publishersRevenueCells[$prev_tracker->affiliate_id][$prev_tracker->revenue_tracker_id] = [
                                    'revenue_formula' => '=$D$'.$rowNumber,
                                    'leads_formula' => '=$C$'.$rowNumber,
                                ];

                                $style_total[] = $rowNumber;

                                $rowNumber++;

                                $rows[] = [
                                    '', '', '', '',
                                ];

                                $rowNumber++;

                                $style_total_rev[] = [
                                    $begin,
                                    $end,
                                ];
                            }

                            ///////Name Row
                            $curPubSubIds = $curTrackerCode;
                            $rows[] = [
                                'Publisher', 'Campaign', 'Leads', 'Revenue',
                            ];
                            $style_header[] = $rowNumber;
                            $rowNumber++;
                            $begin = $rowNumber;
                        }

                        $rows[] = [
                            $tracker->revenue_tracker_id,
                            $this->campaigns[$tracker->campaign_id],
                            $tracker->lead_count,
                            $tracker->revenue,
                        ];

                        $rowNumber++;

                        $c++;

                        $prev_tracker = $tracker;
                    }

                    $sheet->rows($rows);
                }

                //LAST Row
                if ($curPubSubIds != '') {
                    // Log::info('Total Grp');
                    $end = $rowNumber - 1;
                    //append total row for total revenue
                    $totalRevenueFormula = '=SUM(D'.$begin.':D'.($rowNumber - 1).')';
                    $totalLeadsFormula = '=SUM(C'.$begin.':C'.($rowNumber - 1).')';

                    $sheet->appendRow([
                        '', 'total', $totalLeadsFormula, $totalRevenueFormula,
                    ]);

                    //Row for Basing Payout
                    $publishersRevenueCells[$prev_tracker->affiliate_id][$prev_tracker->revenue_tracker_id] = [
                        'revenue_formula' => '=$D$'.$rowNumber,
                        'leads_formula' => '=$C$'.$rowNumber,
                    ];

                    $style_total[] = $rowNumber;

                    $rowNumber++;

                    $sheet->appendRow([
                        '', '', '', '',
                    ]);

                    $rowNumber++;

                    $style_total_rev[] = [
                        $begin,
                        $end,
                    ];
                }

                //Revenue/Cost Title
                $sheet->appendRow([
                    'REVENUE/COST', '', '', '', '', '', '',
                ]);
                $sheet->mergeCells("A$rowNumber:G$rowNumber");
                $sheet->cells("A$rowNumber:G$rowNumber", function ($cells) {
                    $cells->setFont([
                        'size' => '12',
                        'bold' => true,
                    ]);
                    $cells->setAlignment('center');
                    $cells->setValignment('center');
                    $cells->setBackground('#0000FF');
                });

                $rowNumber++;

                //Publisher Title
                $sheet->appendRow([
                    'Publisher', 'Publisher Name', 'Leads', 'Revenue', 'Clicks/Views', 'We Pay', '% Margin',
                ]);
                $sheet->cells("A$rowNumber:G$rowNumber", function ($cells) {
                    $cells->setFont([
                        'size' => '12',
                        'bold' => true,
                    ]);
                    $cells->setAlignment('center');
                    $cells->setValignment('center');
                    $cells->setBackground('#FFFF00');
                });

                $payoutFormatStart = $rowNumber;

                $rowNumber++;

                $d = 0;
                $begin = 0;
                $curAffID = '';
                $curAffRow = '';
                $curAffRevID = '';
                $curPubSubIds = '';
                $affRow = [];
                $affRevRow = [];

                Log::info('Excel Report Affiliate Payout Breakdown');
                $revChunks = $revenueTrackerStatistics->chunk(1000);
                $chunk_counter = 0;
                foreach ($revChunks as $chunk) {
                    Log::info('Chunk: '.++$chunk_counter);
                    foreach ($chunk as $tracker) {
                        //Publisher Title
                        if ($curAffID != $tracker->affiliate_id) {
                            $curAffID = $tracker->affiliate_id;
                            $curAffRow = $rowNumber;

                            $sheet->appendRow([
                                $tracker->affiliate_id, $affiliates[$tracker->affiliate_id], '', '', '', '', '',
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
                                $cells->setBackground('#538ed5');
                            });

                            $affRow[$tracker->affiliate_id] = $rowNumber;

                            $rowNumber++;
                        }

                        //Rev Tracker Title
                        $curAffRevCode = "$tracker->affiliate_id:$tracker->revenue_tracker_id";
                        if ($curAffRevID != $curAffRevCode) {
                            $curAffRevID = $curAffRevCode;
                            $affRevRow[$tracker->affiliate_id][$tracker->revenue_tracker_id] = $rowNumber;
                            $begin = $rowNumber;
                        }

                        //SubID Breakdown
                        $aff_data = isset($publishersRevenueCells[$tracker->affiliate_id][$tracker->revenue_tracker_id]) ? $publishersRevenueCells[$tracker->affiliate_id][$tracker->revenue_tracker_id] : null;

                        $lead_cell = $aff_data != null ? $aff_data['leads_formula'] : 0;
                        $revenue_cell = $aff_data != null ? $aff_data['revenue_formula'] : 0;

                        $title = $affiliates[$tracker->affiliate_id];
                        if (in_array($tracker->revenue_tracker_id, $this->jsRevTrackers)) {
                            $website_name = isset($this->revWebsites[$tracker->revenue_tracker_id]) ? $this->revWebsites[$tracker->revenue_tracker_id] : $tracker->affiliate_id;
                            $title = 'Revenue from JS Midpath for '.$website_name;
                        }

                        $sheet->appendRow([
                            $tracker->affiliate_id.'/'.$tracker->revenue_tracker_id,
                            $title,
                            $lead_cell,
                            $revenue_cell,
                            $tracker->clicks,
                            $tracker->payout,
                            "=(D$rowNumber - F$rowNumber)/ D$rowNumber",
                        ]);

                        $rowNumber++;
                        //Next Tracker
                        $nextTracker = isset($revenueTrackerStatistics[$d + 1]) ? $revenueTrackerStatistics[$d + 1] : null;
                        $nextTrackerCode = $nextTracker == null ? '' : "$nextTracker->affiliate_id:$nextTracker->revenue_tracker_id";
                        $curTrackerCode = "$tracker->affiliate_id:$tracker->revenue_tracker_id";
                        if ($curTrackerCode != $nextTrackerCode) {
                            $end = $rowNumber - 1;
                        }

                        $d++;
                    }
                }

                Log::info('Finalizing Payout Reports');
                $affiliate_rows = [];
                foreach ($affRow as $aff_id => $aRow) {
                    $aff_rows = [];
                    foreach ($affRevRow[$aff_id] as $rev_id => $rRow) {
                        $aff_rows[] = '{KK}'.$rRow;
                    }

                    if (count($aff_rows) > 0) {
                        $aff_str = implode('+', $aff_rows);
                        $leads = str_replace('{KK}', 'C', $aff_str);
                        $revenues = str_replace('{KK}', 'D', $aff_str);
                        $clicks = str_replace('{KK}', 'E', $aff_str);
                        $wePays = str_replace('{KK}', 'F', $aff_str);
                        $sheet->setCellValue("C$aRow", "=$leads");
                        $sheet->setCellValue("D$aRow", "=$revenues");
                        $sheet->setCellValue("E$aRow", "=$clicks");
                        $sheet->setCellValue("F$aRow", "=$wePays");
                        $sheet->setCellValue("G$aRow", "=(D$aRow-F$aRow)/D$aRow");
                    }

                    $affiliate_rows[] = '{KK}'.$aRow;
                }
                $affiliate_row_strings = implode('+', $affiliate_rows);
                $total_lead_cell = str_replace('{KK}', 'C', $affiliate_row_strings);
                $total_revenue_cell = str_replace('{KK}', 'D', $affiliate_row_strings);
                $total_clicks_cell = str_replace('{KK}', 'E', $affiliate_row_strings);
                $total_payout_cell = str_replace('{KK}', 'F', $affiliate_row_strings);

                //Space
                $sheet->appendRow(['', '', '', '', '', '', '']);
                $rowNumber++;

                //TOTAL ROW
                $sheet->appendRow([
                    'Total',
                    '',
                    '='.$total_lead_cell,
                    '='.$total_revenue_cell,
                    '='.$total_clicks_cell,
                    '='.$total_payout_cell,
                    "=(D$rowNumber - F$rowNumber)/ D$rowNumber",
                ]);
                $sheet->cells("A$rowNumber:G$rowNumber", function ($cells) {
                    $cells->setFont([
                        'size' => '12',
                        'bold' => true,
                    ]);
                    // $cells->setAlignment('center');
                    $cells->setValignment('center');
                    $cells->setBackground('#ebb002');
                });
                $totalRow = $rowNumber;

                //3 decimal
                $sheet->setColumnFormat([
                    "F$payoutFormatStart:F$rowNumber" => '0.000',
                ]);

                //Space
                $sheet->appendRow(['', '', '', '', '', '', '']);
                $rowNumber++;

                //PROFIT ROW
                $sheet->appendRow(['Profit']);
                $rowNumber++;
                $profitRowStart = $rowNumber;

                $sheet->appendRow(['=D'.$totalRow.' - F'.$totalRow]);
                $rowNumber++;
                $profitRowEnd = $rowNumber;
                $sheet->cells("A$profitRowStart:A$profitRowEnd", function ($cells) {
                    $cells->setFont([
                        'size' => '12',
                        'bold' => true,
                    ]);
                    $cells->setAlignment('center');
                    $cells->setValignment('center');
                    $cells->setBackground('#ebb002');
                });

                //formatting of margin percentage
                $sheet->setColumnFormat([
                    "G1:G$rowNumber" => '00.00%',
                ]);

                Log::info('Styling Revenue Reports');
                foreach ($style_total as $st) {
                    //style the total field
                    $sheet->cells("B$st:B$st", function ($cells) {
                        // Set font
                        $cells->setFont([
                            'size' => '12',
                            'bold' => true,
                        ]);

                        $cells->setAlignment('right');
                        $cells->setValignment('center');
                    });

                    //style the total value field
                    $sheet->cells("C$st:D$st", function ($cells) {
                        $cells->setAlignment('right');
                        $cells->setValignment('center');
                        $cells->setBackground('#00FF00');
                    });
                }

                foreach ($style_total_rev as $str) {
                    $begin = $str[0];
                    $end = $str[1];
                    //merge the cells in publisher column
                    $sheet->mergeCells("A$begin:A$end");

                    //set the alignment to middle
                    $sheet->cell('A'.$begin, function ($cell) {
                        $cell->setAlignment('center');
                        $cell->setValignment('top');
                    });
                }

                foreach ($style_header as $sh) {
                    //style the headers
                    $sheet->cells("A$sh:D$sh", function ($cells) {
                        // Set font
                        $cells->setFont([
                            'size' => '12',
                            'bold' => true,
                        ]);

                        $cells->setAlignment('center');
                        $cells->setValignment('center');
                        $cells->setBackground('#FF0000');
                    });
                }
                Log::info('Creating Excel File');
            });

        })->store($this->ext, storage_path('downloads'));

        // $contents = Storage::disk('downloads')->get($this->getNSBFullFileName());
        // if(config('app.type') == 'reports') {
        //     if(Storage::disk('main')->has($this->getNSBFullFileName())) Storage::disk('main')->delete($this->getNSBFullFileName()); //delete existing file
        //     Storage::disk('main')->put($this->getNSBFullFileName() , $contents); //Copy Report to Main Server

        //     if(Storage::disk('main_slave')->has($this->getNSBFullFileName())) Storage::disk('main_slave')->delete($this->getNSBFullFileName()); //delete existing file
        //     Storage::disk('main_slave')->put($this->getNSBFullFileName() , $contents); //Copy Report to Slave Server
        // }else if(config('app.type') == 'main') {
        //     if(config('app.drive') == 'master') { //if master drive, copy to slave drive
        //         if(Storage::disk('main_slave')->has($this->getNSBFullFileName())) Storage::disk('main_slave')->delete($this->getNSBFullFileName()); //delete existing file
        //         Storage::disk('main_slave')->put($this->getNSBFullFileName() , $contents); //Copy Report to Slave Server
        //     }else if(config('app.drive') == 'slave') { //slave drive, copy to main drive
        //         if(Storage::disk('main')->has($this->getNSBFullFileName())) Storage::disk('main')->delete($this->getNSBFullFileName()); //delete existing file
        //         Storage::disk('main')->put($this->getNSBFullFileName() , $contents); //Copy Report to Main Server
        //     }
        // }
    }

    protected function subIDBreakdown()
    {
        $title = $this->title;
        $sheetTitle = $this->date_from.' to '.$this->date_to;
        // DB::enableQueryLog();
        $params = [];
        $params['affiliate_type'] = $this->type;
        $params['start_date'] = $this->date_from;
        $params['end_date'] = $this->date_to;
        $key = "$params[start_date]$params[end_date]:$params[affiliate_type]";

        Log::info('Master');
        Log::info('DB::START');
        $revenueTrackerStatistics = RevenueTrackerCakeStatistic::subIDBreakdown($params)->get();
        //Get Affiliate Report Rev Tracker Campaign revenue breakdown
        $affiliateReportsRevenueTrackers = AffiliateReport::getRevTrackerPerCampaignTotalRevenue($params)
            ->where(function ($q) {
                $q->where('lead_count', '!=', 0)
                    ->orWhere('revenue', '!=', 0);
            })
            ->get();
        $affs = $revenueTrackerStatistics->map(function ($st) {
            return $st->affiliate_id;
        })->toArray();

        $affs2 = $affiliateReportsRevenueTrackers->map(function ($st) {
            return $st->affiliate_id;
        })->toArray();
        $affiliate_ids = collect(array_merge($affs, $affs2))->unique();
        //Log::info($affiliate_ids);
        $this->affiliates = Affiliate::whereIn('id', $affiliate_ids)->pluck('company', 'id');
        $affiliates = $this->affiliates;
        Log::info('DB::END');
        // Log::info(DB::getQueryLog());
        // Log::info($revenueTrackerStatistics);

        if (Storage::disk('downloads')->has($this->getFullFileName())) {
            Storage::disk('downloads')->delete($this->getFullFileName());
        } //delete existing file

        Excel::create($title, function ($excel) use ($title, $sheetTitle, $affiliateReportsRevenueTrackers, $revenueTrackerStatistics, $affiliates) {
            $excel->sheet($sheetTitle, function ($sheet) use ($title, $affiliateReportsRevenueTrackers, $revenueTrackerStatistics, $affiliates) {
                $rowNumber = 1;
                $firstDataRowNumber = 1;

                $publishersRevenueCells = [];
                $curPubSubIds = '';
                $begin = 1;
                $c = 0;
                $prev_tracker = false;

                $sheet->setWidth([
                    'B' => 20,
                    'C' => 20,
                    'D' => 20,
                    'E' => 20,
                    'F' => 20,
                ]);

                //Set auto size for sheet
                $sheet->setAutoSize(true);

                Log::info('Excel Report Affiliate Revenue Breakdown');
                $affChunks = $affiliateReportsRevenueTrackers->chunk(1000);
                $chunk_counter = 0;

                $style_total = [];
                $style_total_rev = [];
                $style_header = [];

                $cdOneCells = [];

                foreach ($affChunks as $chunk) {
                    Log::info('Chunk: '.++$chunk_counter);
                    $rows = [];

                    foreach ($chunk as $tracker) {

                        $curTrackerCode = "$tracker->affiliate_id:$tracker->revenue_tracker_id:$tracker->s1:$tracker->s2:$tracker->s3:$tracker->s4;$tracker->s5";
                        if ($curPubSubIds != $curTrackerCode) {

                            // //SPECIAL CASE FOR 1
                            // if($curPubSubIds != '' && $tracker->revenue_tracker_id == 1) {
                            //     Log::info('Rev 2');
                            //     //Row for Basing Payout
                            //     $cdOneCells = [
                            //         'revenue_formula' => '=$I$'.$rowNumber,
                            //         'leads_formula' => '=$H$'.$rowNumber
                            //     ];
                            // }

                            ///////Total Row of Previous Rev Tracker
                            if ($curPubSubIds != '') {

                                if ($prev_tracker->revenue_tracker_id == 1) {
                                    Log::info('Rev Tracker 1');
                                }
                                // Log::info('Total Grp');
                                $end = $rowNumber - 1;
                                //append total row for total revenue
                                $totalRevenueFormula = '=SUM(I'.$begin.':I'.($rowNumber - 1).')';
                                $totalLeadsFormula = '=SUM(H'.$begin.':H'.($rowNumber - 1).')';
                                $rows[] = [
                                    '', '', '', '', '', '', 'total', $totalLeadsFormula, $totalRevenueFormula,
                                ];

                                //Row for Basing Payout
                                $publishersRevenueCells[$prev_tracker->affiliate_id][$prev_tracker->revenue_tracker_id][$prev_tracker->s1][$prev_tracker->s2][$prev_tracker->s3][$prev_tracker->s4][$prev_tracker->s5] = [
                                    'revenue_formula' => '=$I$'.$rowNumber,
                                    'leads_formula' => '=$H$'.$rowNumber,
                                ];

                                $style_total[] = $rowNumber;

                                $rowNumber++;

                                $rows[] = [
                                    '', '', '', '', '', '', '', '', '',
                                ];

                                $rowNumber++;

                                $style_total_rev[] = [
                                    $begin,
                                    $end,
                                ];
                            }

                            ///////Name Row
                            $curPubSubIds = $curTrackerCode;
                            $rows[] = [
                                'Publisher', 'S1', 'S2', 'S3', 'S4', 'S5', 'Campaign', 'Leads', 'Revenue',
                            ];
                            $style_header[] = $rowNumber;
                            $rowNumber++;
                            $begin = $rowNumber;
                        }

                        $rows[] = [
                            $tracker->revenue_tracker_id,
                            $tracker->s1,
                            $tracker->s2,
                            $tracker->s3,
                            $tracker->s4,
                            $tracker->s5,
                            $this->campaigns[$tracker->campaign_id],
                            $tracker->lead_count,
                            $tracker->revenue,
                        ];

                        $rowNumber++;

                        $c++;

                        $prev_tracker = $tracker;
                    }

                    $sheet->rows($rows);
                }

                ///////Total Row of Previous Rev Tracker
                if ($curPubSubIds != '') {

                    if ($prev_tracker->revenue_tracker_id == 1) {
                        Log::info('Rev Tracker 1');
                    }
                    // Log::info('Total Grp');
                    $end = $rowNumber - 1;
                    //append total row for total revenue
                    $totalRevenueFormula = '=SUM(I'.$begin.':I'.($rowNumber - 1).')';
                    $totalLeadsFormula = '=SUM(H'.$begin.':H'.($rowNumber - 1).')';

                    $sheet->appendRow([
                        '', '', '', '', '', '', 'total', $totalLeadsFormula, $totalRevenueFormula,
                    ]);

                    //Row for Basing Payout
                    $publishersRevenueCells[$prev_tracker->affiliate_id][$prev_tracker->revenue_tracker_id][$prev_tracker->s1][$prev_tracker->s2][$prev_tracker->s3][$prev_tracker->s4][$prev_tracker->s5] = [
                        'revenue_formula' => '=$I$'.$rowNumber,
                        'leads_formula' => '=$H$'.$rowNumber,
                    ];

                    $style_total[] = $rowNumber;

                    $rowNumber++;

                    $sheet->appendRow([
                        '', '', '', '', '', '', '', '', '',
                    ]);

                    $rowNumber++;

                    $style_total_rev[] = [
                        $begin,
                        $end,
                    ];
                }

                //Revenue/Cost Title
                $sheet->appendRow([
                    'REVENUE/COST', '', '', '', '', '', '', '', '', '', '', '',
                ]);
                $sheet->mergeCells("A$rowNumber:L$rowNumber");
                $sheet->cells("A$rowNumber:L$rowNumber", function ($cells) {
                    $cells->setFont([
                        'size' => '12',
                        'bold' => true,
                    ]);
                    $cells->setAlignment('center');
                    $cells->setValignment('center');
                    $cells->setBackground('#0000FF');
                });

                $rowNumber++;

                //Publisher Title
                $sheet->appendRow([
                    'Publisher', 'S1', 'S2', 'S3', 'S4', 'S5', 'Publisher Name', 'Leads', 'Revenue', 'Clicks/Views', 'We Pay', '% Margin',
                ]);
                $sheet->cells("A$rowNumber:L$rowNumber", function ($cells) {
                    $cells->setFont([
                        'size' => '12',
                        'bold' => true,
                    ]);
                    $cells->setAlignment('center');
                    $cells->setValignment('center');
                    $cells->setBackground('#FFFF00');
                });

                $payoutFormatStart = $rowNumber;

                $rowNumber++;

                $d = 0;
                $begin = 0;
                $curAffID = '';
                $curAffRow = '';
                $curAffRevID = '';
                $curPubSubIds = '';
                $affRow = [];
                $affRevRow = [];

                // Log::info('KARLA1');
                // // Log::info($cdOneCells);
                // Log::info($publishersRevenueCells);

                Log::info('Excel Report Affiliate Payout Breakdown');
                $revChunks = $revenueTrackerStatistics->chunk(1000);
                $chunk_counter = 0;
                foreach ($revChunks as $chunk) {
                    Log::info('Chunk: '.++$chunk_counter);
                    foreach ($chunk as $tracker) {

                        //Publisher Title
                        if ($curAffID != $tracker->affiliate_id) {
                            $curAffID = $tracker->affiliate_id;
                            $curAffRow = $rowNumber;

                            $title = $affiliates[$tracker->affiliate_id];

                            $sheet->appendRow([
                                $tracker->affiliate_id, '', '', '', '', '', $title, '', '', '', '', '',
                            ]);

                            //style the header below the REVENUE/COST header
                            $sheet->cells("A$rowNumber:L$rowNumber", function ($cells) {
                                // Set font
                                $cells->setFont([
                                    'size' => '12',
                                    'bold' => true,
                                ]);

                                $cells->setAlignment('center');
                                $cells->setValignment('center');
                                $cells->setBackground('#538ed5');
                            });

                            $affRow[$tracker->affiliate_id] = $rowNumber;

                            $rowNumber++;
                        }

                        //Rev Tracker Title
                        $curAffRevCode = "$tracker->affiliate_id:$tracker->revenue_tracker_id";
                        if ($curAffRevID != $curAffRevCode) {
                            $curAffRevID = $curAffRevCode;
                            //set up the title header row

                            $title = $affiliates[$tracker->affiliate_id];

                            $sheet->appendRow([
                                $tracker->affiliate_id.'/'.$tracker->revenue_tracker_id,
                                '',
                                '',
                                '',
                                '',
                                '',
                                $title,
                                '',
                                '',
                                '',
                                '',
                                '',
                            ]);

                            //style the headers
                            $sheet->cells("A$rowNumber:L$rowNumber", function ($cells) {
                                $cells->setFont([
                                    'size' => '12',
                                    'bold' => true,
                                ]);
                                $cells->setAlignment('center');
                                $cells->setValignment('center');
                                $cells->setBackground('#c9c9c9');
                            });

                            $affRevRow[$tracker->affiliate_id][$tracker->revenue_tracker_id] = $rowNumber;

                            $rowNumber++;
                            $begin = $rowNumber;
                        }

                        //SubID Breakdown
                        $aff_data = isset($publishersRevenueCells[$tracker->affiliate_id][$tracker->revenue_tracker_id][$tracker->s1][$tracker->s2][$tracker->s3][$tracker->s4][$tracker->s5]) ? $publishersRevenueCells[$tracker->affiliate_id][$tracker->revenue_tracker_id][$tracker->s1][$tracker->s2][$tracker->s3][$tracker->s4][$tracker->s5] : null;
                        $lead_cell = $aff_data != null ? $aff_data['leads_formula'] : 0;
                        $revenue_cell = $aff_data != null ? $aff_data['revenue_formula'] : 0;

                        // if($tracker->revenue_tracker_id == 1) {
                        //     Log::info('KARLA KARLAX!');
                        //     Log::info($tracker);
                        //     Log::info($aff_data);
                        //     Log::info($rowNumber);
                        //     Log::info($lead_cell);
                        //     Log::info($revenue_cell);
                        // }
                        // Log::info($tracker->affiliate_id.' - '.$tracker->revenue_tracker_id.' - '. $lead_cell.' - '. $revenue_cell);

                        // $sheet->appendRow([
                        //     $tracker->affiliate_id.'/'.$tracker->revenue_tracker_id,
                        //     $tracker->s1,
                        //     $tracker->s2,
                        //     $tracker->s3,
                        //     $tracker->s4,
                        //     $tracker->s5,
                        //     $affiliates[$tracker->affiliate_id],
                        //     $lead_cell,
                        //     $revenue_cell,
                        //     $tracker->clicks,
                        //     $tracker->payout,
                        //     "=(I$rowNumber - K$rowNumber)/ I$rowNumber"
                        // ]);

                        $title = $affiliates[$tracker->affiliate_id];
                        if (in_array($tracker->revenue_tracker_id, $this->jsRevTrackers)) {
                            $website_name = isset($this->revWebsites[$tracker->revenue_tracker_id]) ? $this->revWebsites[$tracker->revenue_tracker_id] : $tracker->affiliate_id;
                            $title = 'Revenue from JS Midpath for '.$website_name;
                        }

                        $sheet->setCellValueExplicit("A$rowNumber", $tracker->affiliate_id.'/'.$tracker->revenue_tracker_id, PHPExcel_Cell_DataType::TYPE_STRING);
                        $sheet->setCellValueExplicit("B$rowNumber", $tracker->s1, PHPExcel_Cell_DataType::TYPE_STRING);
                        $sheet->setCellValueExplicit("C$rowNumber", $tracker->s2, PHPExcel_Cell_DataType::TYPE_STRING);
                        $sheet->setCellValueExplicit("D$rowNumber", $tracker->s3, PHPExcel_Cell_DataType::TYPE_STRING);
                        $sheet->setCellValueExplicit("E$rowNumber", $tracker->s4, PHPExcel_Cell_DataType::TYPE_STRING);
                        $sheet->setCellValueExplicit("F$rowNumber", $tracker->s5, PHPExcel_Cell_DataType::TYPE_STRING);
                        $sheet->setCellValue("G$rowNumber", $title);
                        $sheet->setCellValue("H$rowNumber", $lead_cell);
                        $sheet->setCellValue("I$rowNumber", $revenue_cell);
                        $sheet->setCellValue("J$rowNumber", $tracker->clicks);
                        $sheet->setCellValue("K$rowNumber", $tracker->payout);
                        $sheet->setCellValue("L$rowNumber", "=(I$rowNumber - K$rowNumber)/ I$rowNumber");

                        $rowNumber++;

                        //Next Tracker
                        $nextTracker = isset($revenueTrackerStatistics[$d + 1]) ? $revenueTrackerStatistics[$d + 1] : null;
                        $nextTrackerCode = $nextTracker == null ? '' : "$nextTracker->affiliate_id:$nextTracker->revenue_tracker_id:$nextTracker->s1:$nextTracker->s2:$nextTracker->s3:$nextTracker->s4;$nextTracker->s5";
                        $curTrackerCode = "$tracker->affiliate_id:$tracker->revenue_tracker_id:$tracker->s1:$tracker->s2:$tracker->s3:$tracker->s4;$tracker->s5";
                        if ($curTrackerCode != $nextTrackerCode) {
                            $end = $rowNumber - 1;
                            $aff_rev_row = $affRevRow[$tracker->affiliate_id][$tracker->revenue_tracker_id];

                            // Log::info($tracker);
                            // Log::info($aff_rev_row);

                            $sheet->setCellValue("H$aff_rev_row", "=SUM(H$begin:H$end)");
                            $sheet->setCellValue("I$aff_rev_row", "=SUM(I$begin:I$end)");
                            $sheet->setCellValue("J$aff_rev_row", "=SUM(J$begin:J$end)");
                            $sheet->setCellValue("K$aff_rev_row", "=SUM(K$begin:K$end)");
                            $sheet->setCellValue("L$aff_rev_row", "=(I$aff_rev_row-K$aff_rev_row)/I$aff_rev_row");
                        }

                        $d++;
                    }
                }

                Log::info('Finalizing Payout Reports');
                $affiliate_rows = [];
                foreach ($affRow as $aff_id => $aRow) {
                    $aff_rows = [];
                    foreach ($affRevRow[$aff_id] as $rev_id => $rRow) {
                        $aff_rows[] = '{KK}'.$rRow;
                    }

                    if (count($aff_rows) > 0) {
                        $aff_str = implode('+', $aff_rows);
                        $leads = str_replace('{KK}', 'H', $aff_str);
                        $revenues = str_replace('{KK}', 'I', $aff_str);
                        $clicks = str_replace('{KK}', 'J', $aff_str);
                        $wePays = str_replace('{KK}', 'K', $aff_str);
                        $sheet->setCellValue("H$aRow", "=$leads");
                        $sheet->setCellValue("I$aRow", "=$revenues");
                        $sheet->setCellValue("J$aRow", "=$clicks");
                        $sheet->setCellValue("K$aRow", "=$wePays");
                        $sheet->setCellValue("L$aRow", "=(I$aRow-K$aRow)/I$aRow");
                    }

                    $affiliate_rows[] = '{KK}'.$aRow;
                }
                $affiliate_row_strings = implode('+', $affiliate_rows);
                $total_lead_cell = str_replace('{KK}', 'H', $affiliate_row_strings);
                $total_revenue_cell = str_replace('{KK}', 'I', $affiliate_row_strings);
                $total_clicks_cell = str_replace('{KK}', 'J', $affiliate_row_strings);
                $total_payout_cell = str_replace('{KK}', 'K', $affiliate_row_strings);

                //Space
                $sheet->appendRow(['', '', '', '', '', '', '', '', '', '', '', '']);
                $rowNumber++;

                //TOTAL ROW
                $sheet->appendRow([
                    'Total',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '='.$total_lead_cell,
                    '='.$total_revenue_cell,
                    '='.$total_clicks_cell,
                    '='.$total_payout_cell,
                    "=(I$rowNumber - K$rowNumber)/ I$rowNumber",
                ]);
                $sheet->cells("A$rowNumber:L$rowNumber", function ($cells) {
                    $cells->setFont([
                        'size' => '12',
                        'bold' => true,
                    ]);
                    // $cells->setAlignment('center');
                    $cells->setValignment('center');
                    $cells->setBackground('#ebb002');
                });
                $totalRow = $rowNumber;

                //3 decimal
                $sheet->setColumnFormat([
                    "F$payoutFormatStart:F$rowNumber" => '0.000',
                ]);

                //Space
                $sheet->appendRow(['', '', '', '', '', '', '', '', '', '', '', '']);
                $rowNumber++;

                //PROFIT ROW
                $sheet->appendRow(['Profit']);
                $rowNumber++;
                $profitRowStart = $rowNumber;

                $sheet->appendRow(['=I'.$totalRow.' - K'.$totalRow]);
                $rowNumber++;
                $profitRowEnd = $rowNumber;
                $sheet->cells("A$profitRowStart:A$profitRowEnd", function ($cells) {
                    $cells->setFont([
                        'size' => '12',
                        'bold' => true,
                    ]);
                    $cells->setAlignment('center');
                    $cells->setValignment('center');
                    $cells->setBackground('#ebb002');
                });

                //formatting of margin percentage
                $sheet->setColumnFormat([
                    "L1:L$rowNumber" => '00.00%',
                ]);

                Log::info('Styling Revenue Reports');
                foreach ($style_total as $st) {
                    //style the total field
                    $sheet->cells("G$st:G$st", function ($cells) {
                        // Set font
                        $cells->setFont([
                            'size' => '12',
                            'bold' => true,
                        ]);

                        $cells->setAlignment('right');
                        $cells->setValignment('center');
                    });

                    //style the total value field
                    $sheet->cells("H$st:I$st", function ($cells) {
                        $cells->setAlignment('right');
                        $cells->setValignment('center');
                        $cells->setBackground('#00FF00');
                    });
                }

                foreach ($style_total_rev as $str) {
                    $begin = $str[0];
                    $end = $str[1];
                    //merge the cells in publisher column
                    $sheet->mergeCells("A$begin:A$end");
                    $sheet->mergeCells("B$begin:B$end");
                    $sheet->mergeCells("C$begin:C$end");
                    $sheet->mergeCells("D$begin:D$end");
                    $sheet->mergeCells("E$begin:E$end");
                    $sheet->mergeCells("F$begin:F$end");

                    //set the alignment to middle
                    $sheet->cell('A'.$begin, function ($cell) {
                        $cell->setAlignment('center');
                        $cell->setValignment('top');
                    });
                    $sheet->cell('B'.$begin, function ($cell) {
                        $cell->setAlignment('center');
                        $cell->setValignment('top');
                    });
                    $sheet->cell('C'.$begin, function ($cell) {
                        $cell->setAlignment('center');
                        $cell->setValignment('top');
                    });
                    $sheet->cell('D'.$begin, function ($cell) {
                        $cell->setAlignment('center');
                        $cell->setValignment('top');
                    });
                    $sheet->cell('E'.$begin, function ($cell) {
                        $cell->setAlignment('center');
                        $cell->setValignment('top');
                    });
                    $sheet->cell('F'.$begin, function ($cell) {
                        $cell->setAlignment('center');
                        $cell->setValignment('top');
                    });
                }

                foreach ($style_header as $sh) {
                    //style the headers
                    $sheet->cells("A$sh:I$sh", function ($cells) {
                        // Set font
                        $cells->setFont([
                            'size' => '12',
                            'bold' => true,
                        ]);

                        $cells->setAlignment('center');
                        $cells->setValignment('center');
                        $cells->setBackground('#FF0000');
                    });
                }
                Log::info('Creating Excel File');
            });

        })->store($this->ext, storage_path('downloads'));

        // $contents = Storage::disk('downloads')->get($this->getFullFileName());
        // if(config('app.type') == 'reports') {
        //     if(Storage::disk('main')->has($this->getFullFileName())) Storage::disk('main')->delete($this->getFullFileName()); //delete existing file
        //     Storage::disk('main')->put($this->getFullFileName() , $contents); //Copy Report to Main Server

        //     if(Storage::disk('main_slave')->has($this->getFullFileName())) Storage::disk('main_slave')->delete($this->getFullFileName()); //delete existing file
        //     Storage::disk('main_slave')->put($this->getFullFileName() , $contents); //Copy Report to Slave Server
        // }else if(config('app.type') == 'main') {
        //     if(config('app.drive') == 'master') { //if master drive, copy to slave drive
        //         if(Storage::disk('main_slave')->has($this->getFullFileName())) Storage::disk('main_slave')->delete($this->getFullFileName()); //delete existing file
        //         Storage::disk('main_slave')->put($this->getFullFileName() , $contents); //Copy Report to Slave Server
        //     }else if(config('app.drive') == 'slave') { //slave drive, copy to main drive
        //         if(Storage::disk('main')->has($this->getFullFileName())) Storage::disk('main')->delete($this->getFullFileName()); //delete existing file
        //         Storage::disk('main')->put($this->getFullFileName() , $contents); //Copy Report to Main Server
        //     }
        // }
    }

    /**
     * Downloading of generated file.
     *
     * @param  Request  $request
     */
    public function download(): BinaryFileResponse
    {
        $fileNameToDownload = $this->getFullFileName();
        $filePathToDownload = storage_path('downloads')."/$fileNameToDownload";

        if (file_exists($filePathToDownload)) {
            return response()->download($filePathToDownload, $fileNameToDownload, [
                'Content-Length: '.filesize($filePathToDownload),
            ]);
        } else {
            $this->generate();

            $fileNameToDownload = $this->getFullFileName();
            $filePathToDownload = storage_path('downloads')."/$fileNameToDownload";

            return response()->download($filePathToDownload, $fileNameToDownload, [
                'Content-Length: '.filesize($filePathToDownload),
            ]);
        }
    }

    public function getFileName()
    {
        return $this->title;
    }

    public function getFullFileName()
    {
        return $this->title.'.'.$this->ext;
    }

    public function getTempFileName()
    {
        return $this->title.'.prc';
    }

    public function getPathToFile()
    {
        return storage_path('downloads').'/'.$this->getFullFileName();
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function noSimilarRunningJob()
    {
        $tempFile = $this->getTempFileName();
        if (! file_exists(storage_path('downloads')."/$tempFile")) {
            return true;
        } else {
            return false;
        }
    }

    public function getNSBFullFileName()
    {
        return $this->nsb_title.'.'.$this->ext;
    }

    public function getNSBTempFileName()
    {
        return $this->nsb_title.'.prc';
    }

    public function deleteTempFile()
    {
        $disk = $this->disk;
        if (! $this->noSimilarRunningJob()) {
            Storage::disk($disk)->delete($this->getTempFileName()); //delete templ file
        }
    }
}
