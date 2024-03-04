<?php

namespace App\Http\Services\Campaigns;

use App\Campaign;
use App\CampaignContent;
use App\CampaignTypeReport;
use App\CampaignTypeView;
use App\CampaignView;
use App\CampaignViewReport;
use App\Http\Services\Campaigns\Utils\Content\ContentReplaceable;
use App\Jobs\SaveCampaignTypeView;
use App\Jobs\SaveCampaignView;
use Carbon\Carbon;

class Content
{
    /*
     * Default variables
     *
     */
    protected $stackContents;

    protected $campaignType;

    protected $inputs = [];

    protected $campaigns = [];

    protected $campaignCreatives = [];

    protected $creativeIDs = [];

    protected $affiliateID = '';

    protected $session = '';

    protected $path = '';

    protected $stackContent = '';

    protected $campaignCounter = 0;

    protected $typesThatStacked = [1, 2, 5, 8, 9, 10, 11, 12, 13];

    protected $notCoregCampaigns = [4, 5, 6];

    public $creativeContent;

    public $html = '';

    /**
     * Initialize
     *
     * @var object
     */
    public function __construct(
        Utils\Content\CreativeContent $creativeContent
    ) {
        $this->creativeContent = $creativeContent;
    }

    /**
     * Check wiether the request contaigns campaign ids
     */
    public function hasCampaigns(): bolean
    {
        return (count($this->campaigns)) ? true : false;
    }

    /**
     * Check wiether the request falls in any certain type
     */
    public function hasCampaignType(): bolean
    {
        return ($this->campaignType) ? true : false;
    }

    /**
     * Count the campaign ids in a request
     */
    public function campaignCount(): int
    {
        return count($this->campaigns);
    }

    /**
     * Set campaigns
     */
    public function setCampaigns(array $campaigns): void
    {
        $this->campaigns = $campaigns;
        if ($this->hasCampaigns()) {
            $this->setCampaignType();
        }
    }

    /**
     * Set affiliate id
     */
    public function setAffiliateID(string $affiliateID): void
    {
        $this->affiliateID = $affiliateID;
    }

    /**
     * Set session
     */
    public function setSession(string $session): void
    {
        $this->session = $session;
    }

    /**
     * Set path
     */
    public function setPath(string $path): void
    {
        $this->path = $path;
    }

    /**
     * Set the initial data needed to process campaigns content
     *
     * @param  Illuminate\Http\Request | string  $request
     */
    public function setInitialVariables($request): void
    {
        $this->determineRequestType($request);

        // If exist, overwrite default global variables
        if (array_key_exists('campaigns', $this->inputs)) {
            $this->setCampaigns($this->inputs['campaigns']);
        }
        if (array_key_exists('affiliate_id', $this->inputs)) {
            $this->setAffiliateID($this->inputs['affiliate_id']);
        } else {
            $this->setAffiliateID(1);
        }
        if (array_key_exists('session', $this->inputs)) {
            $this->setLaravelSession($this->inputs['session']);
        }
        if (array_key_exists('path', $this->inputs)) {
            $this->setPath($this->inputs['path']);
        }

        // Determine the campaign type if hve campaigns
        if ($this->hasCampaigns()) {
            $this->setCampaignType();
        }

        // Process creatives if available
        if ($this->hasCampaignType() && array_key_exists('creatives', $this->inputs)) {
            if (count($this->inputs['creatives']) > 0) {
                $this->creativeContent->setCreativeIDS($this->inputs['creatives']);
            }
        }
    }

    /**
     * Add submit button if required
     */
    public function addButton($submitBtnText = 'Submit')
    {
        // Add button if rquirements met
        if (in_array($this->campaignType, $this->typesThatStacked)) {
            $this->html .= '<div align="center"><button id="submit_stack_button" type="button" class="submit_button_form" align="absmiddle">'.$submitBtnText.'</button></div>';
        }
    }

    /**
     * Get the html content of all campaign ids
     */
    public function getHtmlData(): int
    {
        if (! $this->creativeContent->emptyContent) {
            return $this->html;
        }

        return ['message' => trans('campaignList.dont_exist')];
    }

    /**
     * Process of STACK Campaign Content
     *
     * @return int|empty string $limit
     * @return bolean
     */
    public function process(
        $userDetails = '',
        $limit = '')
    {
        // Log::info($campaigns);
        $this->stackContents = CampaignContent::whereIn('id', $this->campaigns)->pluck('stack', 'id');

        // go through campaigns
        collect($this->campaigns)->each(function ($campaignID) use ($limit) {
            $this->html .= "\n\r".'<!-------------- BOF Campaign: '.$campaignID.'-------------->'."\n\r";
            $this->html .= '<div class="individual_campaigns">';

            $this->stackContent($campaignID);
            $this->addSeparator();

            $this->html .= '</div>';
            // Save user views
            $this->saveCampaignView($campaignID);

            $this->html .= "\n\r".'<!-------------- EOF Campaign: '.$campaignID.'-------------->'."\n\r";
            $this->campaignCounter++;
            // break the loop if limit is reach
            if ($limit && $this->campaignCounter == $limit) {
                return false;
            }
        });

        // Save user views per campaign type
        $this->saveCampaignTypeView();

        // Return true if needs to break the parent loop when limit is reach
        if (($limit && $this->campaignCounter == $limit)
            // Or break when all campaigns are run through
            || $this->campaignCounter == $this->campaignCount()
        ) {
            // Replace campaign contents with user details
            $this->html = ContentReplaceable::process($userDetails, $this->html);

            return true;
        }
        // continue parent looping
        return false;
    }

