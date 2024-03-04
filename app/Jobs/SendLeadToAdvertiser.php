<?php

namespace App\Jobs;

use App\AffiliateCampaign;
use App\Campaign;
use App\CampaignConfig;
use App\Helpers\Repositories\LeadData;
use App\Lead;
use App\LeadCount;
use Carbon\Carbon;
use Curl\Curl;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;

class SendLeadToAdvertiser extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $lead;

    /**
     * Create a new job instance.
     */
    public function __construct($lead)
    {
        $this->lead = $lead;
    }

    /**
     * Execute the job.
     */
    public function handle(LeadData $leadData): void
    {
        if ($this->attempts() > 1) {
            return;
        }

        Log::info('Send Lead Queue');
        Log::info('lead id: '.$this->lead->id);

        $campaignConfig = CampaignConfig::find($this->lead->campaign_id);

        //get the campaign and affiliate campaign counts record for handling the capping
        $campaignCounts = LeadCount::getCount(['campaign_id' => $this->lead->campaign_id])->first();
        $campaignAffiliateCounts = LeadCount::getCount(['campaign_id' => $this->lead->campaign_id, 'affiliate_id' => $this->lead->affiliate_id])->first();
        /*
        $dateNowStr =  Carbon::now()->toDateTimeString();

        //meaning no record of it yet so initialize
        if($campaignCounts==null)
        {
            //this mean no record of it then create it
            $campaignCounts = new LeadCount;
            $campaignCounts->campaign_id = $this->lead->campaign_id;
            $campaignCounts->affiliate_id = null;
            $campaignCounts->reference_date = $dateNowStr;

            $campaignCounts->save();
        }

        //meaning no record of it yet so initialize
        if($campaignAffiliateCounts==null)
        {
            //this mean no record of it then create it
            $campaignAffiliateCounts = new LeadCount;
            $campaignAffiliateCounts->campaign_id = $this->lead->campaign_id;
            $campaignAffiliateCounts->affiliate_id = $this->lead->affiliate_id;
            $campaignAffiliateCounts->reference_date = $dateNowStr;

            $campaignAffiliateCounts->save();
        }
        */
        $capReached = $this->checkCap($this->lead, $campaignCounts, $campaignAffiliateCounts);

        if ($capReached) {
            $this->lead->lead_status = 5;
            $this->lead->save();

            return;
        }

        if ($campaignConfig == null) {
            $this->lead->lead_status = 2;
            $this->lead->save();
            Log::info('Campaign Config null');

            return;
        }

        $leadCSV = $this->lead->leadDataCSV;

        if ($leadCSV == null) {
            $this->lead->lead_status = 2;
            $this->lead->save();
            Log::info('Lead CSV null');

            return;
        } elseif ($leadCSV->value == null) {
            $this->lead->lead_status = 2;
            $this->lead->save();
            Log::info('Lead CSV value null');

            return;
        }

        $leadDataCSV = json_decode($leadCSV->value, true);

        //get all the data
        $postURL = $campaignConfig->post_url;
        $postHeader = json_decode($campaignConfig->post_header, true);
        $postData = json_decode($campaignConfig->post_data, true);
        $postDataMap = json_decode($campaignConfig->post_data_map, true);
        $postDataFix = json_decode($campaignConfig->post_data_fixed_value, true);
        $postMethod = $campaignConfig->post_method;

        //advertiser server response
        $postSuccess = $campaignConfig->post_success;

        if (($postURL == null || $postURL == '') ||
            ($postData == null || $postData == '') ||
            ($postMethod == null || $postMethod == '') ||
            ($postSuccess == null || $postSuccess == '')
        ) {
            $this->lead->lead_status = 2;
            $this->lead->save();

            return;
        }

        $params = '?';
        foreach ($leadDataCSV as $key => $csvData) {
            $parameter = null;

            //get the parameter
            if (isset($postData[$key])) {
                $parameter = $postData[$key];
            }

            if ($parameter != null) {
                if (isset($postDataMap[$key])) {
                    //check if there's mapped value for the parameter
                    $dataMap = $postDataMap[$key];

                    if (is_array($dataMap)) {
                        if (count($dataMap) > 0) {
                            if (isset($dataMap[$csvData])) {
                                $csvData = $dataMap[$csvData];
                            } else {
                                $csvData = $dataMap['default'];
                            }
                        } else {
                            $csvData = $dataMap;
                        }
                    }
                }

                $params = $params.$parameter.'='.urlencode($csvData).'&';
            }
        }

        //append the post data fix values
        if ($postDataFix != null || $postDataFix != '') {
            foreach ($postDataFix as $key => $value) {
                if ($value != null || $value != '') {
                    $params = $params.$key.'='.urlencode($value).'&';
                }
            }
        }

        //get the last character in url parameter
        $params = rtrim($params, '&');

        //construct a CURL object to execute
        $advertiserURL = $postURL.$params;

        if (! isset($this->lead->retry_count)) {
            //this is the first time
            $isLeadSuccessful = $this->sendLead($postHeader, $postMethod, $advertiserURL, $postSuccess, $leadData, 0);

            if (! $isLeadSuccessful) {
                //this means it failed now change the status of this lead back to pending
                $this->lead->lead_status = 3;
                $this->lead->save();

                /*
                //reload the lead to ensure that the lead object has updated data.
                $this->lead = Lead::find($this->lead->id)->with('leadMessage','leadDataADV','leadDataCSV','leadSentResult')->first();

                //this means the sending failed in first attempt now retry sending up to 3 times.
                for($i=1;$i<=3;$i++){

                    $isLeadSuccessfulNow = $this->sendLead($postHeader,$postMethod,$advertiserURL,$postSuccess,$leadData,$campaignCounts,$campaignAffiliateCounts,1);

                    if($isLeadSuccessfulNow)
                    {
                        //this means the sending is successful then end the loop
                        break;
                    }
                }
                */
            }
        } else {
            if ($this->lead->retry_count < 3) {
                //this means is being retried to send.
                $isLeadSuccessful = $this->sendLead($postHeader, $postMethod, $advertiserURL, $postSuccess, $leadData, 1);

                //only allow it 3 times
                if (! $isLeadSuccessful && $this->lead->retry_count < 3) {
                    $this->lead->lead_status = 3;
                    $this->lead->save();
                }
            } else {
                //this means is being retried to send.
                $isLeadSuccessful = $this->sendLead($postHeader, $postMethod, $advertiserURL, $postSuccess, $leadData, 1);
            }
        }
    }

    /**
     * Sending of lead
     */
    protected function sendLead($postHeader, $postMethod, $advertiserURL, $postSuccess, $leadData, int $retry = 0): bool
    {
        $isLeadSuccess = false;

        //Log::info("last_retry_count: ".$this->lead->retry_count);
        //Log::info("last_retry_date: ".$this->lead->last_retry_date);

        $curl = new Curl();

        if ($postHeader != null) {
            //set the header
            foreach ($postHeader as $key => $header) {
                $curl->setHeader($key, $header);
            }
        }

        //set the method
        if ($postMethod == 'get' || $postMethod == 'GET') {
            $curl->get($advertiserURL);
        } elseif ($postMethod == 'post' || $postMethod == 'POST') {
            $curl->post($advertiserURL);
        }

        if ($curl->error) {
            Log::info("Error: $curl->error_code");

            switch ($curl->error_code) {
                case 403:
                case 405:
                case 410:
                case 400:
                case 401:
                case 404:
                case 408:
                case 500:
                case 501:
                case 503:
                    $this->lead->lead_status = 0;
                    break;

                    //connection timeout or could not connect for curl then mark the lead with timeout status
                case CURLE_COULDNT_CONNECT:
                case CURLE_OPERATION_TIMEOUTED:
                case 28:

                    $this->lead->lead_status = 6;
                    $leadID = $this->lead->id;
                    Log::info("Lead timeout! - Lead ID:  $leadID");

                    break;

                default:
                    $this->lead->lead_status = 0;
                    break;
            }

            //$this->lead->lead_status = 0;
            $isLeadSuccess = false;
        } else {
            if (strpos($curl->response, $postSuccess) !== false) {
                $this->lead->lead_status = 1;
                Log::info('Success!');

                //increment the counter here
                $leadData->incrementLeadCount($this->lead->campaign_id, $this->lead->affiliate_id);
                $isLeadSuccess = true;
            } else {
                $this->lead->lead_status = 2;
                Log::info('Rejected!');

                //considered success since advertiser server shows no error and this was deliberately rejected by the advertiser
                $isLeadSuccess = true;
            }
        }

        $leadData->updateOrCreateLeadADV($this->lead, $advertiserURL);

        //save the message/response headers
        $responseHeaders = '';
        if (is_array($curl->response_headers)) {
            foreach ($curl->response_headers as $header) {
                $responseHeaders = $responseHeaders.$header.' ';
            }
        } else {
            $responseHeaders = $curl->response_headers;
        }

        $leadData->updateOrCreateLeadMessage($this->lead, $responseHeaders);

        $leadData->updateOrCreateLeadSentResult($this->lead, trim($curl->response));

        if (isset($this->lead->retry_count)) {
            //set the last retry date
            $this->lead->last_retry_date = Carbon::now()->toDateString();
        }

        //set the retry count
        $this->lead->retry_count = intval($this->lead->retry_count) + $retry;

        $this->lead->save();

        //Log::info($isLeadSuccess ? 'true' : 'false');
        //Log::info("last_retry_count: ".$this->lead->retry_count);
        //Log::info("last_retry_date: ".$this->lead->last_retry_date);

        return $isLeadSuccess;
    }

    /**
     * Cap checker
     */
    protected function checkCap($lead, $campaignCounts, $campaignAffiliateCounts): bool
    {
        //get the cap limit for campaign and affiliate campaign
        $campaignCapDetails = Campaign::getCapDetails($lead->campaign_id)->first();
        $affiliateCampaignCapDetails = AffiliateCampaign::getCapDetails(['campaign_id' => $lead->campaign_id, 'affiliate_id' => $lead->affiliate_id])->first();

        $leadCapTypes = config('constants.LEAD_CAP_TYPES');
        $campaignCapType = $leadCapTypes[$campaignCapDetails->lead_cap_type];

        //get the cap type for affiliate campaign
        if (count($affiliateCampaignCapDetails) > 0) {
            $affiliateCampaignCapType = $leadCapTypes[$affiliateCampaignCapDetails->lead_cap_type];
        } else {
            //no existing affiliate campaign record therefore it is considered
            $affiliateCampaignCapType = $leadCapTypes[0];
        }

        $campaignCounts = $this->executeReset(
            $campaignCounts, //campaign lead count object
            $lead->campaign_id, //campaign id
            null, //affiliate id
            $campaignCapType //cap type
        );

        $campaignAffiliateCounts = $this->executeReset($campaignAffiliateCounts, $lead->campaign_id, $lead->affiliate_id, $affiliateCampaignCapType);

        $isCampaignCapReached = false;
        $isAffiliateCampaignCapReached = false;

        //check campaign cap
        switch ($campaignCapType) {
            case 'Daily':
                $isCampaignCapReached = $campaignCounts->count >= $campaignCapDetails->lead_cap_value;
                break;

            case 'Weekly':
                $isCampaignCapReached = $campaignCounts->count >= $campaignCapDetails->lead_cap_value;
                break;

            case 'Monthly':
                $isCampaignCapReached = $campaignCounts->count >= $campaignCapDetails->lead_cap_value;
                break;

            case 'Yearly':
                $isCampaignCapReached = $campaignCounts->count >= $campaignCapDetails->lead_cap_value;
                break;

            default:
                //unlimited
                $isCampaignCapReached = false;
        }

        //do not check if cap type is unlimited
        if ($affiliateCampaignCapType == $leadCapTypes[0]) {
            $isAffiliateCampaignCapReached = false;
        } else {
            //check campaign cap
            switch ($affiliateCampaignCapType) {
                case 'Daily':
                    $isAffiliateCampaignCapReached = $campaignAffiliateCounts->count >= $affiliateCampaignCapDetails->lead_cap_value;
                    break;

                case 'Weekly':
                    $isAffiliateCampaignCapReached = $campaignAffiliateCounts->count >= $affiliateCampaignCapDetails->lead_cap_value;
                    break;

                case 'Monthly':
                    $isAffiliateCampaignCapReached = $campaignAffiliateCounts->count >= $affiliateCampaignCapDetails->lead_cap_value;
                    break;

                case 'Yearly':
                    $isAffiliateCampaignCapReached = $campaignAffiliateCounts->count >= $affiliateCampaignCapDetails->lead_cap_value;
                    break;

                default:
                    //unlimited
                    $isAffiliateCampaignCapReached = false;
            }
        }

        if ($isCampaignCapReached) {
            //Log::info('cap reached!');
            //this means cap already reached set the lead to cap reached
            return true;
        }

        if ($isAffiliateCampaignCapReached) {
            //Log::info('cap reached!');
            //this means cap already reached set the lead to cap reached
            return true;
        }

        return false;
    }

    /**
     * This function resets the lead counter
     *
     * @param  null  $affiliateID
     */
    protected function executeReset($campaignCounts, $campaignID, $affiliateID = null, string $capType = 'Unlimited'): LeadCount
    {
        //reset the counter if it's time to reset already
        if ($campaignCounts == null) {
            $dateNowStr = Carbon::now()->toDateTimeString();

            //this mean no record of it then create it
            $campaignCounts = new LeadCount;
            $campaignCounts->campaign_id = $campaignID;
            $campaignCounts->affiliate_id = $affiliateID;
            $campaignCounts->reference_date = $dateNowStr;

            $campaignCounts->save();
        } else {
            //check campaign cap
            switch ($capType) {
                case 'Daily':

                    //daily campaign count reset
                    $dailyReferenceDate = Carbon::parse($campaignCounts->reference_date);
                    if ($dailyReferenceDate->startOfDay()->diffInDays(Carbon::now()->startOfDay()) > 0) {
                        //this means already past one day and count needs to reset
                        $campaignCounts->count = 0;
                        //change the reference date as well
                        $campaignCounts->reference_date = Carbon::now()->toDateTimeString();
                    }

                    break;

                case 'Weekly':

                    //reset if start of the week
                    $refWeekOfMonth = Carbon::parse($campaignCounts->reference_date)->weekOfMonth;
                    $currentWeekOfMonth = Carbon::now()->weekOfMonth;

                    if ($currentWeekOfMonth != $refWeekOfMonth) {
                        //this means already shifted to different week
                        $campaignCounts->count = 0;
                        //set the new reference date for the current week
                        $campaignCounts->reference_date = Carbon::now()->toDateTimeString();
                    }

                    break;

                case 'Monthly':

                    //reset if start of the month
                    $refMonth = Carbon::parse($campaignCounts->reference_date)->month;
                    $currentMonth = Carbon::now()->month;

                    if ($currentMonth != $refMonth) {
                        //this means already shifted to different month
                        $campaignCounts->count = 0;
                        //set the new reference date for the current month
                        $campaignCounts->reference_date = Carbon::now()->toDateTimeString();
                    }

                    break;

                case 'Yearly':

                    //reset if start of year
                    $refYear = Carbon::parse($campaignCounts->reference_date)->year;
                    $currentYear = Carbon::now()->year;

                    if ($currentYear != $refYear) {
                        //this means already shifted to different year
                        $campaignCounts->count = 0;
                        //set the new reference date for the current year
                        $campaignCounts->reference_date = Carbon::now()->toDateTimeString();
                    }

                    break;

                default:
                    //unlimited
            }

            $campaignCounts->save();
        }

        return $campaignCounts;
    }
}
