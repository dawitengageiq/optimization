<?php

namespace App\Http\Controllers;

use App\Affiliate;
use App\AffiliateCampaign;
use App\AffiliateRevenueTracker;
use App\Campaign;
use App\CampaignConfig;
use App\CampaignPayout;
use App\Cron;
use App\Helpers\Repositories\LeadData;
use App\Helpers\Repositories\Settings;
use App\Lead;
use App\LeadArchive;
use App\LeadCount;
use App\LeadDataAdv;
use App\LeadDataCsv;
use App\LeadDuplicate;
use App\LeadMessage;
use App\LeadSentResult;
use Carbon\Carbon;
use Curl\Curl;
use DB;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Log;

class LeadController extends Controller
{
    private $leadData;

    /**
     * Create new instance
     */
    public function __construct(LeadData $leadData)
    {
        $this->leadData = $leadData;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        //
    }

    /**
     * Send pending leads (old with cap checker)
     *
     *
     * @throws \ErrorException
     */
    public function sendPendingLeads(Settings $settings): JsonResponse
    {
        // Get the number of leads
        $numberOfLeads = $settings->getValue('num_leads_to_process_for_send_pending_leads');

        if ($numberOfLeads == null || $numberOfLeads == 0) {
            // defaults to 200
            $numberOfLeads = 200;
        }

        $pendingLeads = Lead::where('lead_status', '=', 3)->take($numberOfLeads)->get();

        //create the cron instance record
        $cron = new Cron;
        $cron->leads_queued = count($pendingLeads);
        $cron->leads_waiting = count($pendingLeads);
        $cron->time_started = Carbon::now()->toDateTimeString();
        $cron->save();

        $idsToUpdate = [];

        //mark the leads as queue
        foreach ($pendingLeads as $lead) {
            // $lead->lead_status = 4;
            // $lead->save();
            array_push($idsToUpdate, $lead->id);
        }

        // mass update the leads to queued status
        Lead::whereIn('id', $idsToUpdate)->update(['lead_status' => 4]);

        //now send them one by one
        foreach ($pendingLeads as $lead) {
            $campaignConfig = CampaignConfig::find($lead->campaign_id);
            //get the campaign and affiliate campaign counts record for handling the capping
            $campaignCounts = LeadCount::getCount(['campaign_id' => $lead->campaign_id])->first();
            $campaignAffiliateCounts = LeadCount::getCount(['campaign_id' => $lead->campaign_id, 'affiliate_id' => $lead->affiliate_id])->first();

            $dateNowStr = Carbon::now()->toDateTimeString();

            //meaning no record of it yet so initialize
            if ($campaignCounts == null) {
                //this mean no record of it then create it
                $campaignCounts = new LeadCount;
                $campaignCounts->campaign_id = $lead->campaign_id;
                $campaignCounts->affiliate_id = null;
                $campaignCounts->reference_date = $dateNowStr;
                $campaignCounts->save();
            }

            //meaning no record of it yet so initialize
            if ($campaignAffiliateCounts == null) {
                //this mean no record of it then create it
                $campaignAffiliateCounts = new LeadCount;
                $campaignAffiliateCounts->campaign_id = $lead->campaign_id;
                $campaignAffiliateCounts->affiliate_id = $lead->affiliate_id;
                $campaignAffiliateCounts->reference_date = $dateNowStr;
                $campaignAffiliateCounts->save();
            }

            $capReached = $this->checkCap($lead, $campaignCounts, $campaignAffiliateCounts);

            if ($capReached) {
                $lead->lead_status = 5;
                $lead->save();

                continue;
            }

            if ($campaignConfig == null) {
                $lead->lead_status = 2;
                $lead->save();

                continue;
            }

            $leadCSV = $lead->leadDataCSV;
            if ($leadCSV == null) {
                $lead->lead_status = 2;
                $lead->save();

                continue;
            } elseif ($leadCSV->value == null) {
                $lead->lead_status = 2;
                $lead->save();

                continue;
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
                $lead->lead_status = 2;
                $lead->save();

                continue;
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
                        $lead->lead_status = 0;
                        break;

                        //connection timeout or could not connect for curl then mark the lead with timeout status
                    case CURLE_COULDNT_CONNECT:
                    case CURLE_OPERATION_TIMEOUTED:
                    case 28:

                        $lead->lead_status = 6;
                        $leadID = $lead->id;
                        Log::info("Lead timeout! - Lead ID:  $leadID");

                        break;

                    default:
                        $lead->lead_status = 0;
                        break;
                }
            } else {
                if (strpos($curl->response, $postSuccess) !== false) {
                    $lead->lead_status = 1;

                    //update the count for the campaign
                    DB::statement("UPDATE lead_counts SET count = count + 1 WHERE campaign_id = $lead->campaign_id AND affiliate_id IS NULL");
                    //update the count for affiliate of the campaign
                    DB::statement("UPDATE lead_counts SET count = count + 1 WHERE campaign_id = $lead->campaign_id AND affiliate_id = $lead->affiliate_id");
                } else {
                    $lead->lead_status = 2;
                }
            }

            $this->leadData->updateOrCreateLeadADV($lead, $advertiserURL);

            //save the message/response headers
            $responseHeaders = '';

            if (is_array($curl->response_headers)) {
                foreach ($curl->response_headers as $header) {
                    $responseHeaders = $responseHeaders.$header.' ';
                }
            } else {
                $responseHeaders = $curl->response_headers;
            }

            $this->leadData->updateOrCreateLeadMessage($lead, $responseHeaders);
            $this->leadData->updateOrCreateLeadSentResult($lead, trim($curl->response));

            //update the cron here
            $cron->leads_waiting = intval($cron->leads_waiting) - 1;
            $cron->leads_processed = intval($cron->leads_processed) + 1;

            if (empty($cron->lead_ids)) {
                $cron->lead_ids = $lead->id;
            } else {
                $cron->lead_ids = $cron->lead_ids.','.$lead->id;
            }

            $cron->save();
            $lead->save();
        }

