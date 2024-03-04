<?php
/**
 * Created by PhpStorm.
 * User: magbanua-ariel
 * Date: 12/1/2016
 * Time: 5:56 PM
 */

namespace App\Helpers;

use App\AffiliateReport;
use App\AffiliateRevenueTracker;
use App\Campaign;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Log;

class CPAWallJobToShopHelper
{
    private $cpaWallJobToShopCampaignID;

    private $dateFrom;

    private $dateTo;

    private $baseURL;

    private $parser;

    /**
     * CPAWallJobToShopHelper constructor
     */
    public function __construct(int $cpaWallJobToShopCampaignID, Carbon $dateFrom, Carbon $dateTo, JSONParser $parser)
    {
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
        $this->parser = $parser;
        $this->cpaWallJobToShopCampaignID = $cpaWallJobToShopCampaignID;
        $this->baseURL = config('constants.HAS_OFFERS_JOB_TO_SHOP_BASE_URL');
    }

    /**
     * Function for getting stats
     */
    public function getStats()
    {
        $campaign = Campaign::find($this->cpaWallJobToShopCampaignID);

        if ($this->cpaWallJobToShopCampaignID > 0 && $campaign->exists()) {
            $refDate = $this->dateFrom;
            $proceed = false;

            do {

                $url = $this->baseURL.'&data_start='.$refDate->toDateString().'&data_end='.$refDate->toDateString();
                Log::info("JobToShop URL: $url");

                $response = $this->parser->getDataArrayJSON($url);

                if ($this->parser->getErrorCode() != 200) {
                    Log::info('There is problem with the server!');

                    continue;
                }

                //check if there is response
                if (isset($response['response'])) {
                    $responseArray = $response['response'];

                    //log the errors
                    if (isset($responseArray['errorMessage']) &&
                        $responseArray['errorMessage'] != null &&
                        $responseArray['errorMessage'] != '') {
                        Log::info($responseArray['errorMessage']);
                    } else {
                        //check if there is response data
                        if (isset($responseArray['data'])) {
                            $responseData = $responseArray['data']['data'];

                            foreach ($responseData as $data) {
                                $stat = $data['Stat'];

                                $revenueTrackerID = str_replace('CD', '', $stat['affiliate_info1']);
                                $tracker = AffiliateRevenueTracker::where('revenue_tracker_id', '=', $revenueTrackerID)->first();

                                if ($tracker != null) {
                                    $affiliateReport = AffiliateReport::firstOrNew([
                                        'affiliate_id' => $tracker->affiliate_id,
                                        'revenue_tracker_id' => $revenueTrackerID,
                                        'campaign_id' => $campaign->id,
                                        'created_at' => $this->dateFrom->toDateString(),
                                    ]);

                                    $affiliateReport->lead_count = 0;
                                    $affiliateReport->revenue = $stat['payout'];

                                    try {
                                        $affiliateReport->save();
                                        Log::info("$affiliateReport->revenue_tracker_id success!");
                                    } catch (QueryException $e) {
                                        Log::info($e->getMessage());
                                        Log::info("affiliate_id: $affiliateReport->affiliate_id");
                                        Log::info("revenue_tracker_id: $affiliateReport->revenue_tracker_id");
                                    }
                                }

                                /*
                                $report = Report::firstOrNew([
                                    'created_at' => $stat['date'],
                                    'campaign_id' => $jobToShop->id,
                                    'campaign' => $jobToShop->campaignName.'('.$jobToShop->id.')',
                                    'publisher' => $stat['affiliate_info1'],
                                    //'revenue' => $payout
                                ]);

                                $report->revenue = $stat['payout'];
                                $report->save();
                                */
                            }
                        }
                    }
                }

                $proceed = false;
                $refDate->addDay();

                $diffIndays = $refDate->diffInDays($this->dateTo, false);

                if ($diffIndays >= 0) {
                    $proceed = true;
                }

            } while ($proceed);

            Log::info('JobToShop is done processing!');
        }
    }
}
