<?php

namespace App\Jobs\Reports;

use App\AffiliateReport;
use App\AffiliateRevenueTracker;
use App\Campaign;
use App\Helpers\CPAWallEpollSurveysHelper;
use App\Helpers\CPAWallJobToShopHelper;
use App\Helpers\CPAWallSurveySpotHelper;
use App\Helpers\ExternalPathPermissionDataHelper;
use App\Helpers\ExternalPathRexadsHelper;
use App\Helpers\ExternalPathTiburonDataHelper;
use App\Helpers\JSONParser;
use App\Jobs\Job;
use App\Lead;
use App\RevenueTrackerCakeStatistic;
use Carbon\Carbon;
use Curl\Curl;
use DB;
use ErrorException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Log;
use PDOException;
use Sabre\Xml\Reader;

class AffiliateReportsV2 extends Job implements ShouldQueue
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
    public function handle(): void
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
        $revenueTrackers = DB::table('affiliate_revenue_trackers')
            ->select(DB::raw("CONCAT(campaign_id,'-',affiliate_id) AS campaign_affiliate"), 'affiliate_id', 'campaign_id', 'revenue_tracker_id')
            ->join('affiliates', 'affiliate_revenue_trackers.affiliate_id', '=', 'affiliates.id')
            ->where('affiliates.type', '=', 1)
            ->groupBy('affiliate_revenue_trackers.affiliate_id', 'affiliate_revenue_trackers.revenue_tracker_id')
            ->pluck('revenue_tracker_id', 'campaign_affiliate');

        $campaignSummaryBaseURL = config('constants.CAKE_API_CAMPAIGN_SUMMARY_ALL_CAMPAIGNS_BASE_URL_V5');
        $campaignSummaryURL = $campaignSummaryBaseURL.'&start_date='.$this->dateFromStr.'&end_date='.$this->dateToStr;

        //get cake data
        $prefix = config('constants.CAKE_SABRE_PREFIX_V5');
        $subPrefix = config('constants.CAKE_SABRE_SUB_PREFIX_V11');

        $reader = new Reader();
        $reader->elementMap = [
            $prefix.'campaign_summary_response' => 'Sabre\Xml\Element\KeyValue',
            $prefix.'campaign_summary' => 'Sabre\Xml\Element\KeyValue',
            $prefix.'campaign' => 'Sabre\Xml\Element\KeyValue',
            $prefix.'source_affiliate' => 'Sabre\Xml\Element\KeyValue',
            $prefix.'source_affiliate_id' => 'Sabre\Xml\Element\KeyValue',
            $prefix.'source_affiliate_name' => 'Sabre\Xml\Element\KeyValue',
            //$prefix.'site_offer' => 'Sabre\Xml\Element\KeyValue',
            //$prefix.'site_offer_id' => 'Sabre\Xml\Element\KeyValue'
        ];

        Log::info($campaignSummaryURL);

        $curl = new Curl();
        $curl->get($campaignSummaryURL);

        try {
            $reader->xml($curl->response);
            $response = $reader->parse();

            //get the content of the campaign summary
            $campaigns = $response['value'][$prefix.'campaigns'];

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
                    $this->cpaWallReports($affiliateID, $revenueTrackers["$campaignID-$affiliateID"]);

                    //generate the leads reports
                    $this->leadReports($affiliateID, $revenueTrackers["$campaignID-$affiliateID"]);
                }
            }
        } catch (ErrorException $e) {
            Log::info($e->getMessage());
            Log::info($e->getCode());
        } catch (PDOException $e) {
            Log::info($e->getMessage());
            Log::info($e->getCode());
        }

        $endLog = Carbon::now();
        Log::info('Affiliate Revenue Tracker End Time: '.$endLog->toDateTimeString());
        Log::info('External Publisher Start Time: '.$endLog->toDateTimeString());

        //get and generate the lead reports from OLR
        $this->getOLRLeadsRevenue();

        //generate the other API based revenues and external paths
        $this->others();

        $endLog = Carbon::now();

        $diffInHours = $startLog->diffInHours($endLog).' hours';

        //send email to Burt to notify that Affiliate Report Queue was successfully finished
        Mail::send('emails.affiliate_report_execution_email', ['startDate' => $this->dateFromStr, 'endDate' => $this->dateToStr, 'executionDuration' => $diffInHours], function ($m) {
            $m->from('ariel@engageiq.com', 'Ariel Magbanua');
            $m->to('burt@engageiq.com', 'Marwil Burton')->subject('Affiliate Reports Job Queue Successfully Executed!');
        });

        Log::info('Internal Affiliate Reports generated!');
        Log::info('External Publisher End Time: '.$endLog->toDateTimeString());
    }

    private function getOLRLeadsRevenue()
    {
        $leadReportsOLR = $leadReportsOLR = DB::connection('olr')->select("SELECT COUNT(PROGRAM_ID) AS lead_count, PROGRAM_ID, AFFILIATE_ID, DATE(CREATE_DATE) AS CREATE_DATE FROM LEADS WHERE LEAD_STEP='SEND_LEAD' AND LEAD_STATUS='SUCCESS' AND (DATE(CREATE_DATE)>=DATE('$this->dateFromStr') AND DATE(CREATE_DATE)<=DATE('$this->dateFromStr')) GROUP BY AFFILIATE_ID, DATE(CREATE_DATE), PROGRAM_ID");

        foreach ($leadReportsOLR as $olrLeadReport) {
            $olrAffiliateID = $olrLeadReport->AFFILIATE_ID;

            //get the campaign record
            //Campaign::select('campaign_id','default_received')->where('olr_program_id','=',$leadReport->PROGRAM_ID)->with('payouts');
            $campaign = Campaign::whereNotNull('olr_program_id')
                ->where('olr_program_id', '=', $olrLeadReport->PROGRAM_ID)
                ->with(['payouts' => function ($query) use ($olrAffiliateID) {
                    $query->where('affiliate_id', '=', $olrAffiliateID);
                }])
                ->first();

            if (empty($campaign)) {
                //campaign does not exist so skip
                continue;
            }

            //get the affiliate of the revenue tracker
            $revenueTracker = AffiliateRevenueTracker::select('affiliate_id', 'revenue_tracker_id')->where('revenue_tracker_id', '=', $olrAffiliateID)->first();

            if (! empty($revenueTracker)) {

                try {

                    $affiliateReport = AffiliateReport::firstOrNew([
                        'affiliate_id' => $revenueTracker->affiliate_id,
                        'revenue_tracker_id' => $olrAffiliateID,
                        'campaign_id' => $campaign->id,
                        'created_at' => $this->dateFromStr,
                    ]);

                    //calculate the payout
                    if (! empty($campaign->payouts->pivot->received)) {
                        $revenue = intval($olrLeadReport->lead_count) * floatval($campaign->payouts->pivot->received);
                    } else {
                        $revenue = intval($olrLeadReport->lead_count) * floatval($campaign->default_received);
                    }

                    //Log::info('OLR Report: ');
                    //Log::info("NLR lead_count: $affiliateReport->lead_count");
                    //Log::info("NLR Revenue: $affiliateReport->revenue");

                    $affiliateReport->lead_count = $affiliateReport->lead_count + $olrLeadReport->lead_count;
                    $affiliateReport->revenue = floatval($affiliateReport->revenue) + $revenue;

                    //Log::info("affiliate_id: $revenueTracker->affiliate_id");
                    //Log::info("revenue_tracker_id: $olrAffiliateID");
                    //Log::info("campaign_id: $campaign->id");
                    //Log::info("created_at: $olrLeadReport->CREATE_DATE");
                    //Log::info("OLR lead_count: $olrLeadReport->lead_count");
                    //Log::info("Final lead_count: $affiliateReport->lead_count");
                    //Log::info("OLR Revenue: $revenue");
                    //Log::info("Final Revenue: $affiliateReport->revenue");

                    $affiliateReport->save();
                } catch (ErrorException $e) {
                    Log::info($e->getMessage());
                    Log::info($e->getCode());
                } catch (PDOException $e) {
                    Log::info($e->getMessage());
                    Log::info($e->getCode());
                }
            }
        }
    }

    private function others()
    {
        $parser = new JSONParser();

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

        /*
        $externalPathPermissionDataCampaignID = env('EXTERNAL_PATH_PERMISSION_DATA_CAMPAIGN_ID',0);
        Log::info('---EXTERNAL PATH PERMISSION DATA---');
        Log::info('campaign_id: '.$externalPathPermissionDataCampaignID);
        $externalPathPermissionDataHelper = new ExternalPathPermissionDataHelper($externalPathPermissionDataCampaignID, $this->dateFromStr, $this->dateToStr, 'EGI', 'XML');
        $externalPathPermissionDataHelper->getReports();
        */
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

        $leadReports = Lead::revenueTrackerAffiliateReport([
            'revenue_tracker_id' => $revenueTrackerID,
            'created_at' => $this->dateFromStr,
        ])->get();

        foreach ($leadReports as $report) {
            $affiliateReport = AffiliateReport::firstOrNew([
                'affiliate_id' => $affiliateID,
                'revenue_tracker_id' => $report->affiliate_id,
                'campaign_id' => $report->campaign_id,
                'created_at' => $this->dateFromStr,
            ]);

            $affiliateReport->lead_count = $report->lead_count;
            $affiliateReport->revenue = $report->revenue;
            $affiliateReport->save();
        }
    }

    private function cpaWallReports($affiliateID, $revenueTrackerID)
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
            $this->processEngageIQCPAWALL($engageiqSourceAffiliateSummaryCakeAPIBaseURL, $cpaWALLEngageIQCampaignID, $affiliateID, $revenueTrackerID);
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
            $this->processThirdPartyCPAWALLWithCD($affluentSourceAffiliateSummaryCakeAPIBaseURL,$cpaWALLAffluentCampaignID,$affiliateID,$revenueTrackerID);
        }
        */

        //get the CPA WALL SBG ID from env file
        $cpaWALLSBGCampaignID = env('CPA_WALL_SBG_CAMPAIGN_ID', 0);
        $campaign = Campaign::find($cpaWALLSBGCampaignID);
        $sbgCampaignSummaryCakeAPIBaseURL = config('constants.SBG_CAKE_API_CAMPAIGN_SUMMARY_BASE_URL_V6');

        Log::info('---CPA WALL SBG---');
        Log::info('campaign_id: '.$cpaWALLSBGCampaignID);

        if ($cpaWALLSBGCampaignID > 0 && $campaign->exists()) {
            $this->processThirdPartyCPAWALLCD($sbgCampaignSummaryCakeAPIBaseURL, $cpaWALLSBGCampaignID, $affiliateID, $revenueTrackerID);
        }
    }

    private function processEngageIQCPAWALL($baseURL, $campaignID, $affiliateID, $revenueTrackerID)
    {
        //if the campaign id exist proceed generating through API if not then don't
        $url = $baseURL.'&start_date='.$this->dateFromStr.'&end_date='.$this->dateToStr.'&source_affiliate_id='.$revenueTrackerID;

        //get cake data
        $prefix = config('constants.CAKE_SABRE_PREFIX_V3');

        $reader = new Reader();
        $reader->elementMap = [
            $prefix.'source_affiliate_summary_response' => 'Sabre\Xml\Element\KeyValue',
            $prefix.'source_affiliate_summary' => 'Sabre\Xml\Element\KeyValue',
            $prefix.'source_affiliates' => 'Sabre\Xml\Element\KeyValue',
        ];

        $curl = new Curl();
        $curl->get($url);

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
            $reader->xml($curl->response);
            $response = $reader->parse();

            //get the content of the campaign summary
            $affiliateSummary = $response['value'][$prefix.'source_affiliates'][$prefix.'source_affiliate_summary'];

            //get the revenue
            $revenue = empty($affiliateSummary[$prefix.'revenue']) || $affiliateSummary[$prefix.'revenue'] == null ? 0.0 : $affiliateSummary[$prefix.'revenue'];

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
        $offersToBeDeducted = config('constants.CAKE_OFFERS_TO_BE_DEDUCTED');
        $subPrefix = config('constants.CAKE_SABRE_SUB_PREFIX_V11');

        $excludedRevenue = 0.0;

        $url = config('constants.CAKE_API_CAMPAIGN_SUMMARY_BASE_URL_NO_OFFER_ID_V5').
            '&start_date='.$this->dateFromStr.
            '&end_date='.$this->dateToStr.
            '&source_affiliate_id='.$revenueTrackerID.
            '&campaign_id=0'.
            '&site_offer_id=0';

        Log::info('deduct offer url: '.$url);

        $prefix = config('constants.CAKE_SABRE_PREFIX_V5');

        //read the response
        $curl = new Curl();
        $curl->get($url);
        $reader = new Reader();

        $reader->elementMap = [
            $prefix.'campaign_summary_response' => 'Sabre\Xml\Element\KeyValue',
            $prefix.'campaign_summary' => 'Sabre\Xml\Element\KeyValue',
            $prefix.'campaign' => 'Sabre\Xml\Element\KeyValue',
            $prefix.'site_offer' => 'Sabre\Xml\Element\KeyValue',
        ];

        try {
            $reader->xml($curl->response);
            $response = $reader->parse();

            $campaigns = $response['value'][$prefix.'campaigns'];

            Log::info('Revenue Tracker ID: '.$revenueTrackerID);

            if (count($campaigns) > 0) {
                foreach ($campaigns as $campaign) {
                    //get the offer
                    $siteOffer = $campaign['value'][$prefix.'site_offer'];
                    $siteOfferID = $siteOffer[$subPrefix.'site_offer_id'];

                    if (isset($offersToBeDeducted[$siteOfferID])) {
                        Log::info('Site Offer ID: '.$siteOfferID);
                        Log::info('Revenue: '.floatval($campaign['value'][$prefix.'revenue']));
                        $excludedRevenue = $excludedRevenue + floatval($campaign['value'][$prefix.'revenue']);
                    }
                }
            }

        } catch (ErrorException $e) {
            Log::info($e->getMessage());
            Log::info($e->getCode());
        }

        Log::info('Excluded Revenue: '.$excludedRevenue);
        $affiliateReport->lead_count = 0;
        $affiliateReport->revenue = $revenue - $excludedRevenue;
        $affiliateReport->save();

        /*
        foreach ($offersToBeDeducted as $offer)
        {
            $url = config('constants.CAKE_API_CAMPAIGN_SUMMARY_BASE_URL_NO_OFFER_ID_V5').
                '&start_date='.$this->dateFromStr.
                '&end_date='.$this->dateToStr.
                '&source_affiliate_id='.$revenueTrackerID.
                '&campaign_id=0'.
                '&site_offer_id='.$offer;

            Log::info('deduct offer url: '.$url);
            Log::info('deduct_site_offer_id: '.$offer);

            $prefix = config('constants.CAKE_SABRE_PREFIX_V5');

            //read the response
            $curl = new Curl();
            $curl->get($url);
            $reader = new Reader();
            $reader->elementMap = [
                $prefix.'campaign_summary_response' => 'Sabre\Xml\Element\KeyValue',
                $prefix.'campaigns' => 'Sabre\Xml\Element\KeyValue',
                $prefix.'campaign_summary' => 'Sabre\Xml\Element\KeyValue'
            ];

            try
            {
                $reader->xml($curl->response);
                $response = $reader->parse();

                $campaigns = $response['value'][$prefix.'campaigns'];

                if(count($campaigns) > 0)
                {
                    //this means the response has affiliate data
                    foreach ($campaigns as $value)
                    {
                        $excludedRevenue = $excludedRevenue + doubleval($value[$prefix.'revenue']);
                        Log::info('campaign_revenue_deduction: '.doubleval($value[$prefix.'revenue']));
                    }
                }
            }
            catch(ErrorException $e)
            {
                Log::info($e->getMessage());
                Log::info($e->getCode());
            }
        }
        */

        //$affiliateReport->lead_count = 0;
        //$affiliateReport->revenue = $revenue - $excludedRevenue;
        //$affiliateReport->save();
    }

    private function processThirdPartyCPAWALLWithCD($baseURL, $campaignID, $affiliateID, $revenueTrackerID)
    {
        $url = $baseURL.'&start_date='.$this->dateFromStr.'&end_date='.$this->dateToStr.'&sub_affiliate='.$revenueTrackerID;
        $urlCD = $baseURL.'&start_date='.$this->dateFromStr.'&end_date='.$this->dateToStr.'&sub_affiliate=CD'.$revenueTrackerID;
        //Log::info($url);
        //Log::info($urlCD);

        $prefix = config('constants.CAKE_SABRE_AFFILIATES_PREFIX');

        $reader = new Reader();
        $reader->elementMap = [
            $prefix.'campaign_summary_response' => 'Sabre\Xml\Element\KeyValue',
            $prefix.'summary' => 'Sabre\Xml\Element\KeyValue',
        ];

        $affiliateReport = AffiliateReport::firstOrNew([
            'affiliate_id' => $affiliateID,
            'revenue_tracker_id' => $revenueTrackerID,
            'campaign_id' => $campaignID,
            'created_at' => $this->dateFromStr,
        ]);

        $revenue = 0.0;
        $revenueWithCD = 0.0;

        try {
            $curl = new Curl();
            $curl->get($url);

            $reader->xml($curl->response);
            $response = $reader->parse();

            //get the content of the campaign summary
            if (isset($response['value'][$prefix.'summary'])) {
                $campaignSummaryRevenue = $response['value'][$prefix.'summary'];

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
            $curl = new Curl();
            $curl->get($urlCD);

            $reader->xml($curl->response);
            $response = $reader->parse();

            //get the content of the campaign summary
            if (isset($response['value'][$prefix.'summary'])) {
                $campaignSummaryRevenue = $response['value'][$prefix.'summary'];

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

    private function processThirdPartyCPAWALLCD($baseURL, $campaignID, $affiliateID, $revenueTrackerID)
    {
        $urlCD = $baseURL.'&start_date='.$this->dateFromStr.'&end_date='.$this->dateToStr.'&sub_affiliate=CD'.$revenueTrackerID;
        Log::info($urlCD);

        $prefix = config('constants.CAKE_SABRE_AFFILIATES_PREFIX');

        $reader = new Reader();
        $reader->elementMap = [
            $prefix.'campaign_summary_response' => 'Sabre\Xml\Element\KeyValue',
            $prefix.'summary' => 'Sabre\Xml\Element\KeyValue',
        ];

        $affiliateReport = AffiliateReport::firstOrNew([
            'affiliate_id' => $affiliateID,
            'revenue_tracker_id' => $revenueTrackerID,
            'campaign_id' => $campaignID,
            'created_at' => $this->dateFromStr,
        ]);

        $revenueWithCD = 0.0;

        try {
            $curl = new Curl();
            $curl->get($urlCD);

            $reader->xml($curl->response);
            $response = $reader->parse();

            //get the content of the campaign summary
            if (isset($response['value'][$prefix.'summary'])) {
                $campaignSummaryRevenue = $response['value'][$prefix.'summary'];

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

    /**
     * Handle a job failure.
     */
    public function failed(): void
    {
        //job failed
        Log::info('AffiliateReportsV2 Failed! - '.Carbon::now()->toDateTimeString());
    }
}
