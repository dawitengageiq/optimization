<?php

namespace App\Http\Controllers;

use App\BannedAttempt;
use App\Http\Requests;
use App\Http\Services;
use App\LeadUserBanned;
use App\Setting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use RevenueTracker;
use Illuminate\Support\Facades\Route;
use SurveyStack;
use Zip;

class CampaignListController extends Controller
{
    /**
     * Injected class
     */
    protected $isBanned;

    protected $settings;

    protected $apiConfig;

    protected $campaignList;

    protected $revenueTracker;

    /**
     * default variables
     */
    protected $limit;

    protected $filter;

    protected $currentUri;

    protected $affiliateWebsites = [
        'is_registered' => false,
        'is_disabled' => false,
        'is_email_unique' => false,
    ];

    /**
     * User details container
     */
    protected $userDetails;

    /**
     * Load the needed dependencies
     */
    public function __construct(
        Services\Contracts\CampaignListContract $campaignList,
        Services\Campaigns\Repos\RevenueTracker $revenueTracker,
        Services\Campaigns\Repos\Settings $campaignSettings,
        Services\Campaigns\Repos\ApiConfig $apiConfig
    ) {
        // \DB::enableQueryLog();
        $this->campaignList = $campaignList;
        $this->revenueTracker = $revenueTracker;
        $this->settings = $campaignSettings;
        $this->apiConfig = $apiConfig;

        $this->currentUri = Route::current()->uri;
        $this->isBanned = false;
    }

    /**
     * Get campaigns after registration in PFR
     * Note: revenue tracker was set on service provider by supplying request data
     *
     * @return array
     */
    public function registerUserAndGetCampaigns(
        Requests\CampaignListRequest $request,
        Services\Campaigns\Repos\Zip $zip,
        Services\Campaigns\Repos\LeadUser $leadUser
    ) {
        // \Log::info($request->all());
        /* Check if user is banned */
        $phone = $request->phone;
        if ($phone == '') {
            $phone = $request->phone1.$request->phone2.$request->phone3;
        }
        $phone = preg_replace('/[^0-9]/', '', $phone);
        $banned = LeadUserBanned::where('email', $request->email);
        if ($phone != '') {
            $banned = $banned->orWhere('phone', $phone);
        }
        $banned = $banned->get();
        if (count($banned) > 0) {
            $this->isBanned = true;
        }
        /* OVERRIDE PROPERTIES, FETCH DATA FROM REQUEST */
        $this->setProperties($request);

        /* SAVE USER DETAILS */
        $this->saveUserDetails($zip, $leadUser, true);

        if ($this->isBanned) {
            return [
                'status' => 'error',
                'code' => 'banned',
                'message' => 'There are no campaigns available for you at the moment.',
            ];
        }

        /* GET ACTIVE CAMPAIGNS */
        //  Pass the data to injected class
        $this->campaignList->setUserDetails($this->userDetails);
        // Set campaign type ordering , get campaigns with campaign type is in stack_path_campaign_type_order
        $this->campaignList->setTypeOrdering(json_decode($this->settings->campaignTypeOrder()));
        // Get the campaigns
        $this->campaignList->getCampaigns($this->revenueTracker->trackerID());
        // Set first level limit per revenue tracker
        $this->campaignList->setfirstLevelLimit($this->revenueTracker->limit());

        /* FILTER CAMPAIGNS */
        $this->setOrderAndLimit();
        $this->filterCampaigns();

        //Check if Rev Tracker allows SubId Breakdown
        // if($this->revenueTracker->subidBreakdown() == 0) { //if inactive set subids to null
        //     $this->userDetails['s1'] = '';
        //     $this->userDetails['s2'] = '';
        //     $this->userDetails['s3'] = '';
        //     $this->userDetails['s4'] = '';
        //     $this->userDetails['s5'] = '';
        // }

        $campaigns = array_values($this->campaignList->stacking->get($this->revenueTracker->pathType())->toArray());

        if (count($campaigns) == 0) {
            $this->sendWarningEmail();
        }

        /* RETURN NEEDED DATA */
        $arry = [
            'status' => 'success',
            'user' => $this->userDetails,
            'revenue_tracker_id' => $this->revenueTracker->trackerID(),
            'path_type' => $this->revenueTracker->pathType(),
            'campaigns' => $campaigns,
            'creatives' => $this->campaignList->creatives(),
            'campaign_types' => $this->campaignList->stacking->getCampaignTypeNameOrder(),
            // Below indexes will determine what types are being used.
            'stacking' => $request->stackType().' stacking',
            'ordering' => ($this->campaignList->stacking->hasOrder()) ? $this->campaignList->stacking->orderType().' ordering' : 'Proirity ordering',
            'limit' => $request->limitType().' limit',
        ];

        // \Log::info(\DB::getQueryLog());
        // \Log::info($arry);
        return $arry;
    }

