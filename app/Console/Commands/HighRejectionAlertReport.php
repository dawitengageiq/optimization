<?php

namespace App\Console\Commands;

use App\Affiliate;
use App\AffiliateRevenueTracker;
use App\Campaign;
use App\Http\Services;
use App\Lead;
use App\Setting;
use Artisan;
use Cache;
use Carbon\Carbon;
use Config;
use DB;
use File;
use Illuminate\Console\Command;
use Log;
use Maatwebsite\Excel\Facades\Excel;
use Mail;
use Storage;

class HighRejectionAlertReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:high-rejection-alert-report
                        {--date= : The date of the query for all the leads (YYYY-MM-DD).}
                        ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This will execute the gathering and emailing of report about high rejection among leads.';

    protected $logger;

    /**
     * Create a new command instance.
     */
    public function __construct(Services\UserActionLogger $logger)
    {
        parent::__construct();

        $this->logger = $logger;
        // $this->logger = new UserActionLogger;
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $lr_build = env('APP_BUILD', 'NLR');
        $date = $this->option('date');
        if (empty($date)) {
            $date = Carbon::yesterday()->toDateString();
        }
        // $date_yesterday = '2019-07-15'; //For Testing
        $this->info('Generating Report for '.$date);
        Log::info('Generating High Rejection Report for '.$date);

        //get settings
        $settings = Setting::whereIn('code', ['high_critical_rejection_rate', 'report_recipient', 'qa_recipient', 'high_rejection_keywords', 'full_rejection_alert_status', 'full_rejection_alert_min_leads', 'full_rejection_alert_check_days', 'full_rejection_advertiser_email_status', 'full_rejection_excluded_campaigns', 'full_rejection_deactivate_campaign_status'])->get();
        foreach ($settings as $set) {
            switch ($set->code) {
                case 'high_critical_rejection_rate':
                    $high_rates = json_decode($set->string_value);
                    break;
                case 'report_recipient':
                    $report_recipients = [];
                    if ($set->description != '') {
                        $rep_emails = explode(';', $set->description);
                        foreach ($rep_emails as $re) {
                            $re = trim($re);
                            if (filter_var($re, FILTER_VALIDATE_EMAIL)) {
                                $report_recipients[] = $re;
                            }
                        }
                    }
                    if (count($report_recipients) == 0) {
                        $report_recipients = ['karla@engageiq.com'];
                    }

                    break;
                case 'qa_recipient':
                    $qa_recipients = [];
                    if ($set->description != '') {
                        $qa_emails = explode(';', $set->description);
                        foreach ($qa_emails as $qa) {
                            $qa = trim($qa);
                            if (filter_var($qa, FILTER_VALIDATE_EMAIL)) {
                                $qa_recipients[] = $qa;
                            }
                        }
                    }

                    if (count($qa_recipients) == 0) {
                        $qa_recipients = ['karla@engageiq.com'];
                    }
                    break;
                case 'high_rejection_keywords':
                    $keywords = json_decode($set->description, true);
                    $duplicate_keywords = array_filter(array_map('trim', array_map('strtolower', explode(',', $keywords['d']))));
                    $prepop_keywords = array_filter(array_map('trim', array_map('strtolower', explode(',', $keywords['p']))));
                    $filter_keywords = array_filter(array_map('trim', array_map('strtolower', explode(',', $keywords['f']))));
                    $accepted_keywords = array_filter(array_map('trim', array_map('strtolower', explode(',', $keywords['a']))));
                    break;
                case 'full_rejection_alert_status':
                    $full_rejection_alert_status = $set->integer_value;
                    break;
                case 'full_rejection_alert_min_leads':
                    $full_rejection_alert_min_leads = $set->description;
                    break;
                case 'full_rejection_alert_check_days':
                    $full_rejection_alert_check_days = $set->description;
                    break;
                case 'full_rejection_advertiser_email_status':
                    $full_rejection_advertiser_email_status = $set->integer_value;
                    break;
                case 'full_rejection_excluded_campaigns':
                    $full_rejection_excluded_campaigns = array_map('trim', explode(',', $set->description));
                    break;
                case 'full_rejection_deactivate_campaign_status':
                    $full_rejection_deactivate_campaign_status = $set->integer_value;
                    break;
            }
        }
        // $rejection_rates = Setting::where('code','high_critical_rejection_rate')->first();
        // $high_rates = json_decode($rejection_rates->string_value);

        /**
            NLR PART
         **/
        $this->info('Initiating high rejection alert report for '.$lr_build.'...');
        $leads = Lead::leftJoin('lead_sent_results', 'leads.id', '=', 'lead_sent_results.id')
            ->join('campaigns', 'leads.campaign_id', '=', 'campaigns.id')
            ->where(DB::RAW('date(leads.created_at)'), $date)
            ->where('campaigns.status', '!=', 0)
            ->select(DB::RAW('leads.id, leads.affiliate_id, leads.campaign_id, leads.lead_status, lead_sent_results.value as sent_result'))
            ->get();

        // DB::select("SELECT l.id, l.affiliate_id, l.campaign_id, l.lead_status, s.value as sent_result FROM `leads` l LEFT JOIN lead_sent_results s ON l.id = s.id
        //     INNER JOIN campaigns c ON l.campaign_id = c.id
        //     WHERE date(l.created_at) = '$date_yesterday' AND c.status != 0");

        $report = [];
        $result = [];
        $campaign_reports = [];
        $cids = [];
        $affids = [];

        foreach ($leads as $lead) {
            if (! isset($report[$lead->campaign_id][$lead->affiliate_id])) {
                $report[$lead->campaign_id][$lead->affiliate_id] = [];
                $cids[] = $lead->campaign_id;
                $affids[] = $lead->affiliate_id;
            }

            if (! isset($campaign_reports[$lead->campaign_id])) {
                $campaign_reports[$lead->campaign_id] = [];
            }

            /*FOR CHECKING*/ if (! isset($result[$lead->campaign_id][$lead->affiliate_id])) {
                $result[$lead->campaign_id][$lead->affiliate_id] = [];
            }

            //get total count
            if (! array_key_exists('total_count', $report[$lead->campaign_id][$lead->affiliate_id])) {
                $report[$lead->campaign_id][$lead->affiliate_id]['total_count'] = 0;
            }
            $report[$lead->campaign_id][$lead->affiliate_id]['total_count'] += 1;

            //for campaigns total count
            if (! array_key_exists('total_count', $campaign_reports[$lead->campaign_id])) {
                $campaign_reports[$lead->campaign_id]['total_count'] = 0;
            }
            $campaign_reports[$lead->campaign_id]['total_count'] += 1;

            //if rejected lead
            if ($lead->lead_status == 2 || $lead->lead_status == 0) {
                $lead_status = strtolower($lead->sent_result);

                //get reject count & rejected percentage
                if (! array_key_exists('reject_count', $report[$lead->campaign_id][$lead->affiliate_id])) {
                    $report[$lead->campaign_id][$lead->affiliate_id]['reject_count'] = 0;
                }
                $report[$lead->campaign_id][$lead->affiliate_id]['reject_count'] += 1;

                //for campaign reports - get reject count & rejected percentage
                if (! array_key_exists('reject_count', $campaign_reports[$lead->campaign_id])) {
                    $campaign_reports[$lead->campaign_id]['reject_count'] = 0;
                }
                $campaign_reports[$lead->campaign_id]['reject_count'] += 1;

                if ($this->strposa($lead_status, $accepted_keywords)) { //Acceptable Rejects
                    if (! array_key_exists('acceptable_count', $report[$lead->campaign_id][$lead->affiliate_id])) {
                        $report[$lead->campaign_id][$lead->affiliate_id]['acceptable_count'] = 0;
                    }
                    $report[$lead->campaign_id][$lead->affiliate_id]['acceptable_count'] += 1;

                    if (! array_key_exists('acceptable_count', $campaign_reports[$lead->campaign_id])) {
                        $campaign_reports[$lead->campaign_id]['acceptable_count'] = 0;
                    }
                    $campaign_reports[$lead->campaign_id]['acceptable_count'] += 1;

                    /*FOR CHECKING*/ $result[$lead->campaign_id][$lead->affiliate_id]['acceptable'][] = $lead_status;
                } elseif ($this->strposa($lead_status, $duplicate_keywords)) { //Duplicate Rejects
                    if (! array_key_exists('duplicate_count', $report[$lead->campaign_id][$lead->affiliate_id])) {
                        $report[$lead->campaign_id][$lead->affiliate_id]['duplicate_count'] = 0;
                    }
                    $report[$lead->campaign_id][$lead->affiliate_id]['duplicate_count'] += 1;

                    if (! array_key_exists('duplicate_count', $campaign_reports[$lead->campaign_id])) {
                        $campaign_reports[$lead->campaign_id]['duplicate_count'] = 0;
                    }
                    $campaign_reports[$lead->campaign_id]['duplicate_count'] += 1;

                    /*FOR CHECKING*/ $result[$lead->campaign_id][$lead->affiliate_id]['duplicate'][] = $lead_status;
                } elseif ($this->strposa($lead_status, $prepop_keywords)) { //Pre-pop Rejects
                    if (! array_key_exists('prepop_count', $report[$lead->campaign_id][$lead->affiliate_id])) {
                        $report[$lead->campaign_id][$lead->affiliate_id]['prepop_count'] = 0;
                    }
                    $report[$lead->campaign_id][$lead->affiliate_id]['prepop_count'] += 1;

                    if (! array_key_exists('prepop_count', $campaign_reports[$lead->campaign_id])) {
                        $campaign_reports[$lead->campaign_id]['prepop_count'] = 0;
                    }
                    $campaign_reports[$lead->campaign_id]['prepop_count'] += 1;

                    /*FOR CHECKING*/ $result[$lead->campaign_id][$lead->affiliate_id]['prepop'][] = $lead_status;
                } elseif ($this->strposa($lead_status, $filter_keywords)) { //Filter Rejects
                    if (! array_key_exists('filter_count', $report[$lead->campaign_id][$lead->affiliate_id])) {
                        $report[$lead->campaign_id][$lead->affiliate_id]['filter_count'] = 0;
                    }
                    $report[$lead->campaign_id][$lead->affiliate_id]['filter_count'] += 1;

                    if (! array_key_exists('filter_count', $campaign_reports[$lead->campaign_id])) {
                        $campaign_reports[$lead->campaign_id]['filter_count'] = 0;
                    }
                    $campaign_reports[$lead->campaign_id]['filter_count'] += 1;

                    /*FOR CHECKING*/ $result[$lead->campaign_id][$lead->affiliate_id]['filter'][] = $lead_status;
                } else {
                    if (! array_key_exists('other_count', $report[$lead->campaign_id][$lead->affiliate_id])) {
                        $report[$lead->campaign_id][$lead->affiliate_id]['other_count'] = 0;
                    }
                    $report[$lead->campaign_id][$lead->affiliate_id]['other_count'] += 1;

                    if (! array_key_exists('other_count', $campaign_reports[$lead->campaign_id])) {
                        $campaign_reports[$lead->campaign_id]['other_count'] = 0;
                    }
                    $campaign_reports[$lead->campaign_id]['other_count'] += 1;

                    /*FOR CHECKING*/ $result[$lead->campaign_id][$lead->affiliate_id]['other'][] = $lead_status;
                }
            }

            //Get Reject Percentage
            if (isset($report[$lead->campaign_id][$lead->affiliate_id]['reject_count']) && $report[$lead->campaign_id][$lead->affiliate_id]['reject_count'] > 0) {
                $report[$lead->campaign_id][$lead->affiliate_id]['reject_percentage'] = $report[$lead->campaign_id][$lead->affiliate_id]['reject_count'] / $report[$lead->campaign_id][$lead->affiliate_id]['total_count'];
            }

            //For Campaigns Report Get Reject Percentage
            if (isset($campaign_reports[$lead->campaign_id]['reject_count']) && $campaign_reports[$lead->campaign_id]['reject_count'] > 0) {
                $campaign_reports[$lead->campaign_id]['reject_percentage'] = $campaign_reports[$lead->campaign_id]['reject_count'] / $campaign_reports[$lead->campaign_id]['total_count'];
            }
        }
        // Log::info('- filter rejection done.');

        //remove unwanted lead details
        $fake_id = 1;
        foreach ($report as $campaign => $affiliates) {
            foreach ($affiliates as $affiliate => $details) {
                if (! isset($details['reject_count']) || ! isset($details['reject_percentage']) || (isset($details['reject_percentage']) && $details['reject_percentage'] < ($high_rates[0] / 100))) {
                    // Log::info('Remove: '.$campaign.' - '.$affiliate);
                    // Log::info($report[$campaign][$affiliate]);
                    unset($report[$campaign][$affiliate]);
                    /*FOR CHECKING*/ unset($result[$campaign][$affiliate]);
                } else {
                    $sort_total[$fake_id] = $details['total_count'];
                    $sort_campaigns[$fake_id] = $campaign;
                    $sort_affiliates[$fake_id] = $affiliate;
                    $fake_id++;
                }
            }

            if (count($report[$campaign]) == 0) {
                unset($report[$campaign]);
                /*FOR CHECKING*/ unset($result[$campaign]);
            }
        }

        //for campaign reports - remove unwanted lead details
        $cfake_id = 1;
        $csort_total = [];
        $csort_campaigns = [];
        foreach ($campaign_reports as $campaign => $details) {
            if (! isset($details['reject_count']) || ! isset($details['reject_percentage']) || (isset($details['reject_percentage']) && $details['reject_percentage'] < ($high_rates[0] / 100))) {
                // Log::info('1Remove: '.$campaign);
                // Log::info($campaign_reports[$campaign]);
                unset($campaign_reports[$campaign]);
            } else {
                $csort_total[$cfake_id] = $details['total_count'];
                $csort_campaigns[$cfake_id] = $campaign;
                $cfake_id++;
            }
        }
        // Log::info('- removing unwanted lead details.');

        $the_campaigns = Campaign::whereIn('id', $cids)->pluck('name', 'id');
        $the_affiliate_ids = AffiliateRevenueTracker::whereIn('affiliate_revenue_trackers.revenue_tracker_id', $affids)->pluck('affiliate_id', 'revenue_tracker_id')->toArray();
        $the_affiliates = Affiliate::whereIn('id', array_merge($affids, array_values($the_affiliate_ids)))
            ->pluck('company', 'id');

        $full_rejects = [];
        $full_reject_campaigns = [];

        /**
         BY CAMPAIGN BY AFFILIATE
         *
         * */
        $this->info('Starting Affiliate Report');
        // Log::info('- starting affiliate report.');
        if (count($report) > 0) {
            //Sort by total count
            arsort($sort_total);

            $critical_rate_exists = 0;

            $reports = [];
            $duplicates = [];
            $c = 0; //Actual rejection counter
            $d = 0; //Duplicate counter

            //finalize computations
            // Log::info('----------START-----------');

            foreach ($sort_total as $fid => $total_count) {

                $only_duplicates = false; //variable to check whether entry is 100% duplicates only

                $revenue_tracker_id = $sort_affiliates[$fid];
                $affiliate_id = isset($the_affiliate_ids[$revenue_tracker_id]) ? $the_affiliate_ids[$revenue_tracker_id] : $revenue_tracker_id;
                $campaign_id = $sort_campaigns[$fid];
                $reject_count = $report[$campaign_id][$revenue_tracker_id]['reject_count'];
                $rejection_percentage = sprintf('%.2f', ($report[$campaign_id][$revenue_tracker_id]['reject_percentage'] * 100));

                if ($rejection_percentage >= $high_rates[0] && $rejection_percentage <= $high_rates[1]) {
                    $reject_rate = 'HIGH';
                } else {
                    $reject_rate = 'CRITICAL';
                    $critical_rate_exists = 1;
                }

                // Log::info('--------------------------');
                // Log::info('Campaign ID: '.$campaign_id);
                // Log::info('Affiliate ID: '.$affiliate_id);
                // Log::info('Lead Count: '.$total_count);
                // Log::info('Rejection Count: '.$reject_count);
                // Log::info('Rejection Percentage: '.$rejection_percentage);
                // Log::info('Rejection Rate: '.$reject_rate);

                $rejection_split = '';
                if (isset($report[$campaign_id][$revenue_tracker_id]['acceptable_count'])) {
                    $acceptable_ratio = $report[$campaign_id][$revenue_tracker_id]['acceptable_count'] / $reject_count;
                    $acceptable_percentage = sprintf('%.2f', $acceptable_ratio * 100);
                    $rejection_split .= 'Acceptable = <b>'.$acceptable_percentage.'%</b>, ';
                    // Log::info('---');
                    // Log::info('Duplicate Count: '.$report[$campaign_id][$affiliate_id]['duplicate_count']);
                    // Log::info('Duplicate Percentage: '.$duplicate_percentage);
                }
                if (isset($report[$campaign_id][$revenue_tracker_id]['duplicate_count'])) {
                    $duplicate_ratio = $report[$campaign_id][$revenue_tracker_id]['duplicate_count'] / $reject_count;
                    if ($duplicate_ratio == 1) {
                        $only_duplicates = true;
                    }
                    $duplicate_percentage = sprintf('%.2f', $duplicate_ratio * 100);
                    $rejection_split .= 'Duplicates = <b>'.$duplicate_percentage.'%</b>, ';
                    // Log::info('---');
                    // Log::info('Duplicate Count: '.$report[$campaign_id][$affiliate_id]['duplicate_count']);
                    // Log::info('Duplicate Percentage: '.$duplicate_percentage);
                }
                if (isset($report[$campaign_id][$revenue_tracker_id]['filter_count'])) {
                    $filter_percentage = sprintf('%.2f', ($report[$campaign_id][$revenue_tracker_id]['filter_count'] / $reject_count) * 100);
                    $rejection_split .= 'Filter Issue = <b>'.$filter_percentage.'%</b>, ';
                    // Log::info('---');
                    // Log::info('Filter Count: '.$report[$campaign_id][$affiliate_id]['filter_count']);
                    // Log::info('Filter Percentage: '.$filter_percentage);
                }
                if (isset($report[$campaign_id][$revenue_tracker_id]['prepop_count'])) {
                    $prepop_percentage = sprintf('%.2f', ($report[$campaign_id][$revenue_tracker_id]['prepop_count'] / $reject_count) * 100);
                    $rejection_split .= 'Pre-pop Issue = <b>'.$prepop_percentage.'%</b>, ';
                    // Log::info('---');
                    // Log::info('Pre-Pop Count: '.$report[$campaign_id][$affiliate_id]['prepop_count']);
                    // Log::info('Pre-Pop Percentage: '.$prepop_percentage);
                }
                if (isset($report[$campaign_id][$revenue_tracker_id]['other_count'])) {
                    $other_percentage = sprintf('%.2f', ($report[$campaign_id][$revenue_tracker_id]['other_count'] / $reject_count) * 100);
                    $rejection_split .= 'Others = <b>'.$other_percentage.'%</b>, ';
                    // Log::info('---');
                    // Log::info('Other Count: '.$report[$campaign_id][$affiliate_id]['other_count']);
                    // Log::info('Other Percentage: '.$other_percentage);
                }

                $campaign_name = isset($the_campaigns[$campaign_id]) ? $the_campaigns[$campaign_id] : '';

                $rev_tracker_col = '';
                if ($affiliate_id != $revenue_tracker_id) {
                    $rev_tracker_col = $revenue_tracker_id.'<br>';
                }

                $rev_tracker_col .= '['.$the_affiliates[$affiliate_id]." ($affiliate_id)]";

                if ($only_duplicates) {
                    $duplicates[$d]['revenue_tracker_column'] = $rev_tracker_col;
                    $duplicates[$d]['affiliate_id'] = $revenue_tracker_id;
                    $duplicates[$d]['campaign_id'] = $campaign_id;
                    $duplicates[$d]['campaign'] = $campaign_name;
                    $duplicates[$d]['reject_rate'] = $reject_rate;
                    $duplicates[$d]['actual_rejection'] = $rejection_percentage.'%';
                    $duplicates[$d]['split'] = substr($rejection_split, 0, -2);
                    $duplicates[$d]['lead_count'] = $total_count;
                    $d++;

                    $this->info('Duplicate: ');
                    // Log::info('Duplicate: ');
                } else {
                    $reports[$c]['revenue_tracker_column'] = $rev_tracker_col;
                    $reports[$c]['affiliate_id'] = $revenue_tracker_id;
                    $reports[$c]['campaign_id'] = $campaign_id;
                    $reports[$c]['campaign'] = $campaign_name;
                    $reports[$c]['reject_rate'] = $reject_rate;
                    $reports[$c]['actual_rejection'] = $rejection_percentage.'%';
                    $reports[$c]['split'] = substr($rejection_split, 0, -2);
                    $reports[$c]['lead_count'] = $total_count;
                    $c++;

                    $this->info('Actual Rejection: ');
                    // Log::info('Actual Rejection: ');
                }

                $this->info('revenue tracker column: '.$rev_tracker_col);
                $this->info('campaign id: '.$campaign_id);
                $this->info('campaign: '.$campaign_name);
                $this->info('reject rate: '.$reject_rate);
                $this->info('actual rejection: '.$rejection_percentage.'%');
                $this->info('split: '.substr($rejection_split, 0, -2));
                $this->info('lead count: '.$total_count);
                $this->info('------------------');

                // Log::info('revenue tracker column: '.$rev_tracker_col);
                // Log::info('campaign id: '.$campaign_id);
                // Log::info('campaign: '.$campaign_name);
                // Log::info('reject rate: '.$reject_rate);
                // Log::info('actual rejection: '.$rejection_percentage.'%');
                // Log::info('split: '.substr($rejection_split, 0, -2));
                // Log::info('lead count: '.$total_count);
                // Log::info('------------------');
            }

            // Save data first in cached in order to generate first the images before sending email.
            // Needs to save it for the chart page is gathering the data from cached
            $expiresAt = Carbon::now()->addDay(2);
            Cache::put('nlr_rejection_report', $reports, $expiresAt);
            Cache::put('nlr_rejection_report_date', $date, $expiresAt);

            //    // Call artisan to generate the image for charts to use in email.
            //    Config::set('charts.highcharts_convert', public_path('charts/highcharts-convert-pc.js'));
            // Artisan::call('charts:generate', ['version' => 'nlr']);

            $reject_rate_subj = $critical_rate_exists == 1 ? 'Critical' : 'High';
            $title = $reject_rate_subj.' Rejection Alert Per Affiliate for '.$date;
            // \Log::info('- generate excel');
            // \Log::info($title);
            Excel::create($title, function ($excel) use ($reports) {
                $excel->sheet('report', function ($sheet) use ($reports) {
                    $rowNumber = 1;
                    // Set auto size for sheet
                    $sheet->setAutoSize(true);

                    $sheet->appendRow([
                        'Rev Tracker [Affiliate (ID)]',
                        'Campaign',
                        'Campaign ID',
                        'Lead Count',
                        'Actual Rejection',
                        'Split',
                    ]);

                    $sheet->cells("A$rowNumber:F$rowNumber", function ($cells) {
                        $cells->setFont([
                            'size' => '12',
                            'bold' => true,
                        ]);
                    });

                    foreach ($reports as $report) {
                        $rowNumber++;
                        $sheet->appendRow([
                            strip_tags($report['revenue_tracker_column']),
                            $report['campaign'],
                            $report['campaign_id'],
                            $report['lead_count'],
                            $report['actual_rejection'],
                            strip_tags($report['split']),
                        ]);
                        $sheet->cell("E$rowNumber", function ($cell) use ($report) {
                            // $cell->setFont([
                            //     'color'       =>  $report['reject_rate'] == 'CRITICAL' ? '#fe0304' : '#ffc002'
                            // ]);
                            $cell->setFontColor($report['reject_rate'] == 'CRITICAL' ? '#fe0304' : '#ffc002');
                        });
                    }
                });
            })->store('xlsx', storage_path('downloads'));
            $filePath = storage_path('downloads').'/'.$title.'.xlsx';
            $excelAttachment = $filePath;

            $chart_url = config('constants.APP_BASE_URL').'/admin/campaign/rejection?date_from='.$date.'&date_to='.$date;

            \Log::info('- generate mail');
            // Delay in order to finished the image generations
            Mail::send('emails.high_rejection_alert_report',
                ['reports' => $reports, 'yesterday' => $date, 'duplicates' => $duplicates, 'chart_url' => $chart_url], //Sent to view blade
                function ($mail) use ($critical_rate_exists, $date, $lr_build, $report_recipients, $excelAttachment) { //Used inside function only

                    $reject_rate_subj = $critical_rate_exists == 1 ? 'Critical' : 'High';

                    $mail->from('admin@engageiq.com', 'Automated Report by '.$lr_build);

                    foreach ($report_recipients as $recipient) {
                        $mail->to($recipient);
                    }

                    $mail->subject($lr_build.' '.$reject_rate_subj.' Rejection Alert Per Affiliate for '.$date);

                    // // Add attachments, chart image for high and critical
                    // if (File::exists(Config::get('charts')['outfile']['critical'])) {
                    //     $mail->attach(Config::get('charts')['outfile']['critical']);
                    // }

                    // if (File::exists(Config::get('charts')['outfile']['high'])) {
                    //     $mail->attach(Config::get('charts')['outfile']['high']);
                    // }

                    $mail->attach($excelAttachment);
                });

            $this->info('Email Sent for '.$lr_build.'!');
            Log::info('- email sent for '.$lr_build.'!');
        } else {
            Cache::forget('nlr_rejection_report');
            $this->info('No high or critical rejection rate for '.$lr_build);
            // Log::info('No high or critical rejection rate for '.$lr_build);
        }
        Log::info('- done.');

        /**
            NLR REPORT FOR CHECKING
         **/
        /*
        $this->info('Initiating rejection reference report for '.$lr_build);
        $full_report = $report;
        if($full_report) {
            $for_sorting_report = $reports;
            $lead_results = $result;
            $title = $lr_build.' Rejection Reference Report for '.$date;
            Excel::create($title, function($excel) use($date,$lead_results,$for_sorting_report,$full_report){
                $excel->sheet($date,function($sheet) use ($lead_results,$for_sorting_report,$full_report){

                    // Set auto size for sheet
                    $sheet->setAutoSize(true);

                    $total_column = 0;
                    foreach($for_sorting_report as $sorting) {

                        $affid = $sorting['affiliate_id'];
                        $cmpid = $sorting['campaign_id'];

                        $sheet->appendRow(['Campaign: '.$cmpid]);
                        $sheet->appendRow(['Affiliate: '.$affid]);
                        $sheet->appendRow(['Lead Count: '.$full_report[$cmpid][$affid]['total_count']]);
                        $sheet->appendRow(['Rejection: '.$full_report[$cmpid][$affid]['reject_count']]);
                        $total_column += 4;

                        if(isset($full_report[$cmpid][$affid]['duplicate_count'])){
                            $sheet->appendRow(['Duplicate: '.$full_report[$cmpid][$affid]['duplicate_count']]);
                            $total_column++;
                        }

                        if(isset($full_report[$cmpid][$affid]['filter_count'])){
                            $sheet->appendRow(['Filter: '.$full_report[$cmpid][$affid]['filter_count']]);
                            $total_column++;
                        }

                        if(isset($full_report[$cmpid][$affid]['prepop_count'])){
                            $sheet->appendRow(['Pre-Pop: '.$full_report[$cmpid][$affid]['prepop_count']]);
                            $total_column++;
                        }

                        if(isset($full_report[$cmpid][$affid]['other_count'])){
                            $sheet->appendRow(['Other: '.$full_report[$cmpid][$affid]['other_count']]);
                            $total_column++;
                        }

                        if(isset($full_report[$cmpid][$affid]['duplicate_count'])){
                            $sheet->appendRow(['Duplicate Results']);
                            $total_column++;
                            $c = 0;
                            foreach($lead_results[$cmpid][$affid]['duplicate'] as $value) {
                                $sheet->appendRow([++$c, preg_replace('/\s+/', '', $value)]);
                                $total_column++;
                            }
                        }
                        if(isset($full_report[$cmpid][$affid]['filter_count'])){
                            $sheet->appendRow(['Filter Results']);
                            $total_column++;
                            $c = 0;
                            foreach($lead_results[$cmpid][$affid]['filter'] as $value) {
                                $sheet->appendRow([++$c, preg_replace('/\s+/', '', $value)]);
                                $total_column++;
                            }
                        }
                        if(isset($full_report[$cmpid][$affid]['prepop_count'])){
                            $sheet->appendRow(['Pre-Pop Results']);
                            $total_column++;
                            $c = 0;
                            foreach($lead_results[$cmpid][$affid]['prepop'] as $value) {
                                $sheet->appendRow([++$c, preg_replace('/\s+/', '', $value)]);
                                $total_column++;
                            }
                        }
                        if(isset($full_report[$cmpid][$affid]['other_count'])){
                            $sheet->appendRow(['Other Results']);
                            $total_column++;
                            $c = 0;
                            foreach($lead_results[$cmpid][$affid]['other'] as $value) {
                                $sheet->appendRow([++$c, preg_replace('/\s+/', '', $value)]);
                                $total_column++;
                            }
                        }
                    }

                    $sheet->cells('A1:A'.$total_column, function($cells) {
                        $cells->setFont(array(
                            'bold'       =>  true
                        ));
                    });
                });
            })->store('xls',storage_path('downloads'));

            $filePath = storage_path('downloads').'/'.$title.'.xls';

            $excelAttachment = $filePath;

            Mail::send('emails.high_rejection_report_for_testing', [], function ($message) use ($excelAttachment,$lr_build, $qa_recipients) {
                $message->from('admin@engageiq.com', 'Automated Report by '.$lr_build);

                // $message->to('monty@engageiq.com','Monty Magbanua')->to('karla@engageiq.com', 'Karla Librero')->to('dexter@engageiq.com', 'Dexter Cadia')->to('johneil@engageiq.com', 'Johneil Quijano')->to('rian@engageiq.com', 'Rian Barrientos');

                foreach($qa_recipients as $recipient) {
                    $message->to($recipient);
                }

                $message->subject('High Rejection Report for Testing');
                $message->attach($excelAttachment);
            });
        }else {
            $this->info('No reference rejection report for '.$lr_build);
        }
        */

        /**
         BY CAMPAIGN
         *
         * */
        $this->info('Starting Campaign Report');
        // Log::info('- starting campaign report.');
        // \Log::info($campaign_reports);
        if (count($campaign_reports) > 0) {
            //Sort by total count
            arsort($csort_total);

            $critical_rate_exists = 0;

            $reports = [];
            $duplicates = [];
            $c = 0; //Actual rejection counter
            $d = 0; //Duplicate counter

            //finalize computations
            // Log::info('----------START-----------');

            $rows = [];
            foreach ($csort_total as $fid => $total_count) {

                $only_duplicates = false; //variable to check whether entry is 100% duplicates only

                $campaign_id = $csort_campaigns[$fid];
                $reject_count = $campaign_reports[$campaign_id]['reject_count'];
                $rejection_percentage = sprintf('%.2f', ($campaign_reports[$campaign_id]['reject_percentage'] * 100));

                if ($rejection_percentage >= $high_rates[0] && $rejection_percentage <= $high_rates[1]) {
                    $reject_rate = 'HIGH';
                } else {
                    $reject_rate = 'CRITICAL';
                    $critical_rate_exists = 1;
                }

                $rejection_split = '';
                if (isset($campaign_reports[$campaign_id]['acceptable_count'])) {
                    $acceptable_ratio = $campaign_reports[$campaign_id]['acceptable_count'] / $reject_count;
                    $acceptable_percentage = sprintf('%.2f', $acceptable_ratio * 100);
                    $rejection_split .= 'Acceptable = <b>'.$acceptable_percentage.'%</b>, ';
                }
                if (isset($campaign_reports[$campaign_id]['duplicate_count'])) {
                    $duplicate_ratio = $campaign_reports[$campaign_id]['duplicate_count'] / $reject_count;
                    if ($duplicate_ratio == 1) {
                        $only_duplicates = true;
                    }
                    $duplicate_percentage = sprintf('%.2f', $duplicate_ratio * 100);
                    $rejection_split .= 'Duplicates = <b>'.$duplicate_percentage.'%</b>, ';
                }
                if (isset($campaign_reports[$campaign_id]['filter_count'])) {
                    $filter_percentage = sprintf('%.2f', ($campaign_reports[$campaign_id]['filter_count'] / $reject_count) * 100);
                    $rejection_split .= 'Filter Issue = <b>'.$filter_percentage.'%</b>, ';
                }
                if (isset($campaign_reports[$campaign_id]['prepop_count'])) {
                    $prepop_percentage = sprintf('%.2f', ($campaign_reports[$campaign_id]['prepop_count'] / $reject_count) * 100);
                    $rejection_split .= 'Pre-pop Issue = <b>'.$prepop_percentage.'%</b>, ';
                }
                if (isset($campaign_reports[$campaign_id]['other_count'])) {
                    $other_percentage = sprintf('%.2f', ($campaign_reports[$campaign_id]['other_count'] / $reject_count) * 100);
                    $rejection_split .= 'Others = <b>'.$other_percentage.'%</b>, ';
                }

                $campaign_name = isset($the_campaigns[$campaign_id]) ? $the_campaigns[$campaign_id] : '';

                if ($only_duplicates) {
                    $duplicates[$d]['campaign_id'] = $campaign_id;
                    $duplicates[$d]['campaign'] = $campaign_name;
                    $duplicates[$d]['reject_rate'] = $reject_rate;
                    $duplicates[$d]['actual_rejection'] = $rejection_percentage.'%';
                    $duplicates[$d]['split'] = substr($rejection_split, 0, -2);
                    $duplicates[$d]['lead_count'] = $total_count;
                    $d++;

                    $this->info('Duplicate: ');
                    // Log::info('Duplicate: ');
                } else {
                    $reports[$c]['campaign_id'] = $campaign_id;
                    $reports[$c]['campaign'] = $campaign_name;
                    $reports[$c]['reject_rate'] = $reject_rate;
                    $reports[$c]['actual_rejection'] = $rejection_percentage.'%';
                    $reports[$c]['split'] = substr($rejection_split, 0, -2);
                    $reports[$c]['lead_count'] = $total_count;
                    $c++;

                    $this->info('Actual Rejection: ');
                    // Log::info('Actual Rejection: ');
                }

                $this->info('campaign id: '.$campaign_id);
                $this->info('campaign: '.$campaign_name);
                $this->info('reject rate: '.$reject_rate);
                $this->info('actual rejection: '.$rejection_percentage.'%');
                $this->info('split: '.substr($rejection_split, 0, -2));
                $this->info('lead count: '.$total_count);
                $this->info('------------------');

                // Log::info('campaign id: '.$campaign_id);
                // Log::info('campaign: '.$campaign_name);
                // Log::info('reject rate: '.$reject_rate);
                // Log::info('actual rejection: '.$rejection_percentage.'%');
                // Log::info('split: '.substr($rejection_split, 0, -2));
                // Log::info('lead count: '.$total_count);
                // Log::info('------------------');

                $rows[$campaign_id] = [
                    'total_count' => $campaign_reports[$campaign_id]['total_count'],
                    'reject_count' => $reject_count,
                    'acceptable_count' => isset($campaign_reports[$campaign_id]['acceptable_count']) ? $campaign_reports[$campaign_id]['acceptable_count'] : 0,
                    'duplicate_count' => isset($campaign_reports[$campaign_id]['duplicate_count']) ? $campaign_reports[$campaign_id]['duplicate_count'] : 0,
                    'filter_count' => isset($campaign_reports[$campaign_id]['filter_count']) ? $campaign_reports[$campaign_id]['filter_count'] : 0,
                    'prepop_count' => isset($campaign_reports[$campaign_id]['prepop_count']) ? $campaign_reports[$campaign_id]['prepop_count'] : 0,
                    'other_count' => isset($campaign_reports[$campaign_id]['other_count']) ? $campaign_reports[$campaign_id]['other_count'] : 0,
                ];

                if ($campaign_reports[$campaign_id]['total_count'] == $reject_count) {
                    $full_rejects[] = [
                        'campaign_id' => $campaign_id,
                        'campaign' => $campaign_name,
                        'reject_rate' => $reject_rate,
                        'actual_rejection' => $rejection_percentage.'%',
                        'split' => substr($rejection_split, 0, -2),
                        'lead_count' => $total_count,

                        'total_count' => $campaign_reports[$campaign_id]['total_count'],
                        'reject_count' => $reject_count,
                        'acceptable_count' => isset($campaign_reports[$campaign_id]['acceptable_count']) ? $campaign_reports[$campaign_id]['acceptable_count'] : 0,
                        'duplicate_count' => isset($campaign_reports[$campaign_id]['duplicate_count']) ? $campaign_reports[$campaign_id]['duplicate_count'] : 0,
                        'filter_count' => isset($campaign_reports[$campaign_id]['filter_count']) ? $campaign_reports[$campaign_id]['filter_count'] : 0,
                        'prepop_count' => isset($campaign_reports[$campaign_id]['prepop_count']) ? $campaign_reports[$campaign_id]['prepop_count'] : 0,
                        'other_count' => isset($campaign_reports[$campaign_id]['other_count']) ? $campaign_reports[$campaign_id]['other_count'] : 0,
                    ];
                    $full_reject_campaigns[] = $campaign_id;
                }
            }

            // Log::info(count($campaign_reports));
            // Log::info(count($reports));
            // Log::info(count($duplicates));

            $breakdown = [];
            foreach ($rows as $campaign_id => $row) {
                $breakdown[] = [
                    'campaign_id' => $campaign_id,
                    'total_count' => $row['total_count'],
                    'reject_count' => $row['reject_count'],
                    'acceptable_reject_count' => $row['acceptable_count'],
                    'duplicate_count' => $row['duplicate_count'],
                    'filter_count' => $row['filter_count'],
                    'prepop_count' => $row['prepop_count'],
                    'other_count' => $row['other_count'],
                    'created_at' => $date,
                ];
            }

            DB::table('campaign_rejection_statistics')->where('created_at', $date)->delete();
            $chunks = array_chunk($breakdown, 1000);
            foreach ($chunks as $chunk) {
                try {
                    DB::table('campaign_rejection_statistics')->insert($chunk);
                    //Log::info('Rev Tracker Cake Stats Chunks total: ' . count($chunk));
                } catch (QueryException $e) {
                    Log::info('Campaign Rejection Stats Saving Error!');
                    Log::info($e->getMessage());
                }
            }

            $reject_rate_subj = $critical_rate_exists == 1 ? 'Critical' : 'High';
            $title = $reject_rate_subj.' Rejection Alert Per Campaign for '.$date;
            Excel::create($title, function ($excel) use ($reports, $date) {
                $excel->sheet($date, function ($sheet) use ($reports) {
                    $rowNumber = 1;
                    // Set auto size for sheet
                    $sheet->setAutoSize(true);

                    $sheet->appendRow([
                        'Campaign',
                        'Campaign ID',
                        'Lead Count',
                        'Actual Rejection',
                        'Split',
                    ]);

                    $sheet->cells("A$rowNumber:F$rowNumber", function ($cells) {
                        $cells->setFont([
                            'size' => '12',
                            'bold' => true,
                        ]);
                    });

                    $total_column = 0;
                    foreach ($reports as $report) {
                        $rowNumber++;
                        $sheet->appendRow([
                            $report['campaign'],
                            $report['campaign_id'],
                            $report['lead_count'],
                            $report['actual_rejection'],
                            strip_tags($report['split']),
                        ]);
                        $sheet->cell("D$rowNumber", function ($cell) use ($report) {
                            $cell->setFontColor($report['reject_rate'] == 'CRITICAL' ? '#fe0304' : '#ffc002');
                        });
                    }
                });
            })->store('xlsx', storage_path('downloads'));
            $filePath = storage_path('downloads').'/'.$title.'.xlsx';
            $excelAttachment = $filePath;

            // Delay in order to finished the image generations
            Mail::send('emails.campaign_high_rejection_alert_report',
                ['reports' => $reports, 'yesterday' => $date, 'duplicates' => $duplicates, 'chart_url' => $chart_url], //Sent to view blade
                function ($mail) use ($excelAttachment, $critical_rate_exists, $date, $lr_build, $report_recipients) { //Used inside function only

                    $reject_rate_subj = $critical_rate_exists == 1 ? 'Critical' : 'High';

                    $mail->from('admin@engageiq.com', 'Automated Report by '.$lr_build);

                    foreach ($report_recipients as $recipient) {
                        $mail->to($recipient);
                    }

                    $mail->subject($lr_build.' '.$reject_rate_subj.' Rejection Alert Per Campaign for '.$date);

                    $mail->attach($excelAttachment);
                });

            $this->info('Campaign Email Sent for '.$lr_build.'!');
            // Log::info('- campaign Email Sent for '.$lr_build);

            $this->info('Full Reject Status: '.$full_rejection_alert_status);
            $this->info('Full Reject Total: '.count($full_rejects));
            // Log::info('Full Reject Status: '. $full_rejection_alert_status);
            // Log::info('Full Reject Total: '. count($full_rejects));

            if ($full_rejection_alert_status == 1 && count($full_rejects) > 0) {
                Log::info('- full rejection process starting');
                //Action Logger: Edit Campaign Info
                $value_mask = [
                    'status' => config('constants.CAMPAIGN_STATUS'),
                ];

                $from_days = $full_rejection_alert_check_days - 1;
                $from = Carbon::parse($date)->subDay($from_days)->toDateString();
                $to = $date;
                // \DB::enableQueryLog();
                // \DB::connection('secondary')->enableQueryLog();
                $qualifiedCampaigns = Lead::whereBetween('created_at', [$from.' 00:00:00', $to.' 23:59:59'])
                    ->groupBy('campaign_id')->having('total', '>=', $full_rejection_alert_min_leads)->select(['campaign_id', \DB::RAW('COUNT(*) as total')])
                    ->pluck('campaign_id')->toArray();
                // \Log::info(\DB::getQueryLog());
                // \Log::info(\DB::connection('secondary')->getQueryLog());
                // \Log::info($full_reject_campaigns);
                // \Log::info($qualifiedCampaigns);
                $fr_dbs = [];
                $fr_ems = [];
                $fr_ids = [];
                foreach ($full_rejects as $row) {
                    $campaign_id = $row['campaign_id'];
                    if (in_array($campaign_id, $qualifiedCampaigns) && ! in_array($campaign_id, $full_rejection_excluded_campaigns)) {
                        $fr_dbs[] = [
                            'campaign_id' => $campaign_id,
                            'total_count' => $row['total_count'],
                            'reject_count' => $row['reject_count'],
                            'acceptable_reject_count' => $row['acceptable_count'],
                            'duplicate_count' => $row['duplicate_count'],
                            'filter_count' => $row['filter_count'],
                            'prepop_count' => $row['prepop_count'],
                            'other_count' => $row['other_count'],
                            'created_at' => $date,
                        ];

                        $fr_ems[] = [
                            'campaign_id' => $campaign_id,
                            'campaign' => $row['campaign'],
                            'reject_rate' => $row['reject_rate'],
                            'actual_rejection' => $row['actual_rejection'],
                            'split' => $row['split'],
                            'lead_count' => $row['lead_count'],
                        ];

                        $fr_ids[] = $campaign_id;
                    }

                }

                // \Log::info($fr_ids);

                if (count($fr_ems) > 0) {
                    $this->info('- full rejected campaigns: ');
                    // Log::info('- full rejected campaigns: ');
                    $rej_campaigns = Campaign::whereIn('id', $fr_ids)->get();
                    // \Log::info($rej_campaigns);
                    foreach ($rej_campaigns as $rejc) {

                        $this->info('campaign id: '.$rejc->id);
                        // Log::info('campaign id: '. $rejc->id);

                        if ($full_rejection_deactivate_campaign_status == 1) {
                            $this->info('status: deactivated');
                            // Log::info('status: deactivated');
                            //Update status to inactive
                            $rejc->status = 0;
                            $rejc->save();
                        } else {
                            $this->info('status: as is');
                            // Log::info('status: as is');
                        }

                        // Log::info($current_state);
                        // Log::info($rejc->toArray());

                        if ($full_rejection_advertiser_email_status == 1) {
                            $this->info('Sending full rejection alert for campaign: '.$rejc->id);
                            // Log::info('Sending full rejection alert for campaign: '.$rejc->id);

                            Mail::send('emails.campaign_full_rejection_advertiser_alert',
                                ['campaign' => $rejc, 'date' => $date], //Sent to view blade
                                function ($mail) use ($lr_build, $report_recipients, $rejc) { //Used inside function only

                                    $mail->from('admin@engageiq.com', 'Automated Report by '.$lr_build);

                                    $mail->to($rejc->advertiser_email);
                                    foreach ($report_recipients as $recipient) {
                                        $mail->cc($recipient);
                                    }

                                    $mail->subject('EngageIQ Urgent Message. Campaign Deactivated.');
                                });
                        }
                        $this->info('------');
                        // Log::info('------');
                    }

                    $this->info('- saving to db');
                    DB::table('campaign_full_rejection_statistics')->where('created_at', $date)->delete();
                    $chunks = array_chunk($fr_dbs, 1000);
                    foreach ($chunks as $chunk) {
                        try {
                            DB::table('campaign_full_rejection_statistics')->insert($chunk);
                            //Log::info('Rev Tracker Cake Stats Chunks total: ' . count($chunk));
                        } catch (QueryException $e) {
                            Log::info('Campaign Full Rejection Stats Saving Error!');
                            Log::info($e->getMessage());
                        }
                    }

                    if ($full_rejection_deactivate_campaign_status == 1) {
                        $chart_url = config('constants.APP_BASE_URL').'/admin/campaign/100percent_rejection?date_from='.$date.'&date_to='.$date;
                        // Delay in order to finished the image generations
                        Mail::send('emails.full_rejection_campaign_high_rejection_alert_report',
                            ['reports' => $fr_ems, 'yesterday' => $date, 'chart_url' => $chart_url], //Sent to view blade
                            function ($mail) use ($date, $lr_build, $report_recipients) { //Used inside function only

                                $mail->from('admin@engageiq.com', 'Automated Report by '.$lr_build);

                                foreach ($report_recipients as $recipient) {
                                    $mail->to($recipient);
                                }

                                $mail->subject($lr_build.' 100% Rejection Alert Per Campaign for '.$date);
                            });
                    }

                    $this->info('Full Rejection Campaign Email Sent for '.$lr_build.'!');
                    // Log::info('Full Rejection Campaign Email Sent for '.$lr_build.'!');
                } else {
                    $this->info('NO Full Rejection Campaign Email Sent for '.$lr_build.'!');
                    // Log::info('NO Full Rejection Campaign Email Sent for '.$lr_build.'!');
                }

            }
        } else {
            Cache::forget('nlr_rejection_report');
            $this->info('No high or critical rejection rate for '.$lr_build);
            // Log::info('No high or critical rejection rate for '.$lr_build);
        }
    }

    public function strposa($haystack, $needles = [], $offset = 0)
    {
        if (! is_array($needles)) {
            $needles = [$needles];
        }
        foreach ($needles as $query) {
            if (strpos($haystack, $query, $offset) !== false) {
                return true;
            } // stop on first true result
        }

        return false;
    }
}
