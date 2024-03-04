<?php

namespace App\Jobs\Reports;

use App\AffiliateReport;
use App\AffiliateRevenueTracker;
use App\Campaign;
use App\CampaignRevenueViewStatistic;
use App\Helpers\CakeSubIDSummaryHelper;
use App\Helpers\CPAWallAffluentSubAffiliateSummaryHelper;
use App\Helpers\CPAWallEpollSurveysHelper;
use App\Helpers\CPAWallJobToShopHelper;
use App\Helpers\CPAWallSBGSubAffiliateSummaryHelper;
use App\Helpers\CPAWallSurveySpotHelper;
use App\Helpers\ExternalPathIfficientHelper;
use App\Helpers\ExternalPathPermissionDataHelper;
use App\Helpers\ExternalPathRexadsHelper;
use App\Helpers\ExternalPathTiburonDataHelper;
use App\Helpers\JSONParser;
use App\Helpers\Repositories\AffiliateReportCurl;
use App\Jobs\Job;
use App\Lead;
use App\RevenueTrackerCakeStatistic;
use Carbon\Carbon;
use DB;
use ErrorException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Log;
use PDOException;

class AffiliateReportsV3 extends Job implements ShouldQueue
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
     *
     * @throws \Sabre\Xml\LibXMLException
     */
    public function handle(AffiliateReportCurl $affiliateReportCurl): void
    {
        if ($this->attempts() > 1) {
            return;
        }

        $startLog = Carbon::now();
        Log::info('Affiliate Revenue Tracker Start Time: '.$startLog->toDateTimeString());
        Log::info('Generating Internal Affiliate Reports...');

        //delete the existing records in case there's a need to repeat this job
        //RevenueTrackerCakeStatistic::whereRaw("DATE(created_at) = DATE('$this->dateFromStr')")->delete();

        //1 = internal type
        $params['type'] = 1;

        //for cake statistics
        $revTrackers = DB::table('affiliate_revenue_trackers')
            ->select(DB::raw("CONCAT(campaign_id,'-',affiliate_id) AS campaign_affiliate"), 'affiliate_id', 'campaign_id', 'revenue_tracker_id', 'offer_id')
            ->join('affiliates', 'affiliate_revenue_trackers.affiliate_id', '=', 'affiliates.id')
                            //->where('affiliates.type','=',1)
            ->where('offer_id', '!=', 1)
            ->orWhere(function ($subQuery) {
                $subQuery->where('campaign_id', '=', 1);
                $subQuery->where('offer_id', '=', 1);
                $subQuery->where('revenue_tracker_id', '=', 1);
            })
            ->groupBy('affiliate_revenue_trackers.affiliate_id', 'affiliate_revenue_trackers.revenue_tracker_id');
        //->pluck('revenue_tracker_id','campaign_affiliate');

        $revenueTrackers = $revTrackers;
        $revenueTrackers = $revenueTrackers->pluck('revenue_tracker_id', 'campaign_affiliate');
        $fullDataRevenueTrackers = $revTrackers;
        $fullDataRevenueTrackers = $fullDataRevenueTrackers->get();

        $campaignSummaryBaseURL = config('constants.CAKE_API_CAMPAIGN_SUMMARY_ALL_CAMPAIGNS_BASE_URL_V5');
        //$campaignSummaryURL = $campaignSummaryBaseURL.'&start_date='.$this->dateFromStr.'&end_date='.$this->dateToStr;

        //get cake data
        $prefix = config('constants.CAKE_SABRE_PREFIX_V5');
        $subPrefix = config('constants.CAKE_SABRE_SUB_PREFIX_V11');

        try {

            //get the content of the campaign summary
            //$campaigns = $response['value'][$prefix.'campaigns'];
            $campaigns = $affiliateReportCurl->campaignSummary($campaignSummaryBaseURL, $prefix, $subPrefix, $this->dateFromStr, $this->dateToStr);

            foreach ($campaigns as $campaign) {
                $value = $campaign['value'];

                //extract the affiliate_id and campaign_id
                $campaignID = $value[$prefix.'campaign'][$subPrefix.'campaign_id'];
                $affiliateID = $value[$prefix.'source_affiliate'][$subPrefix.'source_affiliate_id'];

                //check if affiliate_id and campaign does exist in revenue trackers table
                if (isset($revenueTrackers["$campaignID-$affiliateID"])) {
                    Log::info("campaign_id: $campaignID");
                    Log::info("affiliate_id: $affiliateID");
                    Log::info('revenue_tracker_id: '.$revenueTrackers["$campaignID-$affiliateID"]);

                    //meaning it exist so insert the stats
                    $revenueTrackerCakeStat = RevenueTrackerCakeStatistic::firstOrNew([
                        'affiliate_id' => $affiliateID,
                        'revenue_tracker_id' => $revenueTrackers["$campaignID-$affiliateID"],
                        'cake_campaign_id' => $campaignID,
                        'type' => 1,
                        'created_at' => $this->dateFromStr,
                    ]);

                    $clicks = intval($value[$prefix.'clicks']);
                    $payout = floatval($value[$prefix.'cost']);

                    $revenueTrackerCakeStat->clicks = $clicks;
                    $revenueTrackerCakeStat->payout = $payout;
                    $revenueTrackerCakeStat->created_at = $this->dateFromStr;
                    $revenueTrackerCakeStat->save();

                    Log::info('clicks: '.$clicks);
                    Log::info('payout: '.$payout);
                    Log::info('created_at: '.$this->dateFromStr);

                    //generate CPA WALL reports
                    $this->cpaWallReports($affiliateID, $revenueTrackers["$campaignID-$affiliateID"], $affiliateReportCurl);

                    //generate the leads reports
                    $this->leadReports($affiliateID, $revenueTrackers["$campaignID-$affiliateID"]);
                }
            }

            //create a RevenueTrackerCakeStatistic record for each revenue tracker with revenue_tracker_id = 1, offer_id = 1, campaign_id = 1
            foreach ($fullDataRevenueTrackers as $tracker) {
                if ($tracker->revenue_tracker_id == 1 &&
                    $tracker->campaign_id == 1 &&
                    $tracker->offer_id == 1) {
                    //meaning it exist so insert the stats
                    $revenueTrackerCakeStat = RevenueTrackerCakeStatistic::firstOrNew([
                        'affiliate_id' => $tracker->affiliate_id,
                        'revenue_tracker_id' => $tracker->revenue_tracker_id,
                        'cake_campaign_id' => $tracker->campaign_id,
                        'type' => 1,
                        'created_at' => $this->dateFromStr,
                    ]);

                    $revenueTrackerCakeStat->clicks = 0;
                    $revenueTrackerCakeStat->payout = 0.0;
                    $revenueTrackerCakeStat->created_at = $this->dateFromStr;
                    $revenueTrackerCakeStat->save();

                    //generate the leads reports
                    $this->leadReports($tracker->affiliate_id, $tracker->revenue_tracker_id);

                    //this is a special case
                    if ($tracker->affiliate_id == 1 && $tracker->revenue_tracker_id == 1) {
                        //generate CPA WALL reports
                        $this->cpaWallReports($tracker->affiliate_id, $tracker->revenue_tracker_id, $affiliateReportCurl);
                    }
                }
            }
        } catch (PDOException $e) {
            Log::info($e->getMessage());
            Log::info($e->getCode());
        }

        //this will refresh the TTR of beanstalkd
        // $this->touch();

        $endLog = Carbon::now();
        Log::info('Affiliate Revenue Tracker End Time: '.$endLog->toDateTimeString());
        Log::info('External Publisher Start Time: '.$endLog->toDateTimeString());

        //get and generate the lead reports from OLR
        // $this->getOLRLeadsRevenue();

        //generate the other API based revenues and external paths
        $this->others();

        $endLog = Carbon::now();

        $diffInHours = $startLog->diffInHours($endLog).' hours';

        $emailNotificationRecipient = env('REPORTS_EMAIL_NOTIFICATION_RECIPIENT', 'marwilburton@hotmail.com');

        //send email to Burt to notify that Affiliate Report Queue was successfully finished
        Mail::send('emails.affiliate_report_execution_email',
            ['startDate' => $this->dateFromStr, 'endDate' => $this->dateToStr, 'executionDuration' => $diffInHours],
            function ($m) use ($emailNotificationRecipient) {
                $m->from('ariel@engageiq.com', 'Ariel Magbanua');
                $m->to($emailNotificationRecipient, 'Marwil Burton')->subject('Affiliate Reports Job Queue Successfully Executed!');
            });

        Log::info('Internal Affiliate Reports generated!');
        Log::info('External Publisher End Time: '.$endLog->toDateTimeString());

        //execute the needed jobs here
        // $job = (new GenerateClicksVsRegistrationStatistics($this->dateFromStr, $this->dateToStr))->delay(2);
        // dispatch($job);

        // $job = (new GeneratePageViewStatistics($this->dateFromStr, $this->dateToStr))->delay(3);
        // dispatch($job);

        // This is placed here and not on separated job queue since this one needed to be generated after the affiliate revenues
        Log::info('Generating Campaign Revenue and View stats | '.$this->dateFromStr);

        //        $campaigns = Campaign::select(
        //            'campaigns.id',
        //            DB::raw("(SELECT COUNT(campaign_views.id) FROM campaign_views WHERE campaign_views.campaign_id = campaigns.id AND DATE(campaign_views.created_at) = DATE('$this->dateFromStr')) AS views"),
        //            DB::raw("(SELECT SUM(affiliate_reports.revenue) FROM affiliate_reports WHERE affiliate_reports.campaign_id = campaigns.id AND DATE(affiliate_reports.created_at) = DATE('$this->dateFromStr')) AS revenue"))->get();
        //
        //        Log::info($campaigns);
        //
        //        foreach ($campaigns as $campaign) {
        //            Log::info($this->dateFromStr);
        //
        //            $stat = CampaignRevenueViewStatistic::firstOrNew([
        //                'campaign_id' => $campaign->id,
        //                'created_at' => $this->dateFromStr
        //            ]);
        //
        //            $stat->revenue = is_null($campaign->revenue) ? 0 : $campaign->revenue;
        //            $stat->views = is_null($campaign->views) ? 0 : $campaign->views;
        //            $stat->created_at = $this->dateFromStr;
        //
        //            Log::info($stat);
        //            $stat->save();
        //        }

        $campaignRevenueViews = AffiliateReport::select('affiliate_reports.revenue_tracker_id', 'affiliate_reports.campaign_id', DB::raw('SUM(affiliate_reports.revenue) AS revenue'), DB::raw("(SELECT COUNT(campaign_views.id) FROM campaign_views WHERE campaign_views.campaign_id = affiliate_reports.campaign_id AND campaign_views.affiliate_id = affiliate_reports.revenue_tracker_id AND DATE(campaign_views.created_at) = DATE('$this->dateFromStr')) AS campaign_views "))
            ->whereRaw("DATE(affiliate_reports.created_at) = DATE('$this->dateFromStr')")
            ->groupBy('affiliate_reports.revenue_tracker_id', 'affiliate_reports.campaign_id')->get();

        foreach ($campaignRevenueViews as $campaignRevenueView) {
            Log::info($this->dateFromStr);

            $stat = CampaignRevenueViewStatistic::firstOrNew([
                'revenue_tracker_id' => $campaignRevenueView->revenue_tracker_id,
                'campaign_id' => $campaignRevenueView->campaign_id,
                'created_at' => $this->dateFromStr,
            ]);

            $stat->revenue = is_null($campaignRevenueView->revenue) ? 0 : $campaignRevenueView->revenue;
            $stat->views = is_null($campaignRevenueView->campaign_views) ? 0 : $campaignRevenueView->campaign_views;
            $stat->created_at = $this->dateFromStr;

            Log::info($stat);
            $stat->save();
        }

        Log::info('Campaign Revenue and View stats done!');
    }

    /*
    private function getOLRLeadsRevenue()
    {
        $leadReportsOLR = $leadReportsOLR = DB::connection('olr')->select("SELECT COUNT(PROGRAM_ID) AS lead_count, PROGRAM_ID, AFFILIATE_ID, DATE(CREATE_DATE) AS CREATE_DATE FROM LEADS WHERE LEAD_STEP='SEND_LEAD' AND LEAD_STATUS='SUCCESS' AND (DATE(CREATE_DATE)>=DATE('$this->dateFromStr') AND DATE(CREATE_DATE)<=DATE('$this->dateFromStr')) GROUP BY AFFILIATE_ID, DATE(CREATE_DATE), PROGRAM_ID");

        foreach($leadReportsOLR as $olrLeadReport)
        {
            $olrAffiliateID = $olrLeadReport->AFFILIATE_ID;

            //get the campaign record
            //Campaign::select('campaign_id','default_received')->where('olr_program_id','=',$leadReport->PROGRAM_ID)->with('payouts');
            $campaign = Campaign::whereNotNull('olr_program_id')
                                  ->where('olr_program_id','=',$olrLeadReport->PROGRAM_ID)
                                  ->with(['payouts' => function($query) use ($olrAffiliateID){
                                      $query->where('affiliate_id','=',$olrAffiliateID);
                                  }])
                                  ->first();

            if(empty($campaign))
            {
                //campaign does not exist so skip
                continue;
            }

            //get the affiliate of the revenue tracker
            $revenueTracker = AffiliateRevenueTracker::select('affiliate_id','revenue_tracker_id')->where('revenue_tracker_id','=',$olrAffiliateID)->first();

            if(!empty($revenueTracker)){
                try
                {

                    $affiliateReport = AffiliateReport::firstOrNew([
                        'affiliate_id' => $revenueTracker->affiliate_id,
                        'revenue_tracker_id' => $olrAffiliateID,
                        'campaign_id' => $campaign->id,
                        'created_at' => $this->dateFromStr
                    ]);

                    //calculate the payout
                    if(!empty($campaign->payouts->pivot->received))
                    {
                        $revenue = intval($olrLeadReport->lead_count) * doubleval($campaign->payouts->pivot->received);
                    }
                    else
                    {
                        $revenue = intval($olrLeadReport->lead_count) * doubleval($campaign->default_received);
                    }

                    //Log::info('OLR Report: ');
                    //Log::info("NLR lead_count: $affiliateReport->lead_count");
                    //Log::info("NLR Revenue: $affiliateReport->revenue");

                    $affiliateReport->lead_count = $affiliateReport->lead_count + $olrLeadReport->lead_count;
                    $affiliateReport->revenue = doubleval($affiliateReport->revenue) + $revenue;

                    //Log::info("affiliate_id: $revenueTracker->affiliate_id");
                    //Log::info("revenue_tracker_id: $olrAffiliateID");
                    //Log::info("campaign_id: $campaign->id");
                    //Log::info("created_at: $olrLeadReport->CREATE_DATE");
                    //Log::info("OLR lead_count: $olrLeadReport->lead_count");
                    //Log::info("Final lead_count: $affiliateReport->lead_count");
                    //Log::info("OLR Revenue: $revenue");
                    //Log::info("Final Revenue: $affiliateReport->revenue");

                    $affiliateReport->save();
                }
                catch(PDOException $e)
                {
                    Log::info($e->getMessage());
                    Log::info($e->getCode());
                }
            }
        }
    }
    */
    private function others()
    {
        $parser = new JSONParser();

        $cpaWALLAffluentCampaignID = env('CPA_WALL_AFFLUENT_CAMPAIGN_ID', 0);
        Log::info('---CPA WALL AFFLUENT---');
        Log::info('campaign_id: '.$cpaWALLAffluentCampaignID);
        $cpaWallAffluentSubAffiliateSummaryHelper = new CPAWallAffluentSubAffiliateSummaryHelper($cpaWALLAffluentCampaignID, Carbon::parse($this->dateFromStr), Carbon::parse($this->dateFromStr), $parser);
        $cpaWallAffluentSubAffiliateSummaryHelper->getStats();

        $cpaWALLSBGCampaignID = env('CPA_WALL_SBG_CAMPAIGN_ID', 0);
        Log::info('---CPA WALL SBG---');
        Log::info('campaign_id: '.$cpaWALLSBGCampaignID);
        $cpaWallSBGSubAffiliateSummaryHelper = new CPAWallSBGSubAffiliateSummaryHelper($cpaWALLSBGCampaignID, Carbon::parse($this->dateFromStr), Carbon::parse($this->dateFromStr), $parser);
        $cpaWallSBGSubAffiliateSummaryHelper->getStats();

        $cpaWallSurveySpotCampaignID = env('CPA_WALL_SURVEY_SPOT_CAMPAIGN_ID', 0);
        Log::info('---CPA WALL SURVEY SPOT---');
        Log::info('campaign_id: '.$cpaWallSurveySpotCampaignID);
        $cpaWallSurveySpotHelper = new CPAWallSurveySpotHelper($cpaWallSurveySpotCampaignID, Carbon::parse($this->dateFromStr), Carbon::parse($this->dateToStr), $parser);
        $cpaWallSurveySpotHelper->processConversions();

        $externalPathTiburonCampaignID = env('EXTERNAL_PATH_TIBURON_CAMPAIGN_ID', 0);
        Log::info('---EXTERNAL PATH TIBURON CAMPAIGN_ID---');
        Log::info('campaign_id: '.$externalPathTiburonCampaignID);
        $externalPathTiburonHelper = new ExternalPathTiburonDataHelper($externalPathTiburonCampaignID, $this->dateFromStr, $this->dateFromStr, $parser);
        $externalPathTiburonHelper->getReports();

        $externalPathRexAdsCampaignID = env('EXTERNAL_PATH_REXADS_CAMPAIGN_ID', 289);
        Log::info('---EXTERNAL PATH REXADS CAMPAIGN_ID---');
        Log::info('campaign_id: '.$externalPathRexAdsCampaignID);
        $externalRexAdsHelper = new ExternalPathRexadsHelper($externalPathRexAdsCampaignID, Carbon::parse($this->dateFromStr), Carbon::parse($this->dateFromStr), $parser);
        $externalRexAdsHelper->getReports();

        $cpaWallEpollSurveysCampaignID = env('CPA_WALL_EPOLL_CAMPAIGN_ID', 285);
        Log::info('---CPA WALL EPOLL CAMPAIGN_ID---');
        Log::info('campaign_id: '.$cpaWallEpollSurveysCampaignID);
        $cpaWallEpollSurveysHelper = new CPAWallEpollSurveysHelper($cpaWallEpollSurveysCampaignID, Carbon::parse($this->dateFromStr), Carbon::parse($this->dateFromStr), $parser);
        $cpaWallEpollSurveysHelper->getStats();

        $cpaWallJobToShopCampaignID = env('CPA_WALL_JOB_TO_SHOP_CAMPAIGN_ID', 297);
        Log::info('---CPA WALL JOB TO SHOP CAMPAIGN_ID---');
        Log::info('campaign_id: '.$cpaWallJobToShopCampaignID);
        $cpaWallJobToShopHelper = new CPAWallJobToShopHelper($cpaWallJobToShopCampaignID, Carbon::parse($this->dateFromStr), Carbon::parse($this->dateFromStr), $parser);
        $cpaWallJobToShopHelper->getStats();

        $ifficientCampaignID = env('EXTERNAL_PATH_IFFICIENT_CAMPAIGN_ID', 287);
        Log::info('---EXTERNAL PATH IFFICIENT CAMPAIGN_ID---');
        Log::info('campaign_id: '.$ifficientCampaignID);
        $externalPathIfficientHelper = new ExternalPathIfficientHelper($ifficientCampaignID, Carbon::parse($this->dateFromStr), Carbon::parse($this->dateFromStr));
        $externalPathIfficientHelper->getReports();

        $externalPathPermissionDataCampaignID = env('EXTERNAL_PATH_PERMISSION_DATA_CAMPAIGN_ID', 0);
        Log::info('---EXTERNAL PATH PERMISSION DATA---');
        Log::info('campaign_id: '.$externalPathPermissionDataCampaignID);
        $externalPathPermissionDataHelper = new ExternalPathPermissionDataHelper($externalPathPermissionDataCampaignID, $this->dateFromStr, $this->dateFromStr, 'EG3', 'XML');
        $externalPathPermissionDataHelper->getReports();

        //Push Crew Notifications
        $pushCrewAffiliateID = config('constants.PUSH_CREW_NOTIFICATIONS_CAKE_AFFILIATE_ID');
        $pushCrewCampaignID = env('PUSH_CREW_NOTIFICATIONS_CAMPAIGN_ID', 304);
        $prefix = config('constants.CAKE_SABRE_PREFIX_V1');
        Log::info('---PUSH CREW NOTIFICATIONS---');
        Log::info('campaign_id: '.$pushCrewCampaignID);
        $cakeSubIDSummaryHelper = new CakeSubIDSummaryHelper($pushCrewAffiliateID, $pushCrewCampaignID, $this->dateFromStr, $this->dateToStr, $prefix, $parser);
        $cakeSubIDSummaryHelper->getStats();
    }

    private function leadReports($affiliateID, $revenueTrackerID)
    {
        //Log::info("reset affiliate_id: $affiliateID");
        //Log::info("reset revenue_tracker_id: $revenueTrackerID");
        //Log::info("reset date: $this->dateFromStr");

        //clear first all the data
        //$affiliateReports = AffiliateReport::getReportsByRevenueTrackerAndDate($affiliateID,$revenueTrackerID,$this->dateFromStr)->get();

        $cpaWALLCampaigns = env('CPA_WALL_CAMPAIGN_IDS');
        $cpaWALLCampaigns = \GuzzleHttp\json_decode($cpaWALLCampaigns);

        $affiliateReports = AffiliateReport::getReportsByRevenueTrackerAndDate($affiliateID, $revenueTrackerID, $this->dateFromStr)
            ->where(function ($query) use ($cpaWALLCampaigns) {
                foreach ($cpaWALLCampaigns as $campaignID) {
                    $query->where('campaign_id', '!=', $campaignID);
                }
            })->get();

        //Log::info($affiliateReports);

        foreach ($affiliateReports as $report) {
            $report->lead_count = 0;
            $report->revenue = 0.0;
            $report->save();
        }

        /*
        $leadReports = Lead::revenueTrackerAffiliateReport([
            'revenue_tracker_id' => $revenueTrackerID,
            'created_at' => $this->dateFromStr
        ])->get();

        foreach($leadReports as $report)
        {
            $affiliateReport = AffiliateReport::firstOrNew([
                'affiliate_id' => $affiliateID,
                'revenue_tracker_id' => $report->affiliate_id,
                'campaign_id' => $report->campaign_id,
                'created_at' => $this->dateFromStr
            ]);

            $affiliateReport->lead_count = $report->lead_count;
            $affiliateReport->revenue = $report->revenue;
            $affiliateReport->save();
        }
        */

        // Get the reports for success and rejected leads
        $leadReports = Lead::revenueTrackerAffiliateReportWithStatuses([
            'revenue_tracker_id' => $revenueTrackerID,
            'created_at' => $this->dateFromStr,
            'lead_statuses' => [0, 1, 2],
        ])->get();

        foreach ($leadReports as $report) {
            $affiliateReport = AffiliateReport::firstOrNew([
                'affiliate_id' => $affiliateID,
                'revenue_tracker_id' => $report->affiliate_id,
                'campaign_id' => $report->campaign_id,
                'created_at' => $this->dateFromStr,
            ]);

            // Only update the lead count of status is success
            if ($report->lead_status == 1) {
                $affiliateReport->lead_count = $report->lead_count;
                $affiliateReport->revenue = $report->revenue;
            } elseif ($report->lead_status == 2) {
                // This row is for reject status therefore update the reject count
                $affiliateReport->reject_count = $report->lead_count;
            } else {
                // This row is for failed status therefore update the failed count
                $affiliateReport->failed_count = $report->lead_count;
            }

            $affiliateReport->save();
        }
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

        /*
        //get the CPA WALL Affluent ID from env file
        $cpaWALLAffluentCampaignID = env('CPA_WALL_AFFLUENT_CAMPAIGN_ID',0);
        $campaign = Campaign::find($cpaWALLAffluentCampaignID);
        $affluentSourceAffiliateSummaryCakeAPIBaseURL = config('constants.AFFLUENT_CAKE_API_CAMPAIGN_SUMMARY_BASE_URL_V6');

        Log::info('---CPA WALL AFFLUENT---');
        Log::info('campaign_id: '.$cpaWALLAffluentCampaignID);
        Log::info('affiliate_id: '.$affiliateID);
        Log::info('revenue_tracker_id: '.$revenueTrackerID);

        if($cpaWALLAffluentCampaignID>0 && $campaign->exists())
        {
            $this->processThirdPartyCPAWALLWithCD($affluentSourceAffiliateSummaryCakeAPIBaseURL,$cpaWALLAffluentCampaignID,$affiliateID,$revenueTrackerID,$affiliateReportCurl);
        }
        */

        /*
        //get the CPA WALL SBG ID from env file
        $cpaWALLSBGCampaignID = env('CPA_WALL_SBG_CAMPAIGN_ID',0);
        $campaign = Campaign::find($cpaWALLSBGCampaignID);
        $sbgCampaignSummaryCakeAPIBaseURL = config('constants.SBG_CAKE_API_CAMPAIGN_SUMMARY_BASE_URL_V6');

        Log::info('---CPA WALL SBG---');
        Log::info('campaign_id: '.$cpaWALLSBGCampaignID);

        if($cpaWALLSBGCampaignID>0 && $campaign->exists())
        {
            $this->processThirdPartyCPAWALLCD($sbgCampaignSummaryCakeAPIBaseURL,$cpaWALLSBGCampaignID,$affiliateID,$revenueTrackerID,$affiliateReportCurl);
        }
        */
    }

    private function processEngageIQCPAWALL($baseURL, $campaignID, $affiliateID, $revenueTrackerID, $affiliateReportCurl)
    {
        //get cake data
        $prefix = config('constants.CAKE_SABRE_PREFIX_V3');

        $affiliateReport = AffiliateReport::firstOrNew([
            'affiliate_id' => $affiliateID,
            'revenue_tracker_id' => $revenueTrackerID,
            'campaign_id' => $campaignID,
            'created_at' => $this->dateFromStr,
        ]);

        $affiliateReport->lead_count = 0;
        $affiliateReport->revenue = 0;
        $revenue = 0.0;

        try {
            //get the content of the campaign summary
            //$affiliateSummary = $response['value'][$prefix.'source_affiliates'][$prefix.'source_affiliate_summary'];
            $affiliateSummary = $affiliateReportCurl->engageIQCPAWALLAffiliateSummary($baseURL, $prefix, $revenueTrackerID, $this->dateFromStr, $this->dateToStr);

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

        //get the revenues from the offers to be deducted for the EngageIQ CPA WALL
        $campaignSummaryBaseURL = config('constants.CAKE_API_CAMPAIGN_SUMMARY_BASE_URL_NO_OFFER_ID_V5');
        $prefix = config('constants.CAKE_SABRE_PREFIX_V5');
        $subPrefix = config('constants.CAKE_SABRE_SUB_PREFIX_V11');
        $offersToBeDeducted = config('constants.CAKE_OFFERS_TO_BE_DEDUCTED');

        $excludedRevenue = 0.0;

        try {
            /*
            //$campaigns = $response['value'][$prefix.'campaigns'];
            $campaigns = $affiliateReportCurl->campaignSummaryForCPAWALLDeduction($campaignSummaryBaseURL,$prefix,$subPrefix,$revenueTrackerID,$this->dateFromStr,$this->dateToStr);
            Log::info('Revenue Tracker ID: '.$revenueTrackerID);

            foreach($campaigns as $campaign)
            {
                //get the offer
                $siteOffer = $campaign['value'][$prefix.'site_offer'];
                $siteOfferID = $siteOffer[$subPrefix.'site_offer_id'];

                if(isset($offersToBeDeducted[$siteOfferID]))
                {
                    Log::info('Site Offer ID: '.$siteOfferID);
                    Log::info('Revenue: '.doubleval($campaign['value'][$prefix.'revenue']));
                    $excludedRevenue = $excludedRevenue + doubleval($campaign['value'][$prefix.'revenue']);
                }
            }
            */
        } catch (ErrorException $e) {
            Log::info($e->getMessage());
            Log::info($e->getCode());
        }

        Log::info('Excluded Revenue: '.$excludedRevenue);
        $affiliateReport->lead_count = 0;
        $affiliateReport->revenue = $revenue - $excludedRevenue;
        $affiliateReport->save();
    }

    private function processThirdPartyCPAWALLWithCD($baseURL, $campaignID, $affiliateID, $revenueTrackerID, $affiliateReportCurl)
    {
        //$url = $baseURL.'&start_date='.$this->dateFromStr.'&end_date='.$this->dateToStr.'&sub_affiliate='.$revenueTrackerID;
        //$urlCD = $baseURL.'&start_date='.$this->dateFromStr.'&end_date='.$this->dateToStr.'&sub_affiliate=CD'.$revenueTrackerID;
        //Log::info($url);
        //Log::info($urlCD);

        $prefix = config('constants.CAKE_SABRE_AFFILIATES_PREFIX');

        $affiliateReport = AffiliateReport::firstOrNew([
            'affiliate_id' => $affiliateID,
            'revenue_tracker_id' => $revenueTrackerID,
            'campaign_id' => $campaignID,
            'created_at' => $this->dateFromStr,
        ]);

        $revenue = 0.0;
        $revenueWithCD = 0.0;

        try {
            $campaignSummaryRevenue = $affiliateReportCurl->thirdPartyCPAWALLCampaignSummaryWithCD($baseURL, $prefix, $revenueTrackerID, $this->dateFromStr, $this->dateToStr);

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
            $campaignSummaryRevenue = $affiliateReportCurl->thirdPartyCPAWALLCampaignSummaryWithCD($baseURL, $prefix, 'CD'.$revenueTrackerID, $this->dateFromStr, $this->dateToStr);

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

        $affiliateReport = AffiliateReport::firstOrNew([
            'affiliate_id' => $affiliateID,
            'revenue_tracker_id' => $revenueTrackerID,
            'campaign_id' => $campaignID,
            'created_at' => $this->dateFromStr,
        ]);

        $revenueWithCD = 0.0;

        try {
            $campaignSummaryRevenue = $affiliateReportCurl->thirdPartyCPAWALLCampaignSummaryWithCD($baseURL, $prefix, 'CD'.$revenueTrackerID, $this->dateFromStr, $this->dateToStr);

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
        Log::info('AffiliateReportsV3 Failed! - '.Carbon::now()->toDateTimeString());
    }
}