    /**
     * Get campaigns from API
     * Note: revenue tracker was set on service provider
     */
    public function getCampaignsByApi(
        Requests\CampaignListApiRequest $request,
        Services\Campaigns\Repos\Zip $zip,
        Services\Campaigns\Repos\LeadUser $leadUser
    ): View {
        /**
         * STEP 1
         * Determine the needed campaign ids
         */

        /* OVERRIDE PROPERTIES, FETCH DATA FROM REQUEST */
        $this->setProperties($request);

        /* SAVE USER DETAILS IF UNIQUE */
        $this->saveUserDetails($zip, $leadUser, $this->affiliateWebsites['is_email_unique']);

        /* GET ACTIVE CAMPAIGNS */
        //  Pass the data to injected class
        $this->campaignList->setUserDetails($this->userDetails);
        // Campaigns where campaign type is in the supplied array
        $this->campaignList->setTypeOrdering($this->apiConfig->campaignTypeOrder());
        // Exclude the campaign ids that are in the supplied array
        $this->campaignList->setExcludedCampaignIds($this->apiConfig->excludedCampaignID());
        // Get the campaigns
        $this->campaignList->getCampaigns($this->revenueTracker->trackerID());
        // Set first level limit per revenue tracker
        $this->campaignList->setfirstLevelLimit($this->revenueTracker->limit());

        /* FILTER CAMPAIGNS */
        $this->setOrderAndLimit();
        $this->filterCampaigns();

        /* IF ONE LOADING, EXIT  */
        if ($this->apiConfig->isMultiPage()) {
            // Set SurveyStack as view helper
            SurveyStack::setPathID($this->revenueTracker->pathType())
                ->settargetUrl($request->targetUrl())
                ->setRedirectUrl($request->redirectUrl())
                ->setReloadParentFrame($request->toReloadParentFrame())
                ->setUserDetails(array_merge($this->userDetails, $this->browserDetails, $this->deviceDetails))
                ->setQueryString($this->campaignList->buildQueryString($this->revenueTracker->pathType()));

            // We'll be using blade this time for eval will be executed on ajax request
            return view('api.one_loading_list');
        }

        /**
         * STEP 2
         * Get the html content of each campaigns
         */
        /* GET CAMPAIGNS CONTENT */
        $data = $this->campaignList->getCampaignsContent(
            array_merge($this->userDetails, $this->browserDetails, $this->deviceDetails, ['target_url' => $request->targetUrl()]),
            $this->apiConfig->displayLimit(),
            $this->campaignList->stacking
                // Get the listing
                ->get($this->revenueTracker->pathType())
                // Flaten to single dimensional
                ->flatten()
                // Return to array
                ->toArray()
        );

        /* RETURN NEEDED DATA */
        // Need to use view but not blade for we will execute eval function
        return view('api.campaign_list')
            ->with('data', $data)
            ->with('user_details', $this->userDetails)
            ->with('redirect_url', $request->redirectUrl())
            ->with('reload_parent_frame', $request->toReloadParentFrame());
    }

    /**
     * Set the class properties and the injected class details
     *
     * @param  Requests\CampaignListRequest|Requests\CampaignListApiRequest  $request
     */
    protected function setProperties($request)
    {
        // If request have the campaign settings, the settings was provided in provider
        if ($request->has('campaign_settings')) {
            $this->settings->setSettings($request->get('campaign_settings'));
        }
        // If not then query.
        else {
            $this->settings->get();
        }

        // Provide settings
        $this->campaignList->setCampaignNoLimitSettings($this->settings->campaignNoLimit());
        $this->campaignList->filterProcessStatus($this->settings->filterProcessStatus());

        // If request have the revenue tracker details, the details was provided in provider
        if ($request->has('revenue_tracker')) {
            $this->revenueTracker->setDetails($request->get('revenue_tracker'));
        }
        // If not then query.
        else {
            $this->revenueTracker->get($request->all());
        }

        if ($this->currentUri == 'frame/get_campaign_list_by_api') {
            // If request have the api config details, the details was provided in provider
            if ($request->has('api_config')) {
                $this->apiConfig->setDetails($request->get('api_config'));
            }
            // If not then query.
            else {
                $this->apiConfig->get($request->get('affiliate_id'));
            }

            // Affiliate website details
            $this->affiliateWebsites = $request->get('affiliate_websites');
        }

        // Provide from request.
        $this->limit = $request->limit();
        $this->filter = $request->filter();
        $this->userDetails = $request->userDetails();
        if (method_exists($request, 'deviceDetails')) {
            $this->deviceDetails = $request->deviceDetails();
        }
        if (method_exists($request, 'browserDetails')) {
            $this->browserDetails = $request->browserDetails();
        }
    }

