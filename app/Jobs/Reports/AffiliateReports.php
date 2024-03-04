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
use Sabre\Xml\Reader;

class AffiliateReports extends Job implements ShouldQueue
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
        $startLog = Carbon::now();
        Log::info('Affiliate Revenue Tracker Start Time: '.$startLog->toDateTimeString());
        Log::info('Generating Internal Affiliate Reports...');

        //1 = internal type
        $params['type'] = 1;

        //$revenueTrackers = AffiliateRevenueTracker::withAffiliates($params)->groupBy('affiliate_id','campaign_id')->get();
        $revenueTrackers = DB::table('affiliate_revenue_trackers')
            ->select('affiliate_id', 'campaign_id', 'revenue_tracker_id')
            ->join('affiliates', 'affiliate_revenue_trackers.affiliate_id', '=', 'affiliates.id')
            ->where('affiliates.type', '=', 1)
            ->groupBy('affiliate_revenue_trackers.affiliate_id', 'affiliate_revenue_trackers.revenue_tracker_id')
            ->get();

        foreach ($revenueTrackers as $tracker) {
            //build the url
            $campaignSummaryBaseURL = config('constants.CAKE_API_CAMPAIGN_SUMMARY_BASE_URL_V5');
            $campaignSummaryURL = $campaignSummaryBaseURL.'&campaign_id='.$tracker->campaign_id.'&source_affiliate_id='.$tracker->affiliate_id.'&start_date='.$this->dateFromStr.'&end_date='.$this->dateToStr;

            //get cake data
            $prefix = config('constants.CAKE_SABRE_PREFIX_V5');

            $reader = new Reader();
            $reader->elementMap = [
                $prefix.'campaign_summary_response' => 'Sabre\Xml\Element\KeyValue',
                $prefix.'campaign_summary' => 'Sabre\Xml\Element\KeyValue',
                $prefix.'campaigns' => 'Sabre\Xml\Element\KeyValue',
            ];

            $curl = new Curl();
            $curl->get($campaignSummaryURL);

            try {
                $reader->xml($curl->response);
                $response = $reader->parse();

                //get the content of the campaign summary
                $campaignSummary = $response['value'][$prefix.'campaigns'][$prefix.'campaign_summary'];

                //get the clicks and payout
                $clicks = $campaignSummary[$prefix.'clicks'];
                $payout = $campaignSummary[$prefix.'cost'];

                $revenueTrackerCakeStat = RevenueTrackerCakeStatistic::firstOrNew([
                    'affiliate_id' => $tracker->affiliate_id,
                    'revenue_tracker_id' => $tracker->revenue_tracker_id,
                    'cake_campaign_id' => $tracker->campaign_id,
                    'type' => 1,
                    'created_at' => $this->dateFromStr,
                ]);

                $revenueTrackerCakeStat->clicks = $clicks;
                $revenueTrackerCakeStat->payout = $payout;
                $revenueTrackerCakeStat->created_at = $this->dateFromStr;
                $revenueTrackerCakeStat->save();

                Log::info('affiliate_id: '.$tracker->affiliate_id);
                Log::info('cake_campaign_id: '.$tracker->campaign_id);
                Log::info('clicks: '.$clicks);
                Log::info('payout: '.$payout);
                Log::info('created_at: '.$this->dateFromStr);
            } catch (ErrorException $e) {
                Log::info('Error ErrorException: '.$e->getMessage());
                Log::info('affiliate_id: '.$tracker->affiliate_id);
                Log::info('campaign_id: '.$tracker->campaign_id);
                Log::info('created_at: '.$this->dateFromStr);

                //this means something wrong with this tracker set everything to zero
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
            }

            //generate the leads reports
            $this->leadReports($tracker);

            //generate CPA WALL reports
            $this->cpaWallReports($tracker);

        }

        $endLog = Carbon::now();
        Log::info('Affiliate Revenue Tracker End Time: '.$endLog->toDateTimeString());

        Log::info('External Publisher Start Time: '.$endLog->toDateTimeString());
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

    private function others()
    {
        $parser = new JSONParser();

        $cpaWallSurveySpotCampaignID = env('CPA_WALL_SURVEY_SPOT_CAMPAIGN_ID', 0);
        Log::info('---CPA WALL SURVEY SPOT---');
        Log::info('campaign_id: '.$cpaWallSurveySpotCampaignID);
        $cpaWallSurveySpotHelper = new CPAWallSurveySpotHelper($cpaWallSurveySpotCampaignID, Carbon::parse($this->dateFromStr), Carbon::parse($this->dateToStr), $parser);
        $cpaWallSurveySpotHelper->processConversions();

        /*
        $externalPathPermissionDataCampaignID = env('EXTERNAL_PATH_PERMISSION_DATA_CAMPAIGN_ID',0);
        Log::info('---EXTERNAL PATH PERMISSION DATA---');
        Log::info('campaign_id: '.$externalPathPermissionDataCampaignID);
        $externalPathPermissionDataHelper = new ExternalPathPermissionDataHelper($externalPathPermissionDataCampaignID, $this->dateFromStr, $this->dateToStr, 'EGI', 'XML');
        $externalPathPermissionDataHelper->getReports();
        */

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
    }

    private function leadReports($tracker)
    {
        $leadReports = Lead::revenueTrackerAffiliateReport([
            'revenue_tracker_id' => $tracker->revenue_tracker_id,
            'created_at' => $this->dateFromStr,
        ])->get();

        Log::info('Lead reports count: '.count($leadReports));

        foreach ($leadReports as $report) {
            $affiliateReport = AffiliateReport::firstOrNew([
                'affiliate_id' => $tracker->affiliate_id,
                'revenue_tracker_id' => $report->affiliate_id,
                'campaign_id' => $report->campaign_id,
                'created_at' => $this->dateFromStr,
            ]);

            $affiliateReport->lead_count = $report->lead_count;
            $affiliateReport->revenue = $report->revenue;
            $affiliateReport->save();
        }
    }

    private function cpaWallReports($tracker)
    {
        //get the EngageIQ CPA WALL from ENV file
        $cpaWALLEngageIQCampaignID = env('CPA_WALL_ENGAGEIQ_CAMPAIGN_ID', 0);
        $campaign = Campaign::find($cpaWALLEngageIQCampaignID);
        $engageiqSourceAffiliateSummaryCakeAPIBaseURL = config('constants.CAKE_API_SOURCE_AFFILIATE_SUMMARY_BASE_URL_V3');

        Log::info('---CPA WALL---');
        Log::info('eiq_campaign_id: '.$cpaWALLEngageIQCampaignID);

        if ($cpaWALLEngageIQCampaignID > 0 && $campaign->exists()) {
            $this->processEngageIQCPAWALL($engageiqSourceAffiliateSummaryCakeAPIBaseURL, $tracker, $campaign);
        }

        //get the CPA WALL Affluent ID from env file
        $cpaWALLAffluentCampaignID = env('CPA_WALL_AFFLUENT_CAMPAIGN_ID', 0);
        $campaign = Campaign::find($cpaWALLAffluentCampaignID);
        $engageiqSourceAffiliateSummaryCakeAPIBaseURL = config('constants.AFFLUENT_CAKE_API_CAMPAIGN_SUMMARY_BASE_URL_V6');

        Log::info('---CPA WALL AFFLUENT---');
        Log::info('campaign_id: '.$cpaWALLAffluentCampaignID);

        if ($cpaWALLAffluentCampaignID > 0 && $campaign->exists()) {
            $this->processThirdPartyCPAWALLWithCD($engageiqSourceAffiliateSummaryCakeAPIBaseURL, $tracker, $campaign);
        }

        //get the CPA WALL SBG ID from env file
        $cpaWALLSBGCampaignID = env('CPA_WALL_SBG_CAMPAIGN_ID', 0);
        $campaign = Campaign::find($cpaWALLSBGCampaignID);
        $sbgCampaignSummaryCakeAPIBaseURL = config('constants.SBG_CAKE_API_CAMPAIGN_SUMMARY_BASE_URL_V6');

        Log::info('---CPA WALL SBG---');
        Log::info('campaign_id: '.$cpaWALLSBGCampaignID);

        if ($cpaWALLSBGCampaignID > 0 && $campaign->exists()) {
            $this->processThirdPartyCPAWALLCD($sbgCampaignSummaryCakeAPIBaseURL, $tracker, $campaign);
        }
    }

    private function processThirdPartyCPAWALLCD($baseURL, $tracker, $campaign)
    {
        $urlCD = $baseURL.'&start_date='.$this->dateFromStr.'&end_date='.$this->dateToStr.'&sub_affiliate=CD'.$tracker->revenue_tracker_id;
        Log::info($urlCD);

        $prefix = config('constants.CAKE_SABRE_AFFILIATES_PREFIX');

        $reader = new Reader();
        $reader->elementMap = [
            $prefix.'campaign_summary_response' => 'Sabre\Xml\Element\KeyValue',
            $prefix.'summary' => 'Sabre\Xml\Element\KeyValue',
        ];

        $affiliateReport = AffiliateReport::firstOrNew([
            'affiliate_id' => $tracker->affiliate_id,
            'revenue_tracker_id' => $tracker->revenue_tracker_id,
            'campaign_id' => $campaign->id,
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

            Log::info('engageiq_affiliate_id: '.$tracker->affiliate_id);
            Log::info('revenue_tracker_id: '.$tracker->revenue_tracker_id);
            Log::info('engageiq_campaign_id: '.$campaign->id);
            Log::info('lead_count: 0');
            Log::info('revenue(with CD): '.$revenueWithCD);
            Log::info('created_at: '.$this->dateFromStr);
        } catch (ErrorException $e) {

            $affiliateReport->lead_count = 0;
            $affiliateReport->revenue = 0;
            $affiliateReport->save();

            Log::info('Error ErrorException: '.$e->getMessage());
            Log::info('engageiq_affiliate_id: '.$tracker->affiliate_id);
            Log::info('revenue_tracker_id: '.$tracker->revenue_tracker_id);
            Log::info('engageiq_campaign_id: '.$campaign->id);
            Log::info('created_at: '.$this->dateFromStr);
        }

    }

    private function processThirdPartyCPAWALLWithCD($baseURL, $tracker, $campaign)
    {
        $url = $baseURL.'&start_date='.$this->dateFromStr.'&end_date='.$this->dateToStr.'&sub_affiliate='.$tracker->revenue_tracker_id;
        $urlCD = $baseURL.'&start_date='.$this->dateFromStr.'&end_date='.$this->dateToStr.'&sub_affiliate=CD'.$tracker->revenue_tracker_id;
        Log::info($url);
        Log::info($urlCD);

        $prefix = config('constants.CAKE_SABRE_AFFILIATES_PREFIX');

        $reader = new Reader();
        $reader->elementMap = [
            $prefix.'campaign_summary_response' => 'Sabre\Xml\Element\KeyValue',
            $prefix.'summary' => 'Sabre\Xml\Element\KeyValue',
        ];

        $affiliateReport = AffiliateReport::firstOrNew([
            'affiliate_id' => $tracker->affiliate_id,
            'revenue_tracker_id' => $tracker->revenue_tracker_id,
            'campaign_id' => $campaign->id,
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

            /*
            $affiliateReport->lead_count = 0;
            $affiliateReport->revenue = $revenue;
            $affiliateReport->save();
            */

            Log::info('engageiq_affiliate_id: '.$tracker->affiliate_id);
            Log::info('revenue_tracker_id: '.$tracker->revenue_tracker_id);
            Log::info('engageiq_campaign_id: '.$campaign->id);
            Log::info('lead_count: 0');
            Log::info('revenue(no CD): '.$revenue);
            Log::info('created_at: '.$this->dateFromStr);
        } catch (ErrorException $e) {
            /*
            $affiliateReport->lead_count = 0;
            $affiliateReport->revenue = 0;
            $affiliateReport->save();
            */
            Log::info('Error ErrorException: '.$e->getMessage());
            Log::info('engageiq_affiliate_id: '.$tracker->affiliate_id);
            Log::info('revenue_tracker_id: '.$tracker->revenue_tracker_id);
            Log::info('engageiq_campaign_id: '.$campaign->id);
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

            /*
            $affiliateReport->lead_count = 0;
            $affiliateReport->revenue = $revenue;
            $affiliateReport->save();
            */

            Log::info('engageiq_affiliate_id: '.$tracker->affiliate_id);
            Log::info('revenue_tracker_id: '.$tracker->revenue_tracker_id);
            Log::info('engageiq_campaign_id: '.$campaign->id);
            Log::info('lead_count: 0');
            Log::info('revenue(with CD): '.$revenueWithCD);
            Log::info('created_at: '.$this->dateFromStr);
        } catch (ErrorException $e) {
            /*
            $affiliateReport->lead_count = 0;
            $affiliateReport->revenue = 0;
            $affiliateReport->save();
            */
            Log::info('Error ErrorException: '.$e->getMessage());
            Log::info('engageiq_affiliate_id: '.$tracker->affiliate_id);
            Log::info('revenue_tracker_id: '.$tracker->revenue_tracker_id);
            Log::info('engageiq_campaign_id: '.$campaign->id);
            Log::info('created_at: '.$this->dateFromStr);
        }

        //save the affiliate report revenue
        $affiliateReport->lead_count = 0;
        $affiliateReport->revenue = $revenue + $revenueWithCD;
        $affiliateReport->save();
    }

    private function processEngageIQCPAWALL($baseURL, $tracker, $campaign)
    {
        //if the campaign id exist proceed generating through API if not then don't
        $url = $baseURL.'&start_date='.$this->dateFromStr.'&end_date='.$this->dateToStr.'&source_affiliate_id='.$tracker->revenue_tracker_id;

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
            'affiliate_id' => $tracker->affiliate_id,
            'revenue_tracker_id' => $tracker->revenue_tracker_id,
            'campaign_id' => $campaign->id,
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

            /*
            $affiliateReport->lead_count = 0;
            $affiliateReport->revenue = $revenue;
            $affiliateReport->save();
            */

            Log::info('engageiq_affiliate_id: '.$tracker->affiliate_id);
            Log::info('revenue_tracker_id: '.$tracker->revenue_tracker_id);
            Log::info('engageiq_campaign_id: '.$campaign->id);
            Log::info('lead_count: 0');
            Log::info('revenue: '.$revenue);
            Log::info('created_at: '.$this->dateFromStr);
        } catch (ErrorException $e) {
            Log::info('Error ErrorException: '.$e->getMessage());
            Log::info('engageiq_affiliate_id: '.$tracker->affiliate_id);
            Log::info('revenue_tracker_id: '.$tracker->revenue_tracker_id);
            Log::info('engageiq_campaign_id: '.$campaign->id);
            Log::info('created_at: '.$this->dateFromStr);
        }

        //get the revenues from the offers to be deducted for the EngageIQ CPA WALL
        $offersToBeDeducted = config('constants.CAKE_OFFERS_TO_BE_DEDUCTED');

        $excludedRevenue = 0.0;

        foreach ($offersToBeDeducted as $offer) {
            $url = config('constants.CAKE_API_CAMPAIGN_SUMMARY_BASE_URL_NO_OFFER_ID_V5').
                        '&start_date='.$this->dateFromStr.
                        '&end_date='.$this->dateToStr.
                        '&source_affiliate_id='.$tracker->revenue_tracker_id.
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
                $prefix.'campaign_summary' => 'Sabre\Xml\Element\KeyValue',
            ];

            try {
                $reader->xml($curl->response);
                $response = $reader->parse();

                $campaigns = $response['value'][$prefix.'campaigns'];

                if (count($campaigns) > 0) {
                    //this means the response has affiliate data
                    foreach ($campaigns as $value) {
                        $excludedRevenue = $excludedRevenue + floatval($value[$prefix.'revenue']);
                        Log::info('campaign_revenue_deduction: '.floatval($value[$prefix.'revenue']));
                    }
                }
            } catch (ErrorException $e) {
            }
        }

        $affiliateReport->lead_count = 0;
        $affiliateReport->revenue = $revenue - $excludedRevenue;
        $affiliateReport->save();
    }
}
