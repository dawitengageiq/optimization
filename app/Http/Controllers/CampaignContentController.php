<?php

namespace App\Http\Controllers;

use ApiConfig;
use Illuminate\Http\Request;

class CampaignContentController extends Controller
{
    /**
     * Injected class
     */
    protected $campaignStack;

    protected $userDetails = '';

    /**
     * Initialize
     *
     * @var object
     */
    public function __construct(\App\Http\Services\Campaigns\Content $campaignStack)
    {
        $this->campaignStack = $campaignStack;
    }

    /**
     * Get the content of campaign stack
     */
    public function surveyStack(Request $request)
    {
        // Overwrite default if user details is available
        if ($request->has('user_details')) {
            $this->setUserDetails($request->all());
        }

        // Set the initial variables needed
        $this->campaignStack->setInitialVariables($request);

        // Check if has campaigns and campaign type
        if ($this->campaignStack->hasCampaigns() && $this->campaignStack->hasCampaignType()) {
            // Get the stack content of each campaign

            // $this->campaignStack->process($this->userDetails, ApiConfig::displayLimit());
            $this->campaignStack->process($this->userDetails);

            // Return the html if no user details, requesting sites will process the replacing of content code
            if (! $this->userDetails) {
                return $this->campaignStack->getHtmlData();
            }

            // Evaluate php since the content code was replace by user details
            [$gender, $one_letter_gender, $title, $age] = $this->varNeededB4Eval();

            eval('?>'.$this->campaignStack->getHtmlData());

            return;
        }

        // Return message if no campaigns
        return ['message' => trans('campaignList.dont_exist')];
    }

    /**
     * Set user details that are needed
     */
    protected function setUserDetails(array $userDetails)
    {
        // birtdate
        $userDetails['dobyear'] = '';
        $userDetails['dobmonth'] = '';
        $userDetails['dobday'] = '';
        if ($userDetails['birthdate']) {
            [$userDetails['dobyear'], $userDetails['dobmonth'], $userDetails['dobday']] = explode('-', $userDetails['birthdate']);
        }
        // Phone
        $userDetails['phone1'] = substr($userDetails['phone'], 0, 3);
        $userDetails['phone2'] = substr($userDetails['phone'], 3, 3);
        $userDetails['phone3'] = substr($userDetails['phone'], 6, 4);

        $this->userDetails = $userDetails;
    }

    /**
     * Variables needed befor evaluation
     */
    protected function varNeededB4Eval(): array
    {
        return [
            ($this->userDetails['gender'] == 'F') ? 'Female' : 'Male',                          // gender
            $this->userDetails['gender'],                                                       // one letter gender
            ($this->userDetails['gender'] == 'M') ? 'Mr.' : 'Ms.',                              // title
            date_diff(date_create($this->userDetails['birthdate']), date_create('today'))->y,    // age
        ];
    }
}
