<?php
/**
 * Created by PhpStorm.
 * User: magbanua-ariel
 * Date: 11/22/2016
 * Time: 5:21 PM
 */

namespace App\Helpers;

use App\AffiliateReport;
use App\AffiliateRevenueTracker;
use App\Campaign;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Log;

class CPAWallSurveySpotHelper
{
    //sample url
    //http://ssi.hasoffers.com/stats/lead_report.json?api_key=AFFDSrSYfm1nU0sA4YDSESqy85VUSz&start_date=2015-06-23&end_date=2015-06-23&filter[Stat.offer_id]=80

    private $dateFrom;

    private $dateTo;

    private $CPAWALLSurveySpotCampaignID;

    private $parser;

    /**
     * CPAWallSurveySpotHelper constructor
     */
    public function __construct(int $CPAWALLSurveySpotCampaignID, Carbon $dateFrom, Carbon $dateTo, JSONParser $parser)
    {
        $this->CPAWALLSurveySpotCampaignID = $CPAWALLSurveySpotCampaignID;
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
        $this->parser = $parser;
    }

    public function processConversions()
    {
        //get the campaign ID of the CPA WALL Survey Spot configured in ENV it should match the live campaign in Lead Reactor.
        //$campaignID = env('CPA_WALL_SURVEY_SPOT',0);
        $campaign = Campaign::find($this->CPAWALLSurveySpotCampaignID);

        if ($this->CPAWALLSurveySpotCampaignID > 0 && $campaign->exists()) {
            $date = $this->dateFrom;

            while ($date->diffInDays($this->dateTo, false) >= 0) {
                //get the revenue count for survey spot
                $url = 'http://ssi.hasoffers.com/stats/lead_report.json?api_key=AFFDSrSYfm1nU0sA4YDSESqy85VUSz&start_date='.$date->toDateString().'&end_date='.$date->toDateString().'&filter[Stat.offer_id]=80';
                Log::info('survey spot url: '.$url);

                $dataArray = $this->parser->getDataArrayJSON($url);
                $surveySpotStats = $dataArray['data'];

                if ($this->parser->getErrorCode() != 200) {
                    Log::info('no response or server error!');

                    continue;
                }

                $convertedStats = [];

                foreach ($surveySpotStats as $data) {
                    $payout = str_replace('$', '', $data['payout']);

                    if (isset($convertedStats[$data['sub_id']])) {
                        $convertedStats[$data['sub_id']]->revenue += $payout;
                    } else {
                        //get the revenue tracker and removed the CD prefix
                        $revenueTrackerID = str_replace('CD', '', $data['sub_id']);
                        //get the affiliate for the particular revenue tracker
                        $tracker = AffiliateRevenueTracker::where('revenue_tracker_id', '=', $revenueTrackerID)->first();

                        if ($tracker != null) {
                            $affiliateReport = AffiliateReport::firstOrNew([
                                'affiliate_id' => $tracker->affiliate_id,
                                'revenue_tracker_id' => $revenueTrackerID,
                                'campaign_id' => $campaign->id,
                                'created_at' => $this->dateFrom->toDateString(),
                            ]);

                            $affiliateReport->lead_count = 0;
                            $affiliateReport->revenue = $payout;

                            $convertedStats[$data['sub_id']] = $affiliateReport;
                        }
                    }
                }

                //save one by one
                foreach ($convertedStats as $stat) {
                    try {
                        $stat->save();
                        Log::info($stat->revenue_tracker_id.' success!');
                    } catch (QueryException $e) {
                        Log::info($e->getMessage());
                        Log::info('affiliate_id: '.$stat->affiliate_id);
                        Log::info('revenue_tracker_id: '.$stat->revenue_tracker_id);
                    }
                }

                $date->addDay();
            }
        }
    }
}