    /**
     * Set the campaign type if available
     */
    protected function setCampaignType()
    {
        if (($cmp = Campaign::select('campaign_type')->whereIn('id', $this->campaigns)->first()) != null) {
            $this->campaignType = $cmp->campaign_type;
        }
    }

    /**
     * Stack the contents of each campaigns
     */
    protected function stackContent($campaignID)
    {
        // get campaign content
        $content = ($this->stackContents->has($campaignID)) ? $this->stackContents[$campaignID] : '';

        /* For Creative Rotation */
        if ($this->creativeContent->campaignHasCreativeID($campaignID)) {
            $this->stackContent = $this->creativeContent->get($content, $campaignID);
            /* For Path ID: ADD PATH ID*/
            $this->addPathIdFieldInForm();
            //Sent a creative id. Probably for previewing a content
            $this->html .= $this->stackContent;

            return;
        }

        //check if has [VALUE_CREATIVE_DESCRIPTION] OR [VALUE_CREATIVE_IMAGE]
        if (strpos($content, '[VALUE_CREATIVE_DESCRIPTION]') !== false
            || strpos($content, '[VALUE_CREATIVE_IMAGE]') !== false
        ) {
            $this->stackContent = '';
            /* For Path ID: ADD PATH ID*/
            $this->addPathIdFieldInForm();
            $this->html .= $this->stackContent;

            return;
        }

        $this->creativeContent->emptyContent = false;
        $this->stackContent = $content;
        /* For Path ID: ADD PATH ID*/
        $this->addPathIdFieldInForm();
        $this->html .= $this->stackContent;
    }

    /**
     * Saved the campaign view
     */
    protected function saveCampaignView($campaignID)
    {
        if ($this->stackContent != '' && $this->affiliateID != '' && $this->session != '') {
            // $viewDetails = [
            //     'campaign_id'   => $campaignID,
            //     'affiliate_id'  => $this->affiliateID,
            //     'session'       => $this->session,
            //     'path_id'       => $this->path,
            //     'creative_id'   => $this->creativeContent->campaignCreativeID($campaignID)
            // ];
            // $job = (new SaveCampaignView($viewDetails))->delay(5);
            //$this->dispatch($job);

            CampaignView::create([
                'campaign_id' => $campaignID,
                'affiliate_id' => $this->affiliateID,
                'session' => $this->session,
                'path_id' => $this->path,
                'creative_id' => $this->creativeContent->campaignCreativeID($campaignID),
            ]);

            $report = CampaignViewReport::firstOrNew([
                'campaign_id' => $campaignID,
                'revenue_tracker_id' => $this->affiliateID,
            ]);

            if ($report->exists) {
                $report->total_view_count = $report->total_view_count + 1;
                $report->current_view_count = $report->current_view_count + 1;
            } else {
                $report->total_view_count = 1;
                $report->current_view_count = 1;
            }

            $report->campaign_type_id = $this->campaignType;
            $report->save();
        }
    }

    /**
     * Saved the campaign type view
     */
    protected function saveCampaignTypeView()
    {
        // $job = (new SaveCampaignTypeView(
        //         $this->campaignType,
        //         $this->affiliateID,
        //         $this->session,
        //         Carbon::now()
        //     )
        // )->delay(5);
        // $this->dispatch($job);
        //

        CampaignTypeView::UpdateOrCreate(['campaign_type_id' => $this->campaignType], [
            'campaign_type_id' => $this->campaignType,
            'revenue_tracker_id' => $this->affiliateID,
            'session' => $this->session,
            'timestamp' => Carbon::now(),
        ]);

        $report = CampaignTypeReport::firstOrNew([
            'campaign_type_id' => $this->campaignType,
            'revenue_tracker_id' => $this->affiliateID,
        ]);

        if ($report->exists) {
            $report->views = $report->views + 1;
        } else {
            $report->views = 1;
        }

        $report->save();
    }

    /**
     * Add path id in campaign
     */
    public function addPathIdFieldInForm()
    {
        if (! in_array($this->campaignType, $this->notCoregCampaigns) && $this->stackContent != '') { //if campaign is coreg
            $html = $this->stackContent;
            if (strpos($html, '[VALUE_PATH_ID]') === false) {
                //If no path id shortcode, add
                if (strpos($html, '<!-- Standard Required Data END-->') !== false) {
                    $formArray = explode('<!-- Standard Required Data END-->', $html);
                    $formArray[0] .= '<input type="hidden" name="eiq_path_id" value="[VALUE_PATH_ID]"/>';
                    $formArray[0] .= '<!-- Standard Required Data END-->';
                    $html = implode(' ', $formArray);
                    $this->stackContent = $html;

                    return;
                }

                $formArray = explode('</form>', $html);
                $formArray[0] .= '<input type="hidden" name="eiq_path_id" value="[VALUE_PATH_ID]"/>';
                $formArray[0] .= '</form>';
                $html = implode(' ', $formArray);
            }
            $this->stackContent = $html;
        }
    }

    /**
     * Add separator for each campaign content
     */
    protected function addSeparator()
    {
        if (in_array($this->campaignType, $this->typesThatStacked) && $this->stackContent != '') {
            $this->html .= '<div class="separator" style=""></div>';
        }
    }

    /**
     * Determine the rquest is object or string
     */
    protected function determineRequestType($request): collection
    {
        // Determine the type of data
        if (! is_object($request)) {
            // String to array
            parse_str($request, $requestUri);
            // To collection
            $request = collect($requestUri);
        }

        $this->inputs = $request->all();
    }
}
