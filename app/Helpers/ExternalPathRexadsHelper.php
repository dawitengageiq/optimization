<?php
/**
 * Created by PhpStorm.
 * User: magbanua-ariel
 * Date: 11/29/2016
 * Time: 4:46 PM
 */

namespace App\Helpers;

use App\AffiliateReport;
use App\AffiliateRevenueTracker;
use App\Campaign;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Log;

class ExternalPathRexadsHelper
{
    private $baseURL = 'http://rexadz.com/api/?key=UGMzdVBlU3FkMWhBOUtiR2pwUjFCUT09';

    private $startDate;

    private $endDate;

    private $parser;

    private $externalPathRexAdsCampaignID;

    /**
     * ExternalPathRexadsHelper constructor
     */
    public function __construct(int $externalPathRexAdsCampaignID, Carbon $dateFrom, Carbon $dateTo, JSONParser $parser)
    {
        $this->startDate = $dateFrom;
        $this->endDate = $dateTo;
        $this->parser = $parser;
        $this->externalPathRexAdsCampaignID = $externalPathRexAdsCampaignID;

        Log::info("rexads start_date: $this->startDate");
        Log::info("rexads end_date: $this->endDate");
    }

    public function getReports()
    {
        $campaign = Campaign::find($this->externalPathRexAdsCampaignID);

        if ($this->externalPathRexAdsCampaignID > 0 && $campaign->exists()) {
            //get the report on each day within the date range
            while ($this->startDate->diffInDays($this->endDate, false) >= 0) {
                $dateStr = $this->startDate->month.'/'.$this->startDate->day.'/'.$this->startDate->year;
                $requestURL = $this->baseURL.'&startdate='.$dateStr.'&enddate='.$dateStr;
                Log::info("RexAds URL: $requestURL");

                $response = $this->parser->getXMLResponseObject($requestURL);

                if ($this->parser->getErrorCode() != 200) {
                    Log::info('no response or server error!');
                    $this->startDate->addDay();

                    continue;
                }

                if ($response->websites == null || ! isset($response->websites)) {
                    Log::info('There were no websites data!');
                    $this->startDate->addDay();

                    continue;
                } else {
                    //$publishers = $response->websites->subid1->subid;
                    $publisherRevenue = [];

                    //get all publisher revenue per website
                    foreach ($response->websites as $website) {
                        $publishers = $website->subid1->subid;
                        //var_dump($publishers);
                        $publishersArray = $this->xmlToArray($publishers);
                        //var_dump($publishersArray);

                        for ($i = 0; $i < count($publishers); $i++) {
                            //get the revenue tracker and removed the CD prefix
                            $revenueTrackerID = str_replace('CD', '', $publishers[$i]['id']);

                            //remove the dollar sign
                            $revenue = ltrim($publishersArray[$i], '$');

                            if (! isset($publisherRevenue[$revenueTrackerID])) {
                                $publisherRevenue[$revenueTrackerID] = floatval($revenue);
                            } else {
                                $publisherRevenue[$revenueTrackerID] = floatval($publisherRevenue[$revenueTrackerID]) + floatval($revenue);
                            }
                        }
                    }

                    foreach ($publisherRevenue as $revenueTrackerID => $revenue) {
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

    /**
     * function xmlToArray
     *
     * This function is part of the PHP manual.
     *
     * The PHP manual text and comments are covered by the Creative Commons
     * Attribution 3.0 License, copyright (c) the PHP Documentation Group
     *
     * @author  k dot antczak at livedata dot pl
     *
     * @date    2011-04-22 06:08 UTC
     *
     * @link    http://www.php.net/manual/en/ref.simplexml.php#103617
     *
     * @license http://www.php.net/license/index.php#doc-lic
     * @license http://creativecommons.org/licenses/by/3.0/
     * @license CC-BY-3.0 <http://spdx.org/licenses/CC-BY-3.0>
     */
    public function xmlToArray($xmlObject, array $out = []): array
    {
        foreach ((array) $xmlObject as $index => $node) {
            $out[$index] = (is_object($node)) ? $this->xmlToArray($node) : $node;
        }

        return $out;
    }
}
