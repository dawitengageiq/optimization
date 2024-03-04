<?php
/**
 * Created by PhpStorm.
 * User: magbanua-ariel
 * Date: 11/23/2016
 * Time: 1:21 PM
 */

namespace App\Helpers;

use App\AffiliateReport;
use App\AffiliateRevenueTracker;
use App\Campaign;
use Illuminate\Database\QueryException;
use Log;

class ExternalPathPermissionDataHelper
{
    private $login = 'repAOP';

    private $password = '31N5T3in';

    private $format = 'XML';

    private $getSessionURL = 'http://api.pdreporting.com/API/aerAPI/v1/?output=output_format&method=getSessionUUID&login=user_login&pin=password_login&pub_code=pubcode&pub_key=everything_is_awesome';

    private $latestSessionID = null;

    private $dateFrom;

    private $dateTo;

    private $pubCode;

    private $externalPathPermissionDataHelperCampaignID;

    private $campaign;

    public function __construct($externalPathPermissionDataHelperCampaignID, $dateFrom, $dateTo, $pubCode, $format)
    {
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
        $this->pubCode = $pubCode;
        $this->format = $format;

        //build the url here
        $this->getSessionURL = str_replace('output_format', $this->format, $this->getSessionURL);
        $this->getSessionURL = str_replace('user_login', $this->login, $this->getSessionURL);
        $this->getSessionURL = str_replace('password_login', $this->password, $this->getSessionURL);
        $this->getSessionURL = str_replace('pubcode', $this->pubCode, $this->getSessionURL);

        $this->externalPathPermissionDataHelperCampaignID = $externalPathPermissionDataHelperCampaignID;

        $this->log($this->getSessionURL);
    }

    private function log($message)
    {
        Log::info('PERMISSION_DATA_HELPER: '.$message);
    }

    public function getReports()
    {
        $this->campaign = Campaign::find($this->externalPathPermissionDataHelperCampaignID);

        if ($this->externalPathPermissionDataHelperCampaignID > 0 && $this->campaign->exists()) {
            if ($this->getSession()) {
                //put a delay
                sleep(5);

                $this->getSourceTrackingByDateReport();
                $this->log('has session!');
            } else {
                $this->log('no session or server is down!');
            }
        } else {
            $this->log('Campaign not found!');
        }
    }

    private function getSession()
    {

        $parser = new JSONParser();

        //get the session
        $sessionObjectResponse = $parser->getXMLResponseObject($this->getSessionURL);

        if ($parser->getErrorCode() != 200) {
            return false;
        }

        if (! $sessionObjectResponse) {
            //this means api call fails then email Sir burt
            $this->log('Session Response failed!');

            return false;
        }

        $this->log('Session Response successful!');

        $this->latestSessionID = (string) $sessionObjectResponse->result->sessionUUID;

        return true;
    }

    private function getSourceTrackingByDateReport()
    {
        //build the url for getSourceTrackingByDateReport
        $sourceTrackingURL = 'http://api.pdreporting.com/API/aerAPI/v1/?output=XML&method=getSourceTrackingByDateReport&date=date_range&pub_code=pubcode&sessionUUID='.$this->latestSessionID;
        $dateRangeStr = $this->dateFrom.','.$this->dateTo;
        $sourceTrackingURL = str_replace('date_range', $dateRangeStr, $sourceTrackingURL);
        $sourceTrackingURL = str_replace('pubcode', $this->pubCode, $sourceTrackingURL);

        $parser = new JSONParser();

        $this->log($sourceTrackingURL);
        $this->log($dateRangeStr);

        //$sourceTrackingObjectResponse = $parser->getXMLResponseObject($sourceTrackingURL);
        $sourceTrackingObjectResponse = $parser->getXMLResponseNoCDATA($sourceTrackingURL);

        if ($sourceTrackingObjectResponse === null || ! $sourceTrackingObjectResponse) {
            //this means api call fails then email Sir burt
            $this->log('SourceTrackingObjectResponse failed!');

            return false;
        }

        //cast it to array to get all groups
        $groups = $this->xmlToArray($sourceTrackingObjectResponse->result->report->groups, []);

        if (isset($groups['group'])) {
            $reportGroups = $groups['group'];

            $date = $reportGroups['groupField'];

            if (isset($reportGroups['groupRecords']['record']['sourceId'])) {
                //this means only one record do exist
                $record = $reportGroups['groupRecords']['record'];
                $this->processRecord($record, $date, $this->campaign);
            } else {
                //multiple record
                $records = $reportGroups['groupRecords']['record'];

                foreach ($records as $record) {
                    $record = $this->xmlToArray($record, []);
                    $this->processRecord($record, $date, $this->campaign);
                }
            }
        } else {
            //this means api call fails then email Sir burt
            $this->log('SourceTrackingObjectResponse failed!');

            return false;
        }
    }

    private function processRecord($record, $date, $campaign)
    {

        //get the publisher id
        $publisherID = '';

        if (isset($record['sourceId'])) {
            $publisherID = $record['sourceId'];
        } else {
            return;
        }

        $pubArray = explode('_', $publisherID);
        $publisherID = $pubArray[1];
        //$grossRevenue = doubleval($record->grossRevenue);

        //get the revenue tracker and removed the CD prefix
        $revenueTrackerID = str_replace('CD', '', $publisherID);
        //get the affiliate for the particular revenue tracker
        $tracker = AffiliateRevenueTracker::where('revenue_tracker_id', '=', $revenueTrackerID)->first();

        if ($tracker != null) {
            $publisherRevenueShare = 0.0;

            if (isset($record['publisherRevenueShare'])) {
                $publisherRevenueShare = floatval($record['publisherRevenueShare']);
            }

            $affiliateReport = AffiliateReport::firstOrNew([
                'affiliate_id' => $tracker->affiliate_id,
                'revenue_tracker_id' => $revenueTrackerID,
                'campaign_id' => $campaign->id,
                'created_at' => $date,
            ]);

            $affiliateReport->lead_count = 0;
            //$grossRevenue = $grossRevenue*.70;
            //$grossRevenue = round($grossRevenue, 2, PHP_ROUND_HALF_DOWN);

            $publisherRevenueShare = round($publisherRevenueShare, 2, PHP_ROUND_HALF_DOWN);
            $affiliateReport->revenue = $publisherRevenueShare;

            try {
                $affiliateReport->save();
                $this->log($affiliateReport->revenue_tracker_id.' success!');
            } catch (QueryException $e) {
                $this->log($e->getMessage());
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
