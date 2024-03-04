<?php

namespace App\Helpers;

use App\AffiliateReport;
use App\AffiliateRevenueTracker;
use App\Campaign;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\TransferStats;
use Illuminate\Database\QueryException;
use Log;

class ExternalPathIfficientHelper
{
    private $baseURL = 'https://api.ifficient.com/inquiry/pubrevenue';

    private $startDate;

    private $endDate;

    private $externalPathIfficientCampaignID;

    private $userName = 'engageiq';

    private $password = 'engage26#2';

    /**
     * Constructor
     */
    public function __construct($externalPathIfficientCampaignID, Carbon $dateFrom, Carbon $dateTo)
    {
        $this->startDate = $dateFrom;
        $this->endDate = $dateTo;
        $this->externalPathIfficientCampaignID = $externalPathIfficientCampaignID;

        Log::info("Ifficient start_date: $this->startDate");
        Log::info("Ifficient end_date: $this->endDate");
    }

    public function getReports()
    {
        $campaign = Campaign::find($this->externalPathIfficientCampaignID);

        if ($this->externalPathIfficientCampaignID > 0 && $campaign->exists()) {
            //get the report on each day within the date range
            while ($this->startDate->diffInDays($this->endDate, false) >= 0) {
                $dateStr = $this->startDate->month.'/'.$this->startDate->day.'/'.$this->startDate->year;
                $callCounter = 0;
                $statusCode = -1;

                $client = new Client();
                $response = null;

                do {

                    try {
                        $response = $client->request('GET', $this->baseURL, [
                            'http_errors' => false,
                            'auth' => [$this->userName, $this->password],
                            'query' => [
                                'sourceids' => 'all',
                                'subids' => 'all',
                                'startdate' => $dateStr,
                                'enddate' => $dateStr,
                            ],
                            'on_stats' => function (TransferStats $stats) {
                                Log::info('Ifficient URL: '.$stats->getEffectiveUri());
                            },
                        ]);

                        //get the status code
                        $statusCode = $response->getStatusCode();
                    } catch (ConnectException $e) {
                        Log::info('Ifficient API Error!');
                        Log::info($e->getMessage());

                        //this means the URL is clearly wrong cut the loop here immediately
                        break;
                    }

                    if ($statusCode != 200) {
                        $callCounter++;

                        //a bit of delay since server just responded an error
                        sleep(10);
                    }

                    //stop the process when budget breached
                    if ($callCounter == 10) {
                        Log::info('Ifficient API call limit reached!');
                        break;
                    }

                } while ($statusCode != 200);

                Log::info("Create Ticket HTTP status code: $statusCode");

                if ($statusCode == 200 && $response != null) {
                    $responseBody = $response->getBody()->getContents();
                    $responseBodyArray = \GuzzleHttp\json_decode($responseBody);

                    //extract the response content
                    $trackerRevenueArray = [];

                    foreach ($responseBodyArray->Sources as $source) {
                        $subIDs = $source->Subids;

                        foreach ($subIDs as $subID) {
                            //remove the CD
                            $revenueTrackerID = str_replace('CD', '', $subID->Sub);
                            //remove the dollar sign
                            $revenue = ltrim($subID->Revenue, '$');

                            Log::info("revenue_tracker_id: $revenueTrackerID");
                            Log::info("revenue: $revenue");

                            if (! isset($trackerRevenueArray[$revenueTrackerID])) {
                                $trackerRevenueArray[$revenueTrackerID] = floatval($revenue);
                            } else {
                                $trackerRevenueArray[$revenueTrackerID] = floatval($trackerRevenueArray[$revenueTrackerID]) + floatval($revenue);
                            }
                        }
                    }

                    foreach ($trackerRevenueArray as $revenueTrackerID => $revenue) {
                        $tracker = AffiliateRevenueTracker::where('revenue_tracker_id', '=', $revenueTrackerID)->first();

                        if ($tracker != null) {
                            $affiliateReport = AffiliateReport::firstOrNew([
                                'affiliate_id' => $tracker->affiliate_id,
                                'revenue_tracker_id' => $revenueTrackerID,
                                'campaign_id' => $campaign->id,
                                'created_at' => $this->startDate->toDateString(),
                            ]);

                            $affiliateReport->lead_count = 0;
                            $affiliateReport->revenue = $revenue;

                            try {
                                $affiliateReport->save();
                                Log::info("$affiliateReport->revenue_tracker_id success!");
                            } catch (QueryException $e) {
                                Log::info($e->getMessage());
                                Log::info('affiliate_id: '.$affiliateReport->affiliate_id);
                                Log::info('revenue_tracker_id: '.$affiliateReport->revenue_tracker_id);
                            }
                        }
                    }
                }

                $this->startDate->addDay();
            }
        }
    }
}
