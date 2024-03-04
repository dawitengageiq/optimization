<?php

namespace App\Helpers\Repositories;

use Curl\Curl;
use Exception;
use Log;
use Sabre\Xml\Reader;

class AffiliateReportCurl implements AffiliateReportCurlInterface
{
    /**
     * Campaign Summary API function that will return campaigns data from cake.
     *
     * @return null
     *
     * @throws \Sabre\Xml\LibXMLException
     */
    public function campaignSummary($campaignSummaryBaseURL, $prefix, $subPrefix, $dateFromStr, $dateToStr)
    {
        $campaignSummaryURL = $campaignSummaryBaseURL.'&start_date='.$dateFromStr.'&end_date='.$dateToStr;

        $reader = new Reader();

        $reader->elementMap = [
            $prefix.'campaign_summary_response' => 'Sabre\Xml\Element\KeyValue',
            $prefix.'campaign_summary' => 'Sabre\Xml\Element\KeyValue',
            $prefix.'campaign' => 'Sabre\Xml\Element\KeyValue',
            $prefix.'source_affiliate' => 'Sabre\Xml\Element\KeyValue',
            $prefix.'source_affiliate_id' => 'Sabre\Xml\Element\KeyValue',
            $prefix.'source_affiliate_name' => 'Sabre\Xml\Element\KeyValue',
            $prefix.'site_offer' => 'Sabre\Xml\Element\KeyValue',
            $prefix.'site_offer_id' => 'Sabre\Xml\Element\KeyValue',
        ];

        Log::info($campaignSummaryURL);

        $campaigns = [];
        $callCounter = 0;

        do {

            $curl = new Curl();
            $curl->get($campaignSummaryURL);

            if ($curl->error) {
                Log::info('campaignSummary API Error!');
                Log::info($curl->error_message);
                $callCounter++;
            } else {
                $reader->xml($curl->response);
                $response = $reader->parse();

                //get the content of the campaign summary
                if (isset($response['value'][$prefix.'campaigns'])) {
                    $campaigns = $response['value'][$prefix.'campaigns'];
                }
            }

            //stop the process when budget breached
            if ($callCounter == 10) {
                Log::info('campaignSummary API call limit reached!');
                break;
            }

        } while ($curl->error);

        return $campaigns;
    }

    /**
     * Affiliate Summary API function for EngageIQ CPA WALL.
     *
     * @return null
     *
     * @throws \Sabre\Xml\LibXMLException
     */
    public function engageIQCPAWALLAffiliateSummary($affiliateSummaryBaseURL, $prefix, $revenueTrackerID, $dateFromStr, $dateToStr)
    {
        $url = $affiliateSummaryBaseURL.'&start_date='.$dateFromStr.'&end_date='.$dateToStr.'&source_affiliate_id='.$revenueTrackerID;

        $reader = new Reader();
        $reader->elementMap = [
            $prefix.'source_affiliate_summary_response' => 'Sabre\Xml\Element\KeyValue',
            $prefix.'source_affiliate_summary' => 'Sabre\Xml\Element\KeyValue',
            $prefix.'source_affiliates' => 'Sabre\Xml\Element\KeyValue',
        ];

        Log::info($url);

        $affiliateSummary = null;
        $callCounter = 0;

        do {

            $curl = new Curl();
            $curl->get($url);

            if ($curl->error) {
                Log::info('engageIQCPAWALLAffiliateSummary API Error!');
                Log::info($curl->error_message);
                $callCounter++;
            } else {
                $reader->xml($curl->response);
                $response = $reader->parse();

                //get the content of the campaign summary
                if (isset($response['value'][$prefix.'source_affiliates'][$prefix.'source_affiliate_summary'])) {
                    $affiliateSummary = $response['value'][$prefix.'source_affiliates'][$prefix.'source_affiliate_summary'];
                }
            }

            //stop the process when budget breached
            if ($callCounter == 10) {
                Log::info('engageIQCPAWALLAffiliateSummary API call limit reached!');
                break;
            }

        } while ($curl->error);

        return $affiliateSummary;
    }

