<?php

namespace App\Jobs\Reports;

use App\AffiliateRevenueTracker;
use App\Campaign;
use App\Helpers\Repositories\AffiliateReportCurl;
use App\InternalIframeAffiliateReport;
use App\Jobs\Job;
use App\Lead;
use App\RevenueTrackerWebsiteViewStatistic;
use Carbon\Carbon;
use DB;
use ErrorException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Log;

class IframeAffiliateReports extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $dateFromStr;

    protected $dateToStr;

    /**
     * Create a new job instance.
     */
    public function __construct($dateFromStr, $dateToStr)
    {
        $this->dateFromStr = $dateFromStr;
        $this->dateToStr = $dateToStr;
    }

    /**
     * Execute the job.
     */
    public function handle(AffiliateReportCurl $affiliateReportCurl): void
    {
        if ($this->attempts() > 1) {
            return;
        }

        $startLog = Carbon::now();
        Log::info('Generating Iframe Affiliate Reports...');

        $dateFromStr = $this->dateFromStr;
        $dateToStr = $this->dateToStr;

        //for cake statistics
        $revenueTrackerWebViewStats = AffiliateRevenueTracker::join('affiliates', 'affiliate_revenue_trackers.affiliate_id', '=', 'affiliates.id')
            ->join('websites_view_tracker', 'affiliate_revenue_trackers.campaign_id', '=', 'websites_view_tracker.website_id')
            ->where(function ($query) use ($dateFromStr, $dateToStr) {
                $query->whereRaw('DATE(websites_view_tracker.created_at) >= DATE(?) and DATE(websites_view_tracker.created_at) <= DATE(?)', [$dateFromStr, $dateToStr]);
            })
            ->where('offer_id', '=', 1)
            ->groupBy('affiliate_revenue_trackers.affiliate_id', 'affiliate_revenue_trackers.revenue_tracker_id', 'affiliate_revenue_trackers.campaign_id', DB::raw('DATE(websites_view_tracker.created_at)'))
            ->select('affiliates.company', 'affiliate_revenue_trackers.affiliate_id', 'campaign_id', 'affiliate_revenue_trackers.revenue_tracker_id', DB::raw('COUNT(websites_view_tracker.website_id) AS view_count'), DB::raw('SUM(websites_view_tracker.payout) AS payout'), DB::raw('DATE(websites_view_tracker.created_at) AS created_at'))
            ->get();

        $revTrackerAffiliateMapping = [];
        $dates = [];

        //store the revenue_tracker_webview_statistics
        foreach ($revenueTrackerWebViewStats as $stat) {
            Log::info("rev_tracker_campaign_id: $stat->campaign_id");
            Log::info("affiliate_id: $stat->affiliate_id");

            $revTrackerAffiliateMapping[$stat->revenue_tracker_id] = $stat->affiliate_id;
            array_push($dates, $stat->created_at);

            $revenueTrackerWebViewStat = RevenueTrackerWebsiteViewStatistic::firstOrNew([
                'affiliate_id' => $stat->affiliate_id,
                'revenue_tracker_id' => $stat->revenue_tracker_id,
                'website_campaign_id' => $stat->campaign_id,
                'created_at' => $stat->created_at,
            ]);

            $revenueTrackerWebViewStat->passovers = $stat->view_count;
            $revenueTrackerWebViewStat->payout = $stat->payout;

            $revenueTrackerWebViewStat->save();
            Log::info("web_views: $stat->view_count");
            Log::info("payout: $stat->payout");
            Log::info("created_at: $stat->created_at");

            //generate CPA WALL reports for the particular affiliate revenue tracker
            $this->cpaWallReports($stat->affiliate_id, $stat->revenue_tracker_id, $affiliateReportCurl);
        }

        //this will refresh the TTR of beanstalkd
        // $this->touch();

        foreach ($dates as $date) {
            //generate the internal iframe lead reports from leads table using the revenue_tracker_id and date
            $leadReports = Lead::revenueTrackerAffiliateReport([
                //'revenue_tracker_id' => $stat->revenue_tracker_id,
                'created_at' => $date,
            ])->whereIn('affiliate_id', array_keys($revTrackerAffiliateMapping))->get();

            foreach ($leadReports as $report) {
                //get the affiliate_id
                $affiliateID = $revTrackerAffiliateMapping[$report->affiliate_id];

                $iframeAffiliateReport = InternalIframeAffiliateReport::firstOrNew([
                    'affiliate_id' => $affiliateID,
                    'revenue_tracker_id' => $report->affiliate_id,
                    'campaign_id' => $report->campaign_id,
                    'created_at' => $date,
                ]);

                Log::info("affiliate_id: $affiliateID");
                Log::info("revenue_tracker_id: $report->affiliate_id");
                Log::info("campaign_id: $report->campaign_id");
                Log::info("date: $date");

                $iframeAffiliateReport->lead_count = $report->lead_count;
                $iframeAffiliateReport->revenue = $report->revenue;
                $iframeAffiliateReport->save();
            }
        }

        $endLog = Carbon::now();
        $diffInHours = $startLog->diffInHours($endLog).' hours';

        $emailNotificationRecipient = env('REPORTS_EMAIL_NOTIFICATION_RECIPIENT', 'marwilburton@hotmail.com');

        //send email to Burt to notify that Internal Iframe Affiliate Report Queue was successfully finished
        Mail::send('emails.affiliate_report_execution_email',
            ['startDate' => $this->dateFromStr, 'endDate' => $this->dateToStr, 'executionDuration' => $diffInHours],
            function ($m) use ($emailNotificationRecipient) {
                $m->from('ariel@engageiq.com', 'Ariel Magbanua');
                $m->to($emailNotificationRecipient, 'Marwil Burton')->subject('Internal Iframe Affiliate Reports Job Queue Successfully Executed!');
            });

        Log::info('Internal Iframe Affiliate Reports generated!');
    }

    /**
     * Function that will gather all CPA WALL reports
     */
    private function cpaWallReports($affiliateID, $revenueTrackerID, $affiliateReportCurl)
    {
        //get the EngageIQ CPA WALL from ENV file
        $cpaWALLEngageIQCampaignID = env('CPA_WALL_ENGAGEIQ_CAMPAIGN_ID', 0);
        $campaign = Campaign::find($cpaWALLEngageIQCampaignID);
        $engageiqSourceAffiliateSummaryCakeAPIBaseURL = config('constants.CAKE_API_SOURCE_AFFILIATE_SUMMARY_BASE_URL_V3');

        Log::info('---CPA WALL---');
        Log::info('eiq_campaign_id: '.$cpaWALLEngageIQCampaignID);
        Log::info('affiliate_id: '.$affiliateID);
        Log::info('revenue_tracker_id: '.$revenueTrackerID);

        if ($cpaWALLEngageIQCampaignID > 0 && $campaign->exists()) {
            $this->processEngageIQCPAWALL($engageiqSourceAffiliateSummaryCakeAPIBaseURL, $cpaWALLEngageIQCampaignID, $affiliateID, $revenueTrackerID, $affiliateReportCurl);
        }

        //get the CPA WALL Affluent ID from env file
        $cpaWALLAffluentCampaignID = env('CPA_WALL_AFFLUENT_CAMPAIGN_ID', 0);
        $campaign = Campaign::find($cpaWALLAffluentCampaignID);
        $affluentSourceAffiliateSummaryCakeAPIBaseURL = config('constants.AFFLUENT_CAKE_API_CAMPAIGN_SUMMARY_BASE_URL_V6');

        Log::info('---CPA WALL AFFLUENT---');
        Log::info('campaign_id: '.$cpaWALLAffluentCampaignID);
        Log::info('affiliate_id: '.$affiliateID);
        Log::info('revenue_tracker_id: '.$revenueTrackerID);

        if ($cpaWALLAffluentCampaignID > 0 && $campaign->exists()) {
            $this->processThirdPartyCPAWALLWithCD($affluentSourceAffiliateSummaryCakeAPIBaseURL, $cpaWALLAffluentCampaignID, $affiliateID, $revenueTrackerID, $affiliateReportCurl);
        }

        //get the CPA WALL SBG ID from env file
        $cpaWALLSBGCampaignID = env('CPA_WALL_SBG_CAMPAIGN_ID', 0);
        $campaign = Campaign::find($cpaWALLSBGCampaignID);
        $sbgCampaignSummaryCakeAPIBaseURL = config('constants.SBG_CAKE_API_CAMPAIGN_SUMMARY_BASE_URL_V6');

        Log::info('---CPA WALL SBG---');
        Log::info('campaign_id: '.$cpaWALLSBGCampaignID);

        if ($cpaWALLSBGCampaignID > 0 && $campaign->exists()) {
            $this->processThirdPartyCPAWALLCD($sbgCampaignSummaryCakeAPIBaseURL, $cpaWALLSBGCampaignID, $affiliateID, $revenueTrackerID, $affiliateReportCurl);
        }
    }

    private function processEngageIQCPAWALL($baseURL, $campaignID, $affiliateID, $revenueTrackerID, $affiliateReportCurl)
    {
        //get cake data
        $prefix = config('constants.CAKE_SABRE_PREFIX_V3');

        $affiliateReport = InternalIframeAffiliateReport::firstOrNew([
            'affiliate_id' => $affiliateID,
            'revenue_tracker_id' => $revenueTrackerID,
            'campaign_id' => $campaignID,
            'created_at' => $this->dateFromStr,
        ]);

        $affiliateReport->lead_count = 0;
        $affiliateReport->revenue = 0;
        $revenue = 0.0;

        $dateFromStr = $this->dateFromStr;
        $dateToStr = Carbon::parse($this->dateFromStr)->addDay()->toDateString();

        try {
            //get the content of the campaign summary
            //$affiliateSummary = $response['value'][$prefix.'source_affiliates'][$prefix.'source_affiliate_summary'];
            $affiliateSummary = $affiliateReportCurl->engageIQCPAWALLAffiliateSummary($baseURL, $prefix, $revenueTrackerID, $dateFromStr, $dateToStr);

            if ($affiliateSummary != null) {
                //get the revenue
                $revenue = empty($affiliateSummary[$prefix.'revenue']) || $affiliateSummary[$prefix.'revenue'] == null ? 0.0 : $affiliateSummary[$prefix.'revenue'];
            }

            //Log::info('engageiq_affiliate_id: '.$affiliateID);
            //Log::info('revenue_tracker_id: '.$revenueTrackerID);
            //Log::info('engageiq_campaign_id: '.$campaignID);
            //Log::info('lead_count: 0');
            //Log::info('revenue: '.$revenue);
            //Log::info('created_at: '.$this->dateFromStr);
        } catch (ErrorException $e) {
            Log::info('Error ErrorException: '.$e->getMessage());
            Log::info('engageiq_affiliate_id: '.$affiliateID);
            Log::info('revenue_tracker_id: '.$revenueTrackerID);
            Log::info('engageiq_campaign_id: '.$campaignID);
            Log::info('created_at: '.$this->dateFromStr);
        }

        $affiliateReport->lead_count = 0;
        $affiliateReport->revenue = $revenue;
        $affiliateReport->save();
    }

    private function processThirdPartyCPAWALLWithCD($baseURL, $campaignID, $affiliateID, $revenueTrackerID, $affiliateReportCurl)
    {
        //$url = $baseURL.'&start_date='.$this->dateFromStr.'&end_date='.$this->dateToStr.'&sub_affiliate='.$revenueTrackerID;
        //$urlCD = $baseURL.'&start_date='.$this->dateFromStr.'&end_date='.$this->dateToStr.'&sub_affiliate=CD'.$revenueTrackerID;
        //Log::info($url);
        //Log::info($urlCD);

        $prefix = config('constants.CAKE_SABRE_AFFILIATES_PREFIX');

        $affiliateReport = InternalIframeAffiliateReport::firstOrNew([
            'affiliate_id' => $affiliateID,
            'revenue_tracker_id' => $revenueTrackerID,
            'campaign_id' => $campaignID,
            'created_at' => $this->dateFromStr,
        ]);

        $revenue = 0.0;
        $revenueWithCD = 0.0;

        $dateFromStr = $this->dateFromStr;
        $dateToStr = Carbon::parse($this->dateFromStr)->addDay()->toDateString();

        try {
            $campaignSummaryRevenue = $affiliateReportCurl->thirdPartyCPAWALLCampaignSummaryWithCD($baseURL, $prefix, $revenueTrackerID, $dateFromStr, $dateToStr);

            //get the content of the campaign summary
            if ($campaignSummaryRevenue != null) {
                //$campaignSummaryRevenue = $response['value'][$prefix.'summary'];

                //get the revenue for the without CD
                $revenue = empty($campaignSummaryRevenue[$prefix.'revenue']) || $campaignSummaryRevenue[$prefix.'revenue'] == null ? 0.0 : $campaignSummaryRevenue[$prefix.'revenue'];
            }

            //Log::info('engageiq_affiliate_id: '.$affiliateID);
            //Log::info('revenue_tracker_id: '.$revenueTrackerID);
            //Log::info('engageiq_campaign_id: '.$campaignID);
            //Log::info('lead_count: 0');
            //Log::info('revenue(no CD): '.$revenue);
            //Log::info('created_at: '.$this->dateFromStr);
        } catch (ErrorException $e) {
            Log::info('Error ErrorException: '.$e->getMessage());
            Log::info('engageiq_affiliate_id: '.$affiliateID);
            Log::info('revenue_tracker_id: '.$revenueTrackerID);
            Log::info('engageiq_campaign_id: '.$campaignID);
            Log::info('created_at: '.$this->dateFromStr);
        }

        try {
            //this call has CD
            $campaignSummaryRevenue = $affiliateReportCurl->thirdPartyCPAWALLCampaignSummaryWithCD($baseURL, $prefix, 'CD'.$revenueTrackerID, $dateFromStr, $dateToStr);

            //get the content of the campaign summary
            if ($campaignSummaryRevenue != null) {
                //$campaignSummaryRevenue = $response['value'][$prefix.'summary'];

                //get the revenue for the without CD
                $revenueWithCD = empty($campaignSummaryRevenue[$prefix.'revenue']) || $campaignSummaryRevenue[$prefix.'revenue'] == null ? 0.0 : $campaignSummaryRevenue[$prefix.'revenue'];
            }

            //Log::info('engageiq_affiliate_id: '.$affiliateID);
            //Log::info('revenue_tracker_id: '.$revenueTrackerID);
            //Log::info('engageiq_campaign_id: '.$campaignID);
            //Log::info('lead_count: 0');
            //Log::info('revenue(with CD): '.$revenueWithCD);
            //Log::info('created_at: '.$this->dateFromStr);
        } catch (ErrorException $e) {
            Log::info('Error ErrorException: '.$e->getMessage());
            Log::info('engageiq_affiliate_id: '.$affiliateID);
            Log::info('revenue_tracker_id: '.$revenueTrackerID);
            Log::info('engageiq_campaign_id: '.$campaignID);
            Log::info('created_at: '.$this->dateFromStr);
        }

        //save the affiliate report revenue
        $affiliateReport->lead_count = 0;
        $affiliateReport->revenue = $revenue + $revenueWithCD;
        $affiliateReport->save();
    }

    private function processThirdPartyCPAWALLCD($baseURL, $campaignID, $affiliateID, $revenueTrackerID, $affiliateReportCurl)
    {
        //$urlCD = $baseURL.'&start_date='.$this->dateFromStr.'&end_date='.$this->dateToStr.'&sub_affiliate=CD'.$revenueTrackerID;
        //Log::info($urlCD);

        $prefix = config('constants.CAKE_SABRE_AFFILIATES_PREFIX');

        $affiliateReport = InternalIframeAffiliateReport::firstOrNew([
            'affiliate_id' => $affiliateID,
            'revenue_tracker_id' => $revenueTrackerID,
            'campaign_id' => $campaignID,
            'created_at' => $this->dateFromStr,
        ]);

        $revenueWithCD = 0.0;

        $dateFromStr = $this->dateFromStr;
        $dateToStr = Carbon::parse($this->dateFromStr)->addDay()->toDateString();

        try {
            $campaignSummaryRevenue = $affiliateReportCurl->thirdPartyCPAWALLCampaignSummaryWithCD($baseURL, $prefix, 'CD'.$revenueTrackerID, $dateFromStr, $dateToStr);

            //get the content of the campaign summary
            if ($campaignSummaryRevenue != null) {
                //$campaignSummaryRevenue = $response['value'][$prefix.'summary'];
                //get the revenue for the without CD
                $revenueWithCD = empty($campaignSummaryRevenue[$prefix.'revenue']) || $campaignSummaryRevenue[$prefix.'revenue'] == null ? 0.0 : $campaignSummaryRevenue[$prefix.'revenue'];
            }

            $affiliateReport->lead_count = 0;
            $affiliateReport->revenue = $revenueWithCD;
            $affiliateReport->save();

            //Log::info('engageiq_affiliate_id: '.$affiliateID);
            //Log::info('revenue_tracker_id: '.$revenueTrackerID);
            //Log::info('engageiq_campaign_id: '.$campaignID);
            //Log::info('lead_count: 0');
            //Log::info('revenue(with CD): '.$revenueWithCD);
            //Log::info('created_at: '.$this->dateFromStr);
        } catch (ErrorException $e) {
            $affiliateReport->lead_count = 0;
            $affiliateReport->revenue = 0;
            $affiliateReport->save();

            Log::info('Error ErrorException: '.$e->getMessage());
            Log::info('engageiq_affiliate_id: '.$affiliateID);
            Log::info('revenue_tracker_id: '.$revenueTrackerID);
            Log::info('engageiq_campaign_id: '.$campaignID);
            Log::info('created_at: '.$this->dateFromStr);
        }
    }

    public function touch()
    {
        if (method_exists($this->job, 'getPheanstalk')) {
            $this->job->getPheanstalk()->touch($this->job->getPheanstalkJob());

            Log::info('Current job timer refresh!');
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(): void
    {
        //job failed
        Log::info('IframeAffiliateReports Failed! - '.Carbon::now()->toDateTimeString());
    }
}
