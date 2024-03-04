<?php
/**
 * Created by PhpStorm.
 * User: magbanua-ariel
 * Date: 11/23/2016
 * Time: 3:24 PM
 */

namespace App\Helpers;

use App\AffiliateReport;
use App\AffiliateRevenueTracker;
use App\Campaign;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Log;

class ExternalPathTiburonDataHelper
{
    /*
    http://reports.tmginteractive.com/NS/TMG_ReportAPI.asmx/GetReport?username=EngageIq&password=EngageIq@45&start_date_time=2015-07-24%2000:00:00&end_date_time=2015-07-24%2023:00:00
    */

    private $dateFrom = '';

    private $dateTo = '';

    private $dateOnlyFrom = '';

    private $dateOnlyTo = '';

    private $externalPathTiburonCampaignID;

    private $parser;

    private $requestURL = 'http://reports.tmginteractive.com/NS/TMG_ReportAPI.asmx/GetReport?username=EngageIq&password=EngageIq@45&start_date_time=date_time_start&end_date_time=date_time_end';

    /**
     * ExternalPathTiburonDataHelper constructor
     */
    public function __construct(int $externalPathTiburonCampaignID, string $dateFrom, string $dateTo, JSONParser $parser)
    {
        $this->externalPathTiburonCampaignID = $externalPathTiburonCampaignID;

        $this->dateOnlyFrom = $dateFrom;
        $this->dateOnlyTo = $dateTo;
        $this->dateFrom = urlencode($dateFrom.' 00:00:00');
        $this->dateTo = urlencode($dateTo.' 23:59:59');

        //build the request url
        $this->requestURL = str_replace('date_time_start', $this->dateFrom, $this->requestURL);
        $this->requestURL = str_replace('date_time_end', $this->dateTo, $this->requestURL);

        $this->parser = $parser;

        Log::info("TIBURON date range: $this->dateFrom - $this->dateTo");
        Log::info('TIBURON url: '.$this->requestURL);
    }

    public function getReports()
    {
        $campaign = Campaign::find($this->externalPathTiburonCampaignID);

        $reports = [];
        //$revenueTrackerRevenue = [];

        //get the session
        $responseObject = $this->parser->getXMLResponseObject($this->requestURL);

        if ($this->parser->getErrorCode() != 200) {
            Log::info('no response or server error!');
        }

        if (isset($responseObject) && $responseObject !== null || $responseObject) {
            $placementData = $this->xmlToArray($responseObject->placements);

            if ($placementData !== null || $placementData) {

                foreach ($placementData as $placements) {

                    $placements = $this->xmlToArray($placements);

                    foreach ($placements as $placement) {

                        foreach ($placement['transactions'] as $transactionData) {

                            foreach ($transactionData as $transaction) {

                                if (! isset($transaction->affId)) {
                                    continue;
                                }

                                $publisherID = (string) $transaction->affId;
                                //$revenueTrackerID = str_replace('CD','', $publisherID);
                                $revenue = floatval($transaction->revenue);
                                $date = Carbon::parse($transaction->transaction_date)->toDateString();

                                /*
                                if(!isset($revenueTrackerRevenue[$revenueTrackerID][$date]))
                                {
                                    $revenueTrackerRevenue[$revenueTrackerID][$date] = 0.00;
                                }

                                $revenueTrackerRevenue[$revenueTrackerID][$date] = $revenueTrackerRevenue[$revenueTrackerID][$date] + $revenue;
                                */

                                if (isset($reports[$publisherID.' '.$date])) {
                                    $rev = $reports[$publisherID.' '.$date]->revenue;
                                    $reports[$publisherID.' '.$date]->revenue = $rev + $revenue;
                                } else {
                                    //get the revenue tracker and removed the CD prefix
                                    $revenueTrackerID = str_replace('CD', '', $publisherID);
                                    //get the affiliate for the particular revenue tracker
                                    $tracker = AffiliateRevenueTracker::where('revenue_tracker_id', '=', $revenueTrackerID)->first();

                                    if ($tracker != null) {
                                        $reports[$publisherID.' '.$date] = AffiliateReport::firstOrNew([
                                            'affiliate_id' => $tracker->affiliate_id,
                                            'revenue_tracker_id' => $revenueTrackerID,
                                            'campaign_id' => $campaign->id,
                                            'created_at' => $date,
                                        ]);

                                        $reports[$publisherID.' '.$date]->lead_count = 0;
                                        $reports[$publisherID.' '.$date]->revenue = $revenue;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        /*
        foreach($revenueTrackerRevenue as $revTrackerID => $dates)
        {
            foreach($dates as $date => $value)
            {
                Log::info("$revTrackerID - $date = $value");

                $tracker = AffiliateRevenueTracker::where('revenue_tracker_id','=',$revTrackerID)->first();

                if($tracker!=null)
                {
                    $report =  AffiliateReport::firstOrNew([
                        'affiliate_id' => $tracker->affiliate_id,
                        'revenue_tracker_id' => $revTrackerID,
                        'campaign_id' => $campaign->id,
                        'created_at' => $date
                    ]);

                    $report->lead_count = 0;
                    $report->revenue = $value;
                    $report->save();
                }
                else
                {
                    Log::info("RevenueTrackerID ($revTrackerID) does not exist!");
                }

            }
        }
        */

        if (count($reports) > 0) {
            foreach ($reports as $report) {
                if ($report->revenue_tracker_id > 0) {
                    try {
                        $report->save();
                        Log::info("Tiburon: $report->revenue_tracker_id - $report->revenue - $report->created_at");
                    } catch (QueryException $e) {
                        Log::info($e->getMessage());
                        Log::info('affiliate_id: '.$report->affiliate_id);
                        Log::info('revenue_tracker_id: '.$report->revenue_tracker_id);
                    }
                }
            }
        }
    }

    /**
     *  function xmlToArray
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