        //update the time ended of the cron
        $cron->status = 0;
        $cron->time_ended = Carbon::now()->toDateTimeString();
        $cron->save();

        return response()->json(['cron_message' => 'cron finished'], 200);
    }

    /**
     * Store a newly created or received lead in storage.
     *
     * @return \Illuminate\Http\RedirectResponse|string
     */
    public function store(Request $request)
    {
        $responseData = [
            'status' => 'lead_received',
            'message' => 'Lead received for processing',
        ];

        $inputs = $request->all();

        if ($request->has('phone')) {
            if ($request->input('phone') != '') {
                $phone = preg_replace('/[^0-9+]/', '', $request->input('phone'));
                $inputs['phone'] = $phone;
            }
        }

        if (! isset($inputs['eiq_email']) || empty($inputs['eiq_email'])) {
            $responseData['status'] = 'email_empty';
            $responseData['message'] = 'Email empty';
        } elseif (! isset($inputs['eiq_campaign_id']) || empty($inputs['eiq_campaign_id'])) {
            $responseData['status'] = 'campaign_id_empty';
            $responseData['message'] = 'Campaign id empty';
        } elseif (! isset($inputs['eiq_affiliate_id']) || empty($inputs['eiq_affiliate_id'])) {
            $responseData['status'] = 'affiliate_id_empty';
            $responseData['message'] = 'Affiliate id empty';
        } else {
            //$campaign = Campaign::find($inputs['eiq_campaign_id']);
            //$affiliate = Affiliate::find($inputs['eiq_affiliate_id']);
            $campaign = Campaign::select('id', 'lead_cap_type', 'lead_cap_value', 'default_payout', 'default_received')
                ->where('id', '=', $inputs['eiq_campaign_id'])
                ->where('status', '!=', 0)
                ->first();
            $affiliate = Affiliate::select('id')->where('id', '=', $inputs['eiq_affiliate_id'])->first();

            if ($campaign == null) {
                $responseData['status'] = 'campaign_not_found_or_invalid';
                $responseData['message'] = 'Campaign not found or invalid';
            } elseif ($affiliate == null) {
                $responseData['status'] = 'affiliate_not_found';
                $responseData['message'] = 'Affiliate not found';
            } else {
                //check the cap for campaign and affiliate
                //get the campaign and affiliate campaign counts record for handling the capping
                $campaignCounts = LeadCount::getCount(['campaign_id' => $campaign->id])->first();
                $campaignAffiliateCounts = LeadCount::getCount(['campaign_id' => $campaign->id, 'affiliate_id' => $affiliate->id])->first();

                $capReached = $this->checkCapNoReset($campaign, $affiliate->id, $campaignCounts, $campaignAffiliateCounts);

                if ($capReached) {
                    //this means cap already reached set the lead to cap reached
                    $responseData['status'] = 'cap_reached';
                    $responseData['message'] = 'Cap reached!';
                } else {
                    //get the campaign payout
                    $campaignPayout = CampaignPayout::getCampaignAffiliatePayout($inputs['eiq_campaign_id'], $inputs['eiq_affiliate_id'])->first();
                    $payout = 0.0;
                    $received = 0.0;

                    if (isset($campaignPayout) || $campaignPayout != null) {
                        $payout = $campaignPayout->payout;
                        $received = $campaignPayout->received;
                    } else {
                        $payout = $campaign->default_payout;
                        $received = $campaign->default_received;
                    }

                    //Check for Subid Breakdown
                    $revTracker = AffiliateRevenueTracker::where('revenue_tracker_id', $inputs['eiq_affiliate_id'])->first();
                    $allowBreakdown = 0;
                    $s1Breakdown = 0;
                    $s2Breakdown = 0;
                    $s3Breakdown = 0;
                    $s4Breakdown = 0;
                    $s5Breakdown = 0;
                    if ($revTracker) {
                        $allowBreakdown = $revTracker->subid_breakdown;
                        if ($allowBreakdown) {
                            $s1Breakdown = $revTracker->sib_s1;
                            $s2Breakdown = $revTracker->sib_s2;
                            $s3Breakdown = $revTracker->sib_s3;
                            $s4Breakdown = $revTracker->sib_s4;
                        }
                    }

                    //Internal Iframe or External Path Checker
                    if (isset($inputs['s5']) && $inputs['s5'] == 'EXTPATH') {
                        $allowBreakdown = true;
                        $s1Breakdown = true;
                        $s2Breakdown = true;
                        $s3Breakdown = true;
                        $s4Breakdown = true;
                        $s5Breakdown = true;
                    }
                    //Custom PingTree
                    if (isset($inputs['s5']) && $inputs['s5'] == 'EIQPingTreeLead2605') {
                        $allowBreakdown = true;
                        $s5Breakdown = true;
                    }

                    //handle the duplicate entry exception here
                    try {
                        //save the lead
                        $lead = Lead::create([
                            'campaign_id' => $inputs['eiq_campaign_id'],
                            'affiliate_id' => $inputs['eiq_affiliate_id'],
                            'creative_id' => isset($inputs['eiq_creative_id']) ? $inputs['eiq_creative_id'] : null,
                            's1' => isset($inputs['s1']) && $allowBreakdown && $s1Breakdown ? $inputs['s1'] : '',
                            's2' => isset($inputs['s2']) && $allowBreakdown && $s2Breakdown ? $inputs['s2'] : '',
                            's3' => isset($inputs['s3']) && $allowBreakdown && $s3Breakdown ? $inputs['s3'] : '',
                            's4' => isset($inputs['s4']) && $allowBreakdown && $s4Breakdown ? $inputs['s4'] : '',
                            's5' => isset($inputs['s5']) && $allowBreakdown && $s5Breakdown ? $inputs['s5'] : '',
                            'lead_step' => 1,
                            'lead_status' => 3,
                            'lead_email' => $inputs['eiq_email'],
                            'payout' => $payout,
                            'received' => $received,
                            'path_id' => isset($inputs['eiq_path_id']) ? $inputs['eiq_path_id'] : null,
                        ]);

                        $leadJsonData = json_encode($inputs);

                        $leadCSVData = new LeadDataCsv;
                        $leadCSVData->value = $leadJsonData;
                        $lead->leadDataCSV()->save($leadCSVData);
                        //$this->leadData->updateOrCreateLeadCSV($lead,$leadJsonData);

                        $responseData['lead_id'] = $lead->id;
                    } catch (QueryException $exception) {
                        $errorCode = $exception->errorInfo[1];

                        if ($errorCode == 1062) {
                            //houston, we have a duplicate entry problem
                            $responseData['status'] = 'duplicate_lead';
                            $responseData['message'] = 'Duplicate lead';

                            //save the lead
                            LeadDuplicate::create([
                                'campaign_id' => $inputs['eiq_campaign_id'],
                                'affiliate_id' => $inputs['eiq_affiliate_id'],
                                'creative_id' => isset($inputs['eiq_creative_id']) ? $inputs['eiq_creative_id'] : null,
                                's1' => isset($inputs['s1']) && $allowBreakdown && $s1Breakdown ? $inputs['s1'] : '',
                                's2' => isset($inputs['s2']) && $allowBreakdown && $s2Breakdown ? $inputs['s2'] : '',
                                's3' => isset($inputs['s3']) && $allowBreakdown && $s3Breakdown ? $inputs['s3'] : '',
                                's4' => isset($inputs['s4']) && $allowBreakdown && $s4Breakdown ? $inputs['s4'] : '',
                                's5' => isset($inputs['s5']) && $allowBreakdown && $s5Breakdown ? $inputs['s5'] : '',
                                'lead_step' => 1,
                                'lead_status' => 3,
                                'lead_email' => $inputs['eiq_email'],
                                'payout' => $payout,
                                'received' => $received,
                                'path_id' => isset($inputs['eiq_path_id']) ? $inputs['eiq_path_id'] : null,
                            ]);
                        } else {
                            Log::info('Lead insert error code: '.$errorCode);
                            Log::info('Error message: '.$exception->getMessage());

                            $responseData['status'] = 'lead_insert_error';
                            $responseData['message'] = $exception->getMessage();
                        }
                    }
                }
            }
        }

        if (isset($inputs['eiq_realtime']) && (int) $inputs['eiq_realtime'] == 1 && isset($lead)) {
            $lead->lead_status = 4;
            $lead->save();

            if (! isset($campaignCounts)) {
                $campaignCounts = null;
            }

            if (! isset($campaignAffiliateCounts)) {
                $campaignAffiliateCounts = null;
            }

            //query again the lead with it's data to ensure there will be no duplicate error when inserting lead data.
            //$lead = Lead::where('id','=',$lead->id)->with('leadMessage','leadDataADV','leadDataCSV','leadSentResult')->first();
            $responseData['realtime_message'] = $this->sendPendingLeadImmediately($lead, $campaignCounts, $campaignAffiliateCounts);

            if ($this->isRealTimeSuccess && ! $this->isRealTimeTimeout) {
                $responseData['realtime_meaning'] = 'Success';
            } elseif (! $this->isRealTimeSuccess && $this->isRealTimeTimeout) {
                $responseData['realtime_meaning'] = 'Timeout';
            } else {
                $responseData['realtime_meaning'] = 'Rejected';
            }
        }

        /*
        if(isset($inputs['eiq_redirect_url']) && $inputs['eiq_redirect_url']!='')
        {
            return redirect()->to($inputs['eiq_redirect_url']);
        }
        */

        if ($request->has('callback')) {
            return $request->input('callback').'('.json_encode($responseData).');';
        } else {
            if ($responseData['status'] == 'lead_insert_error') {
                return response()->json($responseData, 400);
            }

            return response()->json($responseData, 200);
        }
    }

    public function updateLeadsToPendingStatus($strLeadIDs, Request $request): JsonResponse
    {
        $inputs = $request->all();
        $tableName = isset($inputs['table']) ? $inputs['table'] : '';

        $responseData = [
            'status' => 'lead_updated',
            'message' => 'Lead updated to pending',
        ];

        if (! empty($tableName)) {
            $leadIDs = explode(',', $strLeadIDs);

            if ($tableName == 'leads') {
                foreach ($leadIDs as $leadID) {
                    $lead = Lead::find($leadID);

                    //resend only those leads that are not successfully submitted
                    if ($lead->exists() && $lead->status != 1) {
                        $lead->lead_status = 3;
                        // $lead->retry_count = 1;
                        $lead->last_retry_date = Carbon::now()->toDateString();
                        $lead->save();
                    }
                }
            } elseif ($tableName == 'leads_archive') {
                Log::info('restoring archived leads.');
                Log::info($leadIDs);

                $leads = LeadArchive::whereIn('id', $leadIDs)->with('leadMessage', 'leadDataADV', 'leadDataCSV', 'leadSentResult')->get();

                $archivedLeadIDsToDelete = [];

                // copy the leads from the leads table
                foreach ($leads as $archivedLead) {
                    try {
                        //save the lead
                        //                        $lead = Lead::create([
                        //                            'id' => $archivedLead->id,
                        //                            'campaign_id' => $archivedLead->campaign_id,
                        //                            'affiliate_id' => $archivedLead->affiliate_id,
                        //                            'creative_id' => $archivedLead->creative_id,
                        //                            's1' => $archivedLead->s1,
                        //                            's2' => $archivedLead->s2,
                        //                            's3' => $archivedLead->s3,
                        //                            's4' => $archivedLead->s4,
                        //                            's5' => $archivedLead->s5,
                        //                            'lead_step' => $archivedLead->lead_step,
                        //                            'lead_status' => 3,
                        //                            'lead_email' => $archivedLead->lead_email,
                        //                            'payout' => $archivedLead->payout,
                        //                            'received' => $archivedLead->received,
                        //                            'path_id' => $archivedLead->path_id,
                        //                            'created_at' => $archivedLead->created_at,
                        //                            'updated_at' => $archivedLead->updated_at
                        //                        ]);

                        $lead = new Lead;
                        $lead->id = $archivedLead->id;
                        $lead->campaign_id = $archivedLead->campaign_id;
                        $lead->affiliate_id = $archivedLead->affiliate_id;
                        $lead->creative_id = $archivedLead->creative_id;
                        $lead->s1 = $archivedLead->s1;
                        $lead->s2 = $archivedLead->s2;
                        $lead->s3 = $archivedLead->s3;
                        $lead->s4 = $archivedLead->s4;
                        $lead->s5 = $archivedLead->s5;
                        // $lead->lead_step = $archivedLead->lead_step;
                        $lead->lead_status = 3;
                        $lead->lead_email = $archivedLead->lead_email;
                        $lead->payout = $archivedLead->payout;
                        $lead->received = $archivedLead->received;
                        $lead->path_id = $archivedLead->path_id;
                        $lead->created_at = $archivedLead->created_at;
                        $lead->updated_at = $archivedLead->updated_at;
                        $lead->save();

                        if ($archivedLead->leadMessage != null) {
                            $leadMessage = new LeadMessage;
                            $leadMessage->value = $archivedLead->leadMessage->value;
                            $leadMessage->created_at = $archivedLead->leadMessage->created_at;
                            $leadMessage->updated_at = $archivedLead->leadMessage->updated_at;
                            $lead->leadMessage()->save($leadMessage);
                        }

                        if ($archivedLead->leadDataADV != null) {
                            $leadDataAdv = new LeadDataAdv;
                            $leadDataAdv->value = $archivedLead->leadDataADV->value;
                            $leadDataAdv->created_at = $archivedLead->leadDataADV->created_at;
                            $leadDataAdv->updated_at = $archivedLead->leadDataADV->updated_at;
                            $lead->leadDataADV()->save($leadDataAdv);
                        }

                        if ($archivedLead->leadDataCSV != null) {
                            $leadCSVData = new LeadDataCsv;
                            $leadCSVData->value = $archivedLead->leadDataCSV->value;
                            $leadCSVData->created_at = $archivedLead->leadDataCSV->created_at;
                            $leadCSVData->updated_at = $archivedLead->leadDataCSV->updated_at;
                            $lead->leadDataCSV()->save($leadCSVData);
                        }

                        if ($archivedLead->leadSentResult != null) {
                            $leadSentResult = new LeadSentResult;
                            $leadSentResult->value = $archivedLead->leadSentResult->value;
                            $leadSentResult->created_at = $archivedLead->leadSentResult->created_at;
                            $leadSentResult->updated_at = $archivedLead->leadSentResult->updated_at;
                            $lead->leadSentResult()->save($leadSentResult);
                        }

                        // $lead->save();

                        // Now add the lead id to the delete batch
                        array_push($archivedLeadIDsToDelete, $archivedLead->id);

                    } catch (QueryException $exception) {
                        Log::info('Failed to restore archived lead_id = '.$archivedLead->id);
                        Log::info($exception);
                    }
                }

                // delete the restored archived leads
                LeadArchive::whereIn('id', $archivedLeadIDsToDelete)->delete();
            }
        } else {
            $responseData = [
                'status' => 'no_table',
                'message' => 'No table selected',
            ];
        }

        return response()->json($responseData, 200);
    }

    /**
     * This function resets the lead counter
     *
     * @param  null  $affiliateID
     */
    public function executeReset($campaignCounts, $campaignID, $affiliateID = null, string $capType = 'Unlimited'): LeadCount
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

    public function checkCapNoReset($campaign, $affiliateID, $campaignCounts, $campaignAffiliateCounts)
    {
        if ($campaignCounts == null) {
            return false;
        }

        if ($campaignAffiliateCounts == null) {
            return false;
        }

        //get the cap limit for campaign and affiliate campaign
        //$campaignCapDetails = Campaign::getCapDetails($campaignID)->first();
        $affiliateCampaignCapDetails = AffiliateCampaign::getCapDetails(['campaign_id' => $campaign->id, 'affiliate_id' => $affiliateID])->first();

        $leadCapTypes = config('constants.LEAD_CAP_TYPES');
        $campaignCapType = $leadCapTypes[$campaign->lead_cap_type];
        $aCCD = is_array($affiliateCampaignCapDetails) ? count($affiliateCampaignCapDetails) : 0;
        //get the cap type for affiliate campaign
        if ( $aCCD > 0) {
            $affiliateCampaignCapType = $leadCapTypes[$affiliateCampaignCapDetails->lead_cap_type];
        } else {
            //no existing affiliate campaign record therefore it is considered
            $affiliateCampaignCapType = $leadCapTypes[0];
        }

        $isCampaignCapReached = false;
        $isAffiliateCampaignCapReached = false;

        //check campaign cap
        switch ($campaignCapType) {
            case 'Daily':
                $isCampaignCapReached = $campaignCounts->count >= $campaign->lead_cap_value;
                break;

            case 'Weekly':
                $isCampaignCapReached = $campaignCounts->count >= $campaign->lead_cap_value;
                break;

            case 'Monthly':
                $isCampaignCapReached = $campaignCounts->count >= $campaign->lead_cap_value;
                break;

            case 'Yearly':
                $isCampaignCapReached = $campaignCounts->count >= $campaign->lead_cap_value;
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
            //this means cap already reached set the lead to cap reached
            return true;
        }

        if ($isAffiliateCampaignCapReached) {
            //this means cap already reached set the lead to cap reached
            return true;
        }

        return false;
    }

    /**
     * Cap checker
     *
     * @return bool
     */
    public function checkCap($lead, $campaignCounts, $campaignAffiliateCounts)
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
            Log::info('cap reached!');
            //this means cap already reached set the lead to cap reached
            return true;
        }

        if ($isAffiliateCampaignCapReached) {
            Log::info('cap reached!');
            //this means cap already reached set the lead to cap reached
            return true;
        }

        return false;
    }

    public $isRealTimeSuccess;

    public $isRealTimeTimeout;

    /**
     * Sending leads in real time
     *
     * @param  null  $campaignCounts
     * @param  null  $campaignAffiliateCounts
     * @return null
     */
    public function sendPendingLeadImmediately($lead, $campaignCounts = null, $campaignAffiliateCounts = null)
    {
        $this->isRealTimeSuccess = true;
        $this->isRealTimeTimeout = false;

        $campaignConfig = CampaignConfig::find($lead->campaign_id);
        $leadDataCSV = json_decode($lead->leadDataCSV->value, true);

        //get the campaign and affiliate campaign counts record for handling the capping
        //$campaignCounts = LeadCount::getCount(['campaign_id' => $lead->campaign_id])->first();
        //$campaignAffiliateCounts = LeadCount::getCount(['campaign_id' => $lead->campaign_id, 'affiliate_id' => $lead->affiliate_id])->first();
        $dateNowStr = Carbon::now()->toDateTimeString();

        //meaning no record of it yet so initialize
        if ($campaignCounts == null) {
            //this mean no record of it then create it
            $campaignCounts = new LeadCount;
            $campaignCounts->campaign_id = $lead->campaign_id;
            $campaignCounts->affiliate_id = null;
            $campaignCounts->reference_date = $dateNowStr;

            $campaignCounts->save();
        }

        //meaning no record of it yet so initialize
        if ($campaignAffiliateCounts == null) {
            //this mean no record of it then create it
            $campaignAffiliateCounts = new LeadCount;
            $campaignAffiliateCounts->campaign_id = $lead->campaign_id;
            $campaignAffiliateCounts->affiliate_id = $lead->affiliate_id;
            $campaignAffiliateCounts->reference_date = $dateNowStr;

            $campaignAffiliateCounts->save();
        }

        $capReached = $this->checkCap($lead, $campaignCounts, $campaignAffiliateCounts);

        if ($capReached) {
            $lead->lead_status = 5;
            $lead->save();

            return 'cap reached';
        }

        if (! $campaignConfig->exists() || $leadDataCSV == '') {
            return 'no campaign configuration or lead data csv';
        }

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
            return 'Invalid posting configuration';
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
            $this->isRealTimeSuccess = false;

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
                    $lead->lead_status = 0;
                    break;

                    //connection timeout for curl then mark the lead with timeout status
                case CURLE_COULDNT_CONNECT:
                case CURLE_OPERATION_TIMEOUTED:
                case 28:

                    $this->isRealTimeTimeout = true;

                    $lead->lead_status = 6;
                    $leadID = $lead->id;
                    Log::info("Lead timeout! - Lead ID:  $leadID");

                    break;

                default:
                    $lead->lead_status = 0;
                    break;
            }
        } else {
            if (strpos($curl->response, $postSuccess) !== false) {
                $this->isRealTimeSuccess = true;
                $this->isRealTimeTimeout = false;

                $lead->lead_status = 1;

                /*
                //increment the counter here
                $campaignCounts->count = intval($campaignCounts->count) + 1;
                $campaignAffiliateCounts->count = intval($campaignAffiliateCounts->count) + 1;
                $campaignCounts->save();
                $campaignAffiliateCounts->save();
                */

                //update the count for the campaign
                DB::statement("UPDATE lead_counts SET count = count + 1 WHERE campaign_id = $lead->campaign_id AND affiliate_id IS NULL");
                //update the count for affiliate of the campaign
                DB::statement("UPDATE lead_counts SET count = count + 1 WHERE campaign_id = $lead->campaign_id AND affiliate_id = $lead->affiliate_id");
            } else {
                $this->isRealTimeSuccess = false;
                $this->isRealTimeTimeout = false;
                $lead->lead_status = 2;
            }
        }

        //$leadDataAdv = new LeadDataAdv;
        //$leadDataAdv->value = $advertiserURL;
        //$lead->leadDataADV()->save($leadDataAdv);
        $this->leadData->updateOrCreateLeadADV($lead, $advertiserURL);

        //save the message/response headers
        $responseHeaders = '';
        if (is_array($curl->response_headers)) {
            foreach ($curl->response_headers as $header) {
                $responseHeaders = $responseHeaders.$header.' ';
            }
        } else {
            $responseHeaders = $curl->response_headers;
        }

        //$leadMessage = new LeadMessage;
        //$leadMessage->value = $responseHeaders;
        //$lead->leadMessage()->save($leadMessage);
        $this->leadData->updateOrCreateLeadMessage($lead, $responseHeaders);

        //save the sent result/ actual response of lead posting
        //$leadSentResult = new LeadSentResult;
        //$leadSentResult->value = trim($curl->response);
        //$lead->leadSentResult()->save($leadSentResult);
        $this->leadData->updateOrCreateLeadSentResult($lead, trim($curl->response));

        $lead->save();

        return $curl->response;
    }
}
