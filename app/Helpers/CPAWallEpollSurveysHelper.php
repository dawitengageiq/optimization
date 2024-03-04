<?php
/**
 * Created by PhpStorm.
 * User: magbanua-ariel
 * Date: 11/30/2016
 * Time: 4:01 PM
 */

namespace App\Helpers;

use App\AffiliateReport;
use App\AffiliateRevenueTracker;
use App\Campaign;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Log;

class CPAWallEpollSurveysHelper
{
    private $dateFrom;

    private $dateTo;

    private $cpaWallEpollSurveysCampaignID;

    private $parser;

    private $baseURL;

    public function __construct(int $cpaWallEpollSurveysCampaignID, Carbon $dateFrom, Carbon $dateTo, JSONParser $parser)
    {
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
        $this->cpaWallEpollSurveysCampaignID = $cpaWallEpollSurveysCampaignID;
        $this->parser = $parser;

        $this->baseURL = config('constants.HAS_OFFERS_EPOLL_SURVEYS_BASE_URL');

        Log::info("CPA Wall Epoll start_date: $this->dateFrom");
        Log::info("CPA Wall Epoll end_date: $this->dateTo");
    }

    /**
     * Function for getting stats
     */
    public function getStats()
    {
        $campaign = Campaign::find($this->cpaWallEpollSurveysCampaignID);

        if ($this->cpaWallEpollSurveysCampaignID > 0 && $campaign->exists()) {
            $refDate = Carbon::parse($this->dateFrom->toDateTimeString());

            $proceed = false;

            do {
                $url = $this->baseURL.'&data_start='.$refDate->toDateString().'&data_end='.$refDate->toDateString();
                Log::info("CPA WALL Epoll URL: $url");
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
                    }
                }

                $proceed = false;
                $refDate->addDay();

                $diffIndays = $refDate->diffInDays($this->dateTo, false);

                if ($diffIndays >= 0) {
                    $proceed = true;
                }

            } while ($proceed);

            Log::info('EPollSurveys is done processing!');
        }
    }
}
