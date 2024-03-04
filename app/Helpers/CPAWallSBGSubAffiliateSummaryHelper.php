<?php
/**
 * Created by PhpStorm.
 * User: Ariel Magbanua
 * Date: 1/26/2018
 * Time: 1:44 PM
 */

namespace App\Helpers;

use App\AffiliateReport;
use App\AffiliateRevenueTracker;
use App\Campaign;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Log;

class CPAWallSBGSubAffiliateSummaryHelper
{
    private $cpaWallSBGCampaignID;

    private $dateFrom;

    private $dateTo;

    private $parser;

    private $baseURL;

    /**
     * CPAWallSBGSubAffiliateSummaryHelper constructor.
     *
     * CPAWallSBGSubAffiliateSummaryHelper constructor.
     */
    public function __construct($cpaWallSBGCampaignID, Carbon $dateFrom, Carbon $dateTo, JSONParser $parser)
    {
        $this->cpaWallSBGCampaignID = $cpaWallSBGCampaignID;
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
        $this->parser = $parser;

        $this->baseURL = config('constants.SBG_CAKE_API_SUB_AFFILIATE_SUMMARY_BASE_URL_V3');

        Log::info("CPA Wall SBG start_date: $this->dateFrom");
        Log::info("CPA Wall SBG end_date: $this->dateTo");
    }

    /**
     * Function for getting stats
     */
    public function getStats()
    {
        $campaign = Campaign::find($this->cpaWallSBGCampaignID);

        if ($this->cpaWallSBGCampaignID > 0 && $campaign->exists()) {
            $refDate = Carbon::parse($this->dateFrom->toDateTimeString());
            $proceed = false;

            do {
                // Create new instance date for end date
                $endDate = Carbon::parse($refDate->toDateTimeString());
                $endDate->addDay();

                $url = $this->baseURL.'&start_date='.$refDate->toDateString().'&end_date='.$endDate->toDateString();
                Log::info("CPA WALL SBG Sub Affiliate Summary URL: $url");

                $response = $this->parser->getXMLResponseObject($url);
                if ($this->parser->getErrorCode() != 200) {
                    Log::info('There is problem with the server!');

                    continue;
                }

                if (isset($response)) {
                    $subAffiliates = $response->sub_affiliates->sub_affiliate;
                    foreach ($subAffiliates as $subAffiliate) {
                        $revenueTrackerID = str_replace('CD', '', $subAffiliate->sub_id);
                        $tracker = AffiliateRevenueTracker::where('revenue_tracker_id', '=', $revenueTrackerID)->first();

                        if ($tracker != null) {
                            $affiliateReport = AffiliateReport::firstOrNew([
                                'affiliate_id' => $tracker->affiliate_id,
                                'revenue_tracker_id' => $revenueTrackerID,
                                'campaign_id' => $campaign->id,
                                'created_at' => $refDate->toDateString(),
                            ]);

                            $affiliateReport->lead_count = 0;
                            $affiliateReport->revenue = $subAffiliate->revenue;

                            try {
                                Log::info("affiliate_id: $affiliateReport->affiliate_id");
                                Log::info("revenue_tracker_id: $affiliateReport->revenue_tracker_id");
                                Log::info("revenue: $affiliateReport->revenue");

                                $affiliateReport->save();
                                Log::info("$affiliateReport->revenue_tracker_id success!");
                            } catch (QueryException $e) {
                                Log::info($e->getMessage());
                                Log::info($e->getCode());
                            }
                        }
                    }
                }

                $proceed = false;
                $refDate->addDay();
                $diffInDays = $refDate->diffInDays($this->dateTo, false);

                if ($diffInDays >= 0) {
                    $proceed = true;
                }
            } while ($proceed);
        }
    }
}