    /**
     * Save user details
     *
     * @param  bool  $toSave  whether to save the user details
     */
    protected function saveUserDetails(
        Services\Campaigns\Repos\Zip $zip,
        Services\Campaigns\Repos\LeadUser $leadUser,
        bool $toSave
    ) {
        // Zip details
        $zip->set($this->userDetails['zip']);

        // Add information from zip and revenue tracker
        $this->userDetails['state'] = $zip->state();
        $this->userDetails['city'] = $zip->city();
        $this->userDetails['revenue_tracker_id'] = $this->revenueTracker->trackerID();
        $this->userDetails['path_id'] = $this->revenueTracker->pathType();

        //Check if Rev Tracker allows SubId Breakdown
        if ($this->revenueTracker->subidBreakdown() == 0) { //if inactive set subids to null
            $this->userDetails['s1'] = '';
            $this->userDetails['s2'] = '';
            $this->userDetails['s3'] = '';
            $this->userDetails['s4'] = '';
            $this->userDetails['s5'] = '';
        } else {
            if ($this->revenueTracker->s1Breakdown() == 0) {
                $this->userDetails['s1'] = '';
            }
            if ($this->revenueTracker->s2Breakdown() == 0) {
                $this->userDetails['s2'] = '';
            }
            if ($this->revenueTracker->s3Breakdown() == 0) {
                $this->userDetails['s3'] = '';
            }
            if ($this->revenueTracker->s4Breakdown() == 0) {
                $this->userDetails['s4'] = '';
            }
            if ($this->revenueTracker->s5Breakdown() == 0) {
                $this->userDetails['s5'] = '';
            }
        }
        // \Log::info($this->userDetails);
        // Determine if to save.
        if ($toSave) {
            if ($this->isBanned) {
                BannedAttempt::create([
                    'first_name' => $this->userDetails['first_name'],
                    'last_name' => $this->userDetails['last_name'],
                    'email' => $this->userDetails['email'],
                    'birthdate' => Carbon::createFromDate($this->userDetails['dobyear'], $this->userDetails['dobmonth'], $this->userDetails['dobday'])->format('Y-m-d'),
                    'gender' => $this->userDetails['gender'],
                    'zip' => $this->userDetails['zip'],
                    'city' => $this->userDetails['city'],
                    'state' => $this->userDetails['state'],
                    'ethnicity' => 0,
                    'address1' => $this->userDetails['address'],
                    'address2' => $this->userDetails['user_agent'],
                    'phone' => $this->userDetails['phone1'].$this->userDetails['phone2'].$this->userDetails['phone3'],
                    'source_url' => $this->userDetails['source_url'],
                    'affiliate_id' => $this->userDetails['affiliate_id'],
                    'revenue_tracker_id' => $this->userDetails['revenue_tracker_id'],
                    's1' => $this->userDetails['s1'],
                    's2' => $this->userDetails['s2'],
                    's3' => $this->userDetails['s3'],
                    's4' => $this->userDetails['s4'],
                    's5' => $this->userDetails['s5'],
                    'ip' => $this->userDetails['ip'],
                    'is_mobile' => $this->userDetails['screen_view'] == 1 ? false : true,
                ]);
            } else {
                $leadUser->save($this->userDetails);
            }
        }
    }

    /**
     * Set order and limit
     */
    protected function setOrderAndLimit()
    {
        /* LIMIT THE NUMBER OF STACKS PER CAMPAIGN TYPE */
        $this->campaignList->stacking->setOrderAndLimits([
            $this->limit,
            $this->revenueTracker->limit(),
            /* PROVIDE REVENUE TRACKER ID */
            $this->revenueTracker->trackerID(),
            json_decode($this->settings->campaignTypeOrder()),
        ]);
    }

    /**
     * Filter campaigns if passed
     */
    protected function filterCampaigns()
    {

        $this->campaignList->filterCampaigns(
            $this->filter,
            /* PROVIDE PATH TYPE */
            $this->revenueTracker->pathType(),
            /* PROVIDE REVENUE TRACKER ID */
            $this->revenueTracker->trackerID()
        );
    }

    protected function sendWarningEmail()
    {
        $time = Carbon::now()->toDateTimeString();
        $setting = Setting::where('code', 'error_email_recipient')->first();
        $recipients = array_map('trim', array_filter(explode(';', $setting->description)));
        $url = url('admin/campaigns');
        Mail::send('emails.no_campaigns_returned_to_user',
            ['date' => $time, 'url' => $url],
            function ($m) use ($recipients) {
                $m->from('karla@engageiq.com');
                $m->to($recipients)
                    ->subject('CRITICAL ERROR! Lead Reactor not returning campaigns.');
            });
    }
}