    /**
     * Campaign Summary API function that will return campaigns data from cake. This is for CPA Wall deductions
     *
     *
     * @throws \Sabre\Xml\LibXMLException
     */
    public function campaignSummaryForCPAWALLDeduction($campaignSummaryBaseURL, $prefix, $subPrefix, $revenueTrackerID, $dateFromStr, $dateToStr): array
    {
        $campaignSummaryURL = $campaignSummaryBaseURL.'&start_date='.$dateFromStr.
            '&end_date='.$dateToStr.
            '&source_affiliate_id='.$revenueTrackerID.
            '&campaign_id=0'.
            '&site_offer_id=0';

        Log::info('deduct offer url: '.$campaignSummaryURL);

        $reader = new Reader();
        $reader->elementMap = [
            $prefix.'campaign_summary_response' => 'Sabre\Xml\Element\KeyValue',
            $prefix.'campaign_summary' => 'Sabre\Xml\Element\KeyValue',
            $prefix.'campaign' => 'Sabre\Xml\Element\KeyValue',
            $prefix.'site_offer' => 'Sabre\Xml\Element\KeyValue',
        ];

        $campaigns = [];
        $callCounter = 0;

        do {

            $curl = new Curl();
            $curl->get($campaignSummaryURL);

            if ($curl->error) {
                Log::info('campaignSummaryForCPAWALLDeduction API Error!');
                Log::info($curl->error_message);
                $callCounter++;
            } else {
                $reader->xml($curl->response);
                $response = $reader->parse();

                //get the content of the campaign summary
                if (isset($response['value'][$prefix.'campaigns'])) {
                    $campaigns = $response['value'][$prefix.'campaigns'];
                }
            }

            //stop the process when budget breached
            if ($callCounter == 10) {
                Log::info('campaignSummaryForCPAWALLDeduction API call limit reached!');
                break;
            }

        } while ($curl->error);

        return $campaigns;
    }

    /**
     * Campaign Summary API function that will return campaigns data from cake advertiser servers.
     *
     * @return null
     *
     * @throws \Sabre\Xml\LibXMLException
     */
    public function thirdPartyCPAWALLCampaignSummaryWithCD($campaignSummaryBaseURL, $prefix, $revenueTrackerID, $dateFromStr, $dateToStr)
    {
        $url = $campaignSummaryBaseURL.'&start_date='.$dateFromStr.'&end_date='.$dateToStr.'&sub_affiliate='.$revenueTrackerID;

        Log::info($url);

        $reader = new Reader();
        $reader->elementMap = [
            $prefix.'campaign_summary_response' => 'Sabre\Xml\Element\KeyValue',
            $prefix.'summary' => 'Sabre\Xml\Element\KeyValue',
        ];

        $campaignSummaryRevenue = null;
        $callCounter = 0;

        do {

            $curl = new Curl();
            $curl->get($url);

            if ($curl->error) {
                Log::info('thirdPartyCPAWALLCampaignSummaryWithCD API Error!');
                Log::info($curl->error_message);
                $callCounter++;
            } else {
                $reader->xml($curl->response);
                $response = $reader->parse();

                //get the content of the campaign summary
                //$campaignSummaryRevenue = $response['value'][$prefix.'campaigns'];
                if (isset($response['value'][$prefix.'summary'])) {
                    $campaignSummaryRevenue = $response['value'][$prefix.'summary'];
                }
            }

            //stop the process when budget breached
            if ($callCounter == 10) {
                Log::info('thirdPartyCPAWALLCampaignSummaryWithCD API call limit reached!');
                break;
            }

        } while ($curl->error);

        return $campaignSummaryRevenue;
    }

    /**
     * Click reports from Cake
     */
    public function clicksReport($clicksReportBaseURL, $prefix, $affiliateID, $campaignID, $dateFromStr, $dateToStr): ?array
    {
        $cakeClickURL = $clicksReportBaseURL."&affiliate_id=$affiliateID&campaign_id=$campaignID&start_date=$dateFromStr&end_date=$dateToStr&include_duplicates=true";

        Log::info($cakeClickURL);

        $reader = new Reader();

        $reader->elementMap = [
            $prefix.'click_report_response' => 'Sabre\Xml\Element\KeyValue',
            $prefix.'click' => 'Sabre\Xml\Element\KeyValue',
        ];

        $callCounter = 0;
        $response = null;

        do {
            $curl = new Curl();
            $curl->get($cakeClickURL);

            if ($curl->error) {
                Log::info('getCakeClicks API Error!');
                Log::info($curl->error_message);
                $callCounter++;
            } else {
                try {

                    $reader->xml($curl->response);
                    $response = $reader->parse();

                    //$clicksCount = $response['value'][$prefix.'row_count'];
                } catch (Exception $e) {
                    Log::info($e->getCode());
                    Log::info($e->getMessage());
                    $callCounter++;

                    //stop the process when budget breached
                    if ($callCounter == 10) {
                        Log::info('getCakeClicks API call limit reached!');
                        break;
                    }

                    continue;
                }
            }

            //stop the process when budget breached
            if ($callCounter == 10) {
                Log::info('getCakeClicks API call limit reached!');
                break;
            }

        } while ($curl->error);

        return $response;
    }
}
