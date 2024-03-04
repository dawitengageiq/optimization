<?php

namespace App\Helpers;

use App\AffiliateReport;
use App\AffiliateRevenueTracker;
use App\Campaign;
use ErrorException;
use Log;
use PDOException;
use Sabre\Xml\Reader;

class CakeSubIDSummaryHelper
{
    private $dateFrom;

    private $dateTo;

    private $cakeAffiliateID;

    private $campaignID;

    private $parser;

    private $baseURL;

    private $prefix;

    public function __construct($cakeAffiliateID, $campaignID, $dateFrom, $dateTo, $prefix, JSONParser $parser)
    {
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
        $this->cakeAffiliateID = $cakeAffiliateID;
        $this->campaignID = $campaignID;
        $this->parser = $parser;
        $this->prefix = $prefix;
        $this->baseURL = config('constants.CAKE_SUBID_SUMMARY_BASE_URL_V1');

        Log::info("SubID Summary start_date: $this->dateFrom");
        Log::info("SubID Summary end_date: $this->dateTo");
    }

    public function getStats()
    {
        $campaign = Campaign::find($this->campaignID);

        if ($this->cakeAffiliateID > 0 && ($this->campaignID > 0 && $campaign->exists())) {
            $url = $this->baseURL.'&start_date='.$this->dateFrom.'&end_date='.$this->dateTo.'&source_affiliate_id='.$this->cakeAffiliateID;
            Log::info("SubID Summary URL: $url");

            $reader = new Reader();

            $reader->elementMap = [
                $this->prefix.'sub_id_summary_response' => 'Sabre\Xml\Element\KeyValue',
                $this->prefix.'sub_id_summary' => 'Sabre\Xml\Element\KeyValue',
            ];

            $subIDS = null;
            $curlResponse = $this->parser->getResponse($url);

            $reader->xml($curlResponse);
            $response = $reader->parse();

            if (isset($response['value'][$this->prefix.'sub_ids'])) {
                $subIDS = $response['value'][$this->prefix.'sub_ids'];

                foreach ($subIDS as $subID) {
                    $subIDSummary = $subID['value'];

                    //the subID is the revenue tracker.
                    $subID = $subIDSummary[$this->prefix.'sub_id_name'];

                    //revenue
                    $revenue = $subIDSummary[$this->prefix.'revenue'];

                    Log::info("SUB_ID: $subID");
                    Log::info("Revenue: $revenue");

                    try {
                        //get the revenuetracker record
                        $revenueTracker = AffiliateRevenueTracker::select('affiliate_id', 'revenue_tracker_id')->where('revenue_tracker_id', '=', $subID)->first();

                        $affiliateReport = AffiliateReport::firstOrNew([
                            'affiliate_id' => $revenueTracker->affiliate_id,
                            'revenue_tracker_id' => $subID,
                            'campaign_id' => $this->campaignID,
                            'created_at' => $this->dateFrom,
                        ]);

                        $affiliateReport->revenue = $revenue;
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
    }
}
