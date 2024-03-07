<?php

namespace App\Jobs\Reports;

use App\Affiliate;
use App\AffiliateRevenueTracker;
use App\Campaign;
use App\CampaignPayout;
use App\CoregReport;
use App\Jobs\Job;
use App\Lead;
use App\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class GetCoregPerformanceReport extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->attempts() > 1) {
            return;
        }

        $lr_build = config('settings.app_build');
        $date_yesterday = Carbon::yesterday()->toDateString();

        Log::info('Generating Report for '.$date_yesterday);

        //Get Recipients
        $report_recipients = [];
        $setting = Setting::where('code', 'report_recipient')->first();
        $rep_emails = explode(';', $setting->description);
        foreach ($rep_emails as $re) {
            $re = trim($re);
            if (filter_var($re, FILTER_VALIDATE_EMAIL)) {
                $report_recipients[] = $re;
            }
        }

        //Get OLR Program ID => Campaign ID
        $coregs = Campaign::whereIn('campaign_type', [1, 2, 3, 7, 8, 9, 10, 11, 12])->pluck('id', 'olr_program_id')->toArray();
        //Get Rev Tracker => Affiliate ID
        $revAffiliates = AffiliateRevenueTracker::pluck('affiliate_id', 'revenue_tracker_id')->toArray();

        //Get Campaign default and affiliate-specific received and payout for OLR leads
        // $payouts = DB::select('SELECT campaigns.id, campaign_payouts.affiliate_id, campaigns.default_payout, campaigns.default_received,
        //     campaign_payouts.received, campaign_payouts.payout FROM campaign_payouts RIGHT JOIN campaigns ON campaign_payouts.campaign_id = campaigns.id');
        $payouts = CampaignPayout::rightJoin('campaigns', 'campaign_payouts.campaign_id', '=', 'campaigns.id')
            ->select(DB::RAW('campaigns.id, campaign_payouts.affiliate_id, campaigns.default_payout, campaigns.default_received, campaign_payouts.received, campaign_payouts.payout'))->get();

        $lead_payouts = [];

        //Save them in a easy access array
        foreach ($payouts as $payout) {
            if (! isset($lead_payouts[$payout->id][null])) {
                $lead_payouts[$payout->id][null]['payout'] = $payout->default_payout;
                $lead_payouts[$payout->id][null]['received'] = $payout->default_received;
            }

            $lead_payouts[$payout->id][$payout->affiliate_id]['payout'] = $payout->affiliate_id == null ? $payout->default_payout : $payout->payout;
            $lead_payouts[$payout->id][$payout->affiliate_id]['received'] = $payout->affiliate_id == null ? $payout->default_received : $payout->received;
        }

        $report = [];

        ///NLR
        Log::info('Retrieving from NLR');
        $time1 = microtime(true);

        //ACTUAL QUERY
        $nlr = Lead::where(DB::RAW('date(created_at)'), $date_yesterday)->select('id', 'campaign_id', 'affiliate_id', 'lead_status', 'received', 'payout')->get();
        //TESTING
        // $nlr = DB::connection('nlr')->select('SELECT id, campaign_id, affiliate_id, lead_status, received, payout FROM leads WHERE date(created_at) = "'.$date_yesterday.'"');

        $time2 = microtime(true);
        $runtime = $time2 - $time1;
        Log::info('Done retrieving from NLR');
        Log::info('Runtime: '.$runtime);

        foreach ($nlr as $lead) {

            //Initialize Campaign and Affiliate/Rev Tracker
            $campaign_id = $lead->campaign_id;
            $affiliate_id = $lead->affiliate_id;

            if (! isset($report['n'][$campaign_id][$lead->affiliate_id])) {
                //Initialize if Campaign & Affiliate not exists in report
                // Log::info('NLR: '.$campaign_id.' - '.$lead->affiliate_id);
                $report['n'][$campaign_id][$affiliate_id]['lr_total'] = 0;
                $report['n'][$campaign_id][$affiliate_id]['lr_rejected'] = 0;
                $report['n'][$campaign_id][$affiliate_id]['lr_failed'] = 0;
                $report['n'][$campaign_id][$affiliate_id]['lr_pending'] = 0;
                $report['n'][$campaign_id][$affiliate_id]['lr_cap'] = 0;
                $report['n'][$campaign_id][$affiliate_id]['lr_success'] = 0;

                $report['n'][$campaign_id][$affiliate_id]['lf_total'] = 0;
                $report['n'][$campaign_id][$affiliate_id]['lf_filter_drop'] = 0;
                $report['n'][$campaign_id][$affiliate_id]['lf_admin_drop'] = 0;
                $report['n'][$campaign_id][$affiliate_id]['lf_nlr_drop'] = 0;

                /*
                $report['n'][$campaign_id][$affiliate_id]['olr_total'] = 0;
                $report['n'][$campaign_id][$affiliate_id]['olr_rejected'] = 0;
                $report['n'][$campaign_id][$affiliate_id]['olr_failed'] = 0;
                $report['n'][$campaign_id][$affiliate_id]['olr_success'] = 0;
                */

                //Set received/lead & payout/lead
                $report['n'][$campaign_id][$affiliate_id]['received'] = $lead->received; // Received/lead
                $report['n'][$campaign_id][$affiliate_id]['payout'] = $lead->payout; // Payout/lead

                //Set Source of Entry
                $report['n'][$campaign_id][$affiliate_id]['source'] = 2; //Where Source: 1 = OLR; 2 = NLR; 3 = Mixed; 4 = LF
            }

            //NLR COUNTER
            if ($lead->lead_status == 1) {
                //Success
                $report['n'][$campaign_id][$lead->affiliate_id]['lr_success'] += 1;
            } elseif ($lead->lead_status == 2) {
                //Rejected
                $report['n'][$campaign_id][$lead->affiliate_id]['lr_rejected'] += 1;
            } elseif ($lead->lead_status == 3 || $lead->lead_status == 4) {
                //Pending Or Queued
                $report['n'][$campaign_id][$lead->affiliate_id]['lr_pending'] += 1;
            } elseif ($lead->lead_status == 5) {
                //Cap Reached
                $report['n'][$campaign_id][$lead->affiliate_id]['lr_cap'] += 1;
            } else {
                //Failed or TimeOut
                $report['n'][$campaign_id][$lead->affiliate_id]['lr_failed'] += 1;
            }

            $report['n'][$campaign_id][$lead->affiliate_id]['lr_total'] += 1; //
        }

        //OLR
        // Log::info('Retrieving from OLR');
        // $time1 = microtime(true);
        // $olr = DB::connection('olr')->select('SELECT LEAD_INTERNAL_ID,PROGRAM_ID as campaign_id, AFFILIATE_ID as affiliate_id, LEAD_STATUS as lead_status, LEAD_PROGRAM_NAME FROM LEADS WHERE date(LEAD_DATE) = "'.$date_yesterday.'"');

        //ADJUSTED
        // $olr = DB::connection('olr')->select('SELECT LEAD_INTERNAL_ID,PROGRAM_ID as campaign_id, AFFILIATE_ID as affiliate_id, LEAD_STATUS as lead_status, LEAD_PROGRAM_NAME FROM LEADS WHERE LEAD_DATE BETWEEN "'.$date_yesterday.' 03:00:00" AND "'.Carbon::now()->toDateString().' 02:59:59"');
        // $time2 = microtime(true);
        // $runtime = $time2 - $time1;
        // Log::info('Done retrieving from OLR');
        // Log::info('Runtime: '.$runtime);
        //Log::info('OLR Runtime: '.$runtime);

        //$olr_campaigns = []; //For saving olr campaign names

        $no_payouts = [];
        $campaign_affiliate_no_payouts = [];

        /*
        foreach($olr as $lead) {

            // if(! array_key_exists($lead->campaign_id,$olr_campaigns)) $olr_campaigns[$lead->campaign_id] = $lead->LEAD_PROGRAM_NAME;

            //Initialize Campaign and Affiliate/Rev Tracker
            $campaign_not_exists_in_lr = false; //Check if olr program id exists in NLR
            if(array_key_exists($lead->campaign_id,$coregs))
            {
                $campaign_id = $coregs[$lead->campaign_id];
            }
            else
            {
                // Log::info('{OLR}Not Exists: '.$lead->campaign_id);
                $campaign_id = $lead->campaign_id;
                $campaign_not_exists_in_lr = true;
            }

            $affiliate_id = $lead->affiliate_id;

            if(! isset($report['o'][$campaign_id][$lead->affiliate_id]))
            {
                //Initialize if Campaign & Affiliate not exists in report
                // Log::info('OLR: '.$campaign_id.' - '.$lead->affiliate_id);
                $report['o'][$campaign_id][$affiliate_id]['lr_total'] = 0;
                $report['o'][$campaign_id][$affiliate_id]['lr_rejected'] = 0;
                $report['o'][$campaign_id][$affiliate_id]['lr_failed'] = 0;
                $report['o'][$campaign_id][$affiliate_id]['lr_pending'] = 0;
                $report['o'][$campaign_id][$affiliate_id]['lr_cap'] = 0;
                $report['o'][$campaign_id][$affiliate_id]['lr_success'] = 0;

                $report['o'][$campaign_id][$affiliate_id]['lf_total'] = 0;
                $report['o'][$campaign_id][$affiliate_id]['lf_filter_drop'] = 0;
                $report['o'][$campaign_id][$affiliate_id]['lf_admin_drop'] = 0;
                $report['o'][$campaign_id][$affiliate_id]['lf_nlr_drop'] = 0;

                $report['o'][$campaign_id][$affiliate_id]['olr_total'] = 0;
                $report['o'][$campaign_id][$affiliate_id]['olr_rejected'] = 0;
                $report['o'][$campaign_id][$affiliate_id]['olr_failed'] = 0;
                $report['o'][$campaign_id][$affiliate_id]['olr_success'] = 0;
                $report['o'][$campaign_id][$affiliate_id]['olr_revenue'] = 0;
                $report['o'][$campaign_id][$affiliate_id]['olr_only'] = 0; //Where olr_only: 0 = Entry exists only in OLR, 1 = Entry exists in OLR and program id not exists in NLR, NULL = Exists in NLR

                //Set received/lead & payout/lead
                if($campaign_not_exists_in_lr)
                {
                    //Program ID not exisisting in NLR
                    $report['o'][$campaign_id][$affiliate_id]['olr_only'] = 1; //Set to 1 when olr program id not exists in NLR
                    $no_payouts[] = $campaign_id; //Store in array. When program id not exists in NLR, we have no record of how much it's payout is. We need to get the payout in OLR.
                    $campaign_affiliate_no_payouts[] = ['c' => $campaign_id, 'a' => $affiliate_id];

                }
                else
                {
                    //Program Exists in NLR
                    if(isset($lead_payouts[$campaign_id][$lead->affiliate_id]))
                    {
                        //has specific received/payout for affiliate
                        $report['o'][$campaign_id][$affiliate_id]['received'] = $lead_payouts[$campaign_id][$lead->affiliate_id]['received'];
                        $report['o'][$campaign_id][$affiliate_id]['payout'] = $lead_payouts[$campaign_id][$lead->affiliate_id]['payout'];

                    }
                    else
                    {
                        //get default
                        $report['o'][$campaign_id][$affiliate_id]['received'] = $lead_payouts[$campaign_id][null]['received']; // Received/lead
                        $report['o'][$campaign_id][$affiliate_id]['payout'] = $lead_payouts[$campaign_id][null]['payout']; // Payout/Lead

                    }
                }

                //Set Source of Entry
                $report['o'][$campaign_id][$affiliate_id]['source'] = 1; //Where Source: 1 = OLR; 2 = NLR; 3 = Mixed; 4 = LF
            }

            // Check if Campaign - Affiliate exists in both NLR & OLR
            // if(isset($report[$campaign_id][$affiliate_id]['source'])) {
            //    if($report[$campaign_id][$affiliate_id]['source'] == 2) {
            //        //If source == 2; meaning this campaign - affiliate was first initialized in NLR but also has OLR leads
            //        $report[$campaign_id][$affiliate_id]['source'] = 3; //Where Source: 1 = OLR; 2 = NLR; 3 = Mixed
            //    }
            // }

            //OLR COUNTER

            if($lead->lead_status == 'SUCCESS')
            {
                $report['o'][$campaign_id][$lead->affiliate_id]['olr_success'] += 1;
            }
            else if($lead->lead_status == 'REJECTED')
            {
                $report['o'][$campaign_id][$lead->affiliate_id]['olr_rejected'] += 1;
            }
            else
            {
                $report['o'][$campaign_id][$lead->affiliate_id]['olr_failed'] += 1;
            }

            $report['o'][$campaign_id][$lead->affiliate_id]['olr_total'] += 1;
        }
        */

        //LEAD FILTER
        Log::info('Retrieving from LF');
        $time1 = microtime(true);
        //ACTUAL QUERY
        $lf = DB::connection('lf')->select('SELECT id,campaign_id, status, affiliate_id, source FROM lead_requests WHERE date(updated_at) = "'.$date_yesterday.'"');
        //ACTUAL QUERY
        $lfd = DB::connection('lf')->select('SELECT id,campaign_id, status, affiliate_id, source FROM lead_request_duplicates WHERE date(updated_at) = "'.$date_yesterday.'"');
        //FOR TESTING
        //$lf = DB::connection('lf')->select('SELECT id,campaign_id, status, url FROM lead_requests WHERE date(updated_at) = "'.$date_yesterday.'"');
        //FOR TESTING
        //$lfd = DB::connection('lf')->select('SELECT id,campaign_id, status, url FROM lead_request_duplicates WHERE date(updated_at) = "'.$date_yesterday.'"');

        $time2 = microtime(true);
        $runtime = $time2 - $time1;
        Log::info('Done retrieving from LF');
        Log::info('Runtime: '.$runtime);

        //Log::info('LF Runtime: '.$runtime);

        foreach ($lf as $lead) {
            //Initialize Campaign and Affiliate/Rev Tracker
            $campaign_not_exists_in_lr = false;

            if (array_key_exists($lead->campaign_id, $coregs)) {
                $campaign_id = $coregs[$lead->campaign_id];
            } else {
                //Log::info('{LF}Not Exists: '.$lead->campaign_id);
                $campaign_id = $lead->campaign_id;
                $campaign_not_exists_in_lr = true;
            }

            //FOR TESTING
            // $query = parse_url($lead->url, PHP_URL_QUERY);
            // parse_str($query, $params);
            // $affiliate_id = null;
            // if(isset($params['eiq_affiliate_id'])) $affiliate_id = $params['eiq_affiliate_id'];
            // else if(isset($params['add_code'])) {
            //     $affiliate_id = preg_replace("/[^0-9]/", "", $params['add_code'] );
            // }
            //FOR TESTING
            $affiliate_id = $lead->affiliate_id;

            //GET LEAD SOURCE
            if ($lead->source == 1) {
                $source = 'n';
            } else {
                $source = 'o';
            }

            if (! isset($report[$source][$campaign_id][$affiliate_id])) {
                //Initialize if Campaign & Affiliate not exists in report
                //Log::info('LF: '.$campaign_id.' - '.$affiliate_id);

                $report[$source][$campaign_id][$affiliate_id]['lr_total'] = 0;
                $report[$source][$campaign_id][$affiliate_id]['lr_rejected'] = 0;
                $report[$source][$campaign_id][$affiliate_id]['lr_failed'] = 0;
                $report[$source][$campaign_id][$affiliate_id]['lr_pending'] = 0;
                $report[$source][$campaign_id][$affiliate_id]['lr_cap'] = 0;
                $report[$source][$campaign_id][$affiliate_id]['lr_success'] = 0;

                $report[$source][$campaign_id][$affiliate_id]['lf_total'] = 0;
                $report[$source][$campaign_id][$affiliate_id]['lf_filter_drop'] = 0;
                $report[$source][$campaign_id][$affiliate_id]['lf_admin_drop'] = 0;
                $report[$source][$campaign_id][$affiliate_id]['lf_nlr_drop'] = 0;

                /*
                $report[$source][$campaign_id][$affiliate_id]['olr_total'] = 0;
                $report[$source][$campaign_id][$affiliate_id]['olr_rejected'] = 0;
                $report[$source][$campaign_id][$affiliate_id]['olr_failed'] = 0;
                $report[$source][$campaign_id][$affiliate_id]['olr_success'] = 0;
                */

                /*
                if($campaign_not_exists_in_lr && $source == 'o')
                {
                    $report[$source][$campaign_id][$affiliate_id]['olr_only'] = 1; //Set to 1 when olr program id not exists in NLR
                    $no_payouts[] = $campaign_id;
                    $campaign_affiliate_no_payouts[] = ['c' => $campaign_id, 'a' => $affiliate_id];
                }
                */

                //Set Source of Entry
                $report[$source][$campaign_id][$affiliate_id]['source'] = 4; //Where Source: 1 = OLR; 2 = NLR; 3 = Mixed; 4 = LF
            }

            //LF COUNTER
            if ($lead->status == 2 || $lead->status == 0) {
                $report[$source][$campaign_id][$affiliate_id]['lf_admin_drop'] += 1;
            } elseif ($lead->status == 3) {
                $report[$source][$campaign_id][$affiliate_id]['lf_filter_drop'] += 1;
            } elseif ($lead->status == 4) {
                $report[$source][$campaign_id][$affiliate_id]['lf_nlr_drop'] += 1;
            }

            $report[$source][$campaign_id][$affiliate_id]['lf_total'] += 1;
        }

        //Lead Filter Duplicates
        foreach ($lfd as $lead) {
            //Initialize Campaign and Affiliate/Rev Tracker
            $campaign_not_exists_in_lr = false;

            if (array_key_exists($lead->campaign_id, $coregs)) {
                $campaign_id = $coregs[$lead->campaign_id];
            } else {
                // Log::info('{LFD}Not Exists: '.$lead->campaign_id);
                $campaign_id = $lead->campaign_id;
                $campaign_not_exists_in_lr = true;
            }

            //FOR TESTING
            // $query = parse_url($lead->url, PHP_URL_QUERY);
            // parse_str($query, $params);
            // $affiliate_id = null;
            // if(isset($params['eiq_affiliate_id'])) $affiliate_id = $params['eiq_affiliate_id'];
            // else if(isset($params['add_code'])) {
            //     $affiliate_id = preg_replace("/[^0-9]/", "", $params['add_code'] );
            // }
            //FOR TESTING
            $affiliate_id = $lead->affiliate_id;

            //GET LEAD SOURCE
            if ($lead->source == 1) {
                $source = 'n';
            } else {
                $source = 'o';
            }

            if (! isset($report[$source][$campaign_id][$affiliate_id])) {
                //Initialize if Campaign & Affiliate not exists in report
                // Log::info('LF: '.$campaign_id.' - '.$affiliate_id);
                $report[$source][$campaign_id][$affiliate_id]['lr_total'] = 0;
                $report[$source][$campaign_id][$affiliate_id]['lr_rejected'] = 0;
                $report[$source][$campaign_id][$affiliate_id]['lr_failed'] = 0;
                $report[$source][$campaign_id][$affiliate_id]['lr_pending'] = 0;
                $report[$source][$campaign_id][$affiliate_id]['lr_cap'] = 0;
                $report[$source][$campaign_id][$affiliate_id]['lr_success'] = 0;

                $report[$source][$campaign_id][$affiliate_id]['lf_total'] = 0;
                $report[$source][$campaign_id][$affiliate_id]['lf_filter_drop'] = 0;
                $report[$source][$campaign_id][$affiliate_id]['lf_admin_drop'] = 0;
                $report[$source][$campaign_id][$affiliate_id]['lf_nlr_drop'] = 0;

                /*
                $report[$source][$campaign_id][$affiliate_id]['olr_total'] = 0;
                $report[$source][$campaign_id][$affiliate_id]['olr_rejected'] = 0;
                $report[$source][$campaign_id][$affiliate_id]['olr_failed'] = 0;
                $report[$source][$campaign_id][$affiliate_id]['olr_success'] = 0;

                if($campaign_not_exists_in_lr && $source == 'o')
                {
                    $report[$source][$campaign_id][$affiliate_id]['olr_only'] = 1;
                    $no_payouts[] = $campaign_id;
                    $campaign_affiliate_no_payouts[] = ['c' => $campaign_id, 'a' => $affiliate_id];
                }
                */
                //Set Source of Entry
                $report[$source][$campaign_id][$affiliate_id]['source'] = 4; //Where Source: 1 = OLR; 2 = NLR; 3 = Mixed; 4 = LF
            }

            //LFD COUNTER
            $report[$source][$campaign_id][$affiliate_id]['lf_filter_drop'] += 1;
            $report[$source][$campaign_id][$affiliate_id]['lf_total'] += 1;
        }

        Log::info('Computing...');

        //If there are campaigns with no payout
        if (count($no_payouts) > 0) {
            /*
            $the_campaigns = implode(',',$no_payouts);

            //Get payouts for campaigns with no payout
            $olr_payouts = DB::connection('olr')->select('SELECT p1.campaignId, p1.partnerId, p1.leadPayout as payout FROM partnerpayout p1 LEFT JOIN partnerpayout p2
                            ON (p1.campaignId = p2.campaignId AND p1.partnerId = p2.partnerId AND p1.effectiveDate < p2.effectiveDate)
                            WHERE p2.effectiveDate IS NULL AND p1.campaignId IN ('.$the_campaigns.')');

            foreach($olr_payouts as $p)
            {
                $old_payouts[$p->campaignId][$p->partnerId] = $p->payout;
            }
            */

            //Get received for campaigns with no payout
            // $olr_received = DB::connection('olr')->select('SELECT c1.campaignId, c1.partnerId, c1.leadPayout as received FROM campaignpayout c1 LEFT JOIN campaignpayout c2
            //                 ON (c1.campaignId = c2.campaignId AND c1.partnerId = c2.partnerId AND c1.effectiveDate < c2.effectiveDate)
            //                 WHERE c2.effectiveDate IS NULL AND c1.campaignId IN ('.$the_campaigns.')');
            // foreach($olr_received as $r) {
            //    $old_received[$r->campaignId][$r->partnerId] = $r->received;
            // }

            //Assign the payouts to campaigns
            foreach ($campaign_affiliate_no_payouts as $no) {
                $campaign = $no['c'];
                $affiliate = $no['a'];
                $payout = 0;
                $received = 0;

                /*
                if(isset($old_payouts[$campaign][$affiliate]))
                {
                    $payout = $old_payouts[$campaign][$affiliate];
                    $received = $old_payouts[$campaign][$affiliate];
                }
                else if(isset($old_payouts[$campaign]['All']))
                {
                    $payout = $old_payouts[$campaign]['All'];
                    $received = $old_payouts[$campaign]['All'];
                }
                */

                $campaign_id = $campaign;
                $affiliate_id = $affiliate;

                if (! isset($report['o'][$campaign_id][$affiliate_id])) {
                    $report['o'][$campaign_id][$affiliate_id]['lr_total'] = 0;
                    $report['o'][$campaign_id][$affiliate_id]['lr_rejected'] = 0;
                    $report['o'][$campaign_id][$affiliate_id]['lr_failed'] = 0;
                    $report['o'][$campaign_id][$affiliate_id]['lr_pending'] = 0;
                    $report['o'][$campaign_id][$affiliate_id]['lr_cap'] = 0;
                    $report['o'][$campaign_id][$affiliate_id]['lr_success'] = 0;

                    $report['o'][$campaign_id][$affiliate_id]['lf_total'] = 0;
                    $report['o'][$campaign_id][$affiliate_id]['lf_filter_drop'] = 0;
                    $report['o'][$campaign_id][$affiliate_id]['lf_admin_drop'] = 0;
                    $report['o'][$campaign_id][$affiliate_id]['lf_nlr_drop'] = 0;

                    /*
                    $report['o'][$campaign_id][$affiliate_id]['olr_total'] = 0;
                    $report['o'][$campaign_id][$affiliate_id]['olr_rejected'] = 0;
                    $report['o'][$campaign_id][$affiliate_id]['olr_failed'] = 0;
                    $report['o'][$campaign_id][$affiliate_id]['olr_success'] = 0;
                    $report['o'][$campaign_id][$affiliate_id]['olr_only'] = 1;
                    */

                    $report['o'][$campaign_id][$affiliate_id]['source'] = 4; //Where Source: 1 = OLR; 2 = NLR; 3 = Mixed; 4 = LF
                }

                $report['o'][$campaign][$affiliate]['received'] = $received;
                $report['o'][$campaign][$affiliate]['payout'] = $payout;
            }
        }

        // Log::info($coregs);
        // Log::info('OLR Campaigns:');
        // Log::info($olr_campaigns);
        // Log::info('NNO PAYOUTS');
        // Log::info($no_payouts);
        // Log::info($campaign_affiliate_no_payouts);

        //DELETE RECORDS IF ALREADY EXISTS FOR THAT DAY
        DB::table('coreg_reports')->where('date', '=', $date_yesterday)->delete();

        Log::info('Saving to Database...');

        foreach ($report as $sources) {
            foreach ($sources as $campaign => $details) {
                foreach ($details as $affiliate => $info) {
                    $affiliate_id = isset($revAffiliates[$affiliate]) ? $revAffiliates[$affiliate] : $affiliate;
                    $revenue_tracker_id = isset($revAffiliates[$affiliate]) ? $affiliate : null;

                    /*
                    $lr_total = $info['lr_total'] + $info['olr_total'];
                    $lr_rejected = $info['lr_rejected'] + $info['olr_rejected'];
                    $lr_failed = $info['lr_failed'] + $info['olr_failed'];
                    $lr_success = $info['lr_success'] + $info['olr_success'];
                    */

                    $lr_total = $info['lr_total'];
                    $lr_rejected = $info['lr_rejected'];
                    $lr_failed = $info['lr_failed'];
                    $lr_success = $info['lr_success'];

                    $payout = isset($info['payout']) ? $info['payout'] : 0;
                    $received = isset($info['received']) ? $info['received'] : 0;

                    if ($info['lf_total'] == 0) {
                        $cost = $lr_total * $payout;
                    } else {
                        $cost = ($info['lf_total'] - $info['lf_filter_drop']) * $payout;
                        // $cost = ($info['lf_total'] - $info['lf_filter_drop'] - $info['lf_nlr_drop']) * $payout;
                    }

                    // $data = CoregReport::firstOrNew([
                    //     'campaign_id' => $campaign,
                    //     'affiliate_id' => $affiliate_id,
                    //     'revenue_tracker_id' => $revenue_tracker_id,
                    //     'source' => $info['source'],
                    //     'date' => $date_yesterday
                    // ]);

                    $data = new CoregReport();
                    $data->campaign_id = $campaign;
                    $data->affiliate_id = $affiliate_id;
                    $data->revenue_tracker_id = $revenue_tracker_id;
                    $data->source = $info['source'];
                    $data->date = $date_yesterday;
                    $data->lf_total = $info['lf_total'];
                    $data->lf_filter_do = $info['lf_filter_drop'];
                    $data->lf_admin_do = $info['lf_admin_drop'];
                    $data->lf_nlr_do = $info['lf_nlr_drop'];
                    $data->lr_total = $lr_total;
                    $data->lr_rejected = $lr_rejected;
                    $data->lr_failed = $lr_failed;
                    $data->lr_pending = $info['lr_pending'];
                    $data->lr_cap = $info['lr_cap'];
                    $data->cost = $cost;
                    $data->revenue = $lr_success * $received;

                    // if(isset($info['olr_only'])) $data->olr_only = $info['olr_only'];

                    $data->save();

                    // if( (!isset($info['payout'])) || (!isset($info['received'])) ){
                    //      Log::info('No payout: '.$campaign.' - '.$affiliate);
                    // }
                }
            }
        }

        Log::info('Saved to Database');

        $inputs['excel'] = 1;
        $inputs['lead_date'] = $date_yesterday;
        $reports = CoregReport::getReport($inputs, null, null, 'we_get', 'desc')
            ->selectRaw('coreg_reports.*, revenue - cost as we_get')
            ->get();

        $affs = $reports->map(function ($st) {
            return $st->affiliate_id;
        });
        $affiliates = Affiliate::whereIn('id', $affs)->pluck('company', 'id');

        $cmps = $reports->map(function ($st) {
            return $st->campaign_id;
        });
        $campaigns = Campaign::whereIn('id', $cmps)->pluck('name', 'id');

        if ($reports) {
            Log::info('Creating Excel');
            $date = Carbon::parse($date_yesterday)->format('m/d/Y');
            $title = 'CoregReport_'.Carbon::parse($date_yesterday)->toDateString();

            if (Storage::disk('downloads')->has($title.'.xlsx')) {
                Storage::disk('downloads')->delete($title.'.xlsx');
            } //delete existing file

            Excel::create($title, function ($excel) use ($reports, $date, $affiliates, $campaigns) {
                $excel->sheet(Carbon::parse($date)->toDateString(), function ($sheet) use ($reports, $affiliates, $campaigns) {

                    $totalRows = count($reports) + 3;
                    //Set auto size for sheet
                    $sheet->setAutoSize(true);

                    $sheet->setColumnFormat([
                        'G' => '0.00',
                        'M' => '0.00',
                        'N' => '0.00',
                    ]);

                    //TITLE ROW
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

                    //TOTAL ROW
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

                    //HEADER ROW
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
                        $campaign_name = isset($campaigns[$report->campaign_id]) ? $campaigns[$report->campaign_id].'('.$report->campaign_id.')' : 'OLR Campaign ID: '.$report->campaign_id;
                        // if(($report->olr_only === 0 || $report->olr_only === 1) && $report->revenue_tracker_id === null) $revCol .= 'OLR: ';
                        $affiliate_name = $affiliates[$report->affiliate_id];
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
                        if ($report->source == 1) {
                            $row_color = '#dff0d8';
                        } elseif ($report->source == 2) {
                            $row_color = '#f2dede';
                        } elseif ($report->source == 4) {
                            $row_color = '#D9EDF7';
                        } else {
                            $row_color = '#fff';
                        }
                        // switch($report->source) {
                        //     case 1:
                        //         $row_color = '#dff0d8';
                        //         break;
                        //     case 2:
                        //         $row_color = '#f2dede';
                        //         break;
                        //     case 4:
                        //         // $row_color = '#d9edf7';
                        //         $row_color = '#D9EDF7';
                        //         break;
                        //     default:
                        //         $row_color = '#fff';
                        // }

                        // Set black background
                        $sheet->row($row_num++, function ($row) use ($row_color) {
                            $row->setBackground($row_color);
                        });

                    }
                });
            })->store('xlsx', storage_path('downloads'));

            if (config('app.type') == 'reports') {
                $contents = Storage::disk('downloads')->get($title.'.xlsx');
                if (Storage::disk('main')->has($title.'.xlsx')) {
                    Storage::disk('main')->delete($title.'.xlsx');
                } //delete existing file
                Storage::disk('main')->put($title.'.xlsx', $contents); //Copy Report to Main Server

                if (Storage::disk('main_slave')->has($title.'.xlsx')) {
                    Storage::disk('main_slave')->delete($title.'.xlsx');
                } //delete existing file
                Storage::disk('main_slave')->put($title.'.xlsx', $contents); //Copy Report to Main Server
            }

            $file_path = storage_path('downloads').'/'.$title.'.xlsx';
            // Log::info($file_path);
            Log::info('Done with Excel.');

            Log::info('Sending Email...');
            $excelAttachment = $file_path;

            $emailNotificationRecipient = config('settings.reports_email_notification_recipient');

            Mail::send('emails.coreg_report', ['url' => config('constants.APP_BASE_URL').'/admin/coregReports', 'date' => $date_yesterday],
                function ($message) use ($excelAttachment, $lr_build, $emailNotificationRecipient, $report_recipients) {

                    $message->from('admin@engageiq.com', 'Automated Report by '.$lr_build);

                    $message->to($emailNotificationRecipient, 'Marwil Burton');

                    if (count($report_recipients) > 0) {
                        foreach ($report_recipients as $recipient) {
                            $message->to($recipient);
                        }
                    }
                    /*$message->to('karla@engageiq.com', 'Karla Librero')
                        ->to('ariel@engageiq.com', 'Ariel Magbanua')
                        ->to($emailNotificationRecipient, 'Marwil Burton')
                        ->to('rohit@engageiq.com', 'Rohit Gambhir')
                        ->to('monty@engageiq.com', 'Monty Magbanua')
                        ->to('dexter@engageiq.com', 'Dexter Cadia');

                    if($lr_build == 'NLR') {
                        $message->to('delicia@engageiq.com','Delicia Wijaya')
                            ->to('jackie@engageiq.com', 'Jacquelin Beveridge')
                            ->to('johneil@engageiq.com', 'Johneil Quijano')
                            ->to('janhavi@engageiq.com', 'Janhavi Paranjape');
                    }*/

                    $message->subject('Co-Reg Report');
                    $message->attach($excelAttachment);
                });

            Log::info('Email Sent!');
        }

        Log::info('FINISHED!');
    }
}
