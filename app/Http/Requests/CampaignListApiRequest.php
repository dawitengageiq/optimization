<?php

namespace App\Http\Requests;

use Illuminate\Http\Response;
use View;

class CampaignListApiRequest extends CampaignListRequest
{
    /**
     * Default variables
     */
    protected $message = '';

    protected $redirectUrl = '';

    protected $reloadParentFrame = false;

    protected $targetUrl = 'http://leadreactor.engageiq.com/';

    protected $browser = [
        'os' => '',
        'os_version' => '',
        'browser' => '',
        'browser_version' => '',
        'user_agent' => '',
    ];

    protected $device = [
        'isMobile' => '',
        'isTablet' => '',
        'isDesktop' => '',
        'type' => '',
    ];

    protected $required = [
        'affiliate_id',
        'website_id',
        'first_name',
        'last_name',
        'email',
        'zip',
        'dobmonth',
        'dobday',
        'dobyear',
        'gender',
    ];

    protected $incomplete = false;

    /**
     * Retrieve browser details
     */
    public function browserDetails(): array
    {
        return $this->browser;
    }

    /**
     * Retrieve device details
     */
    public function deviceDetails(): array
    {
        return $this->device;
    }

    /**
     * Retrieve redirect url
     */
    public function redirectUrl(): string
    {
        return $this->redirectUrl;
    }

    /**
     * True:Reload parent iframe/self irame
     * False:Reload window top
     */
    public function toReloadParentFrame(): bolean
    {
        return $this->reloadParentFrame;
    }

    /**
     * Environment url
     */
    public function targetUrl(): bolean
    {
        return $this->targetUrl;
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Set data needed for error page if ....
        $this->setErrorPageData();

        if ($this->incompleteRequestData()) {
            $this->message = trans('campaignList.incomplete_parameters');

            return false;
        }

        if ($this->invalidEmailFormat()) {
            $this->message = trans('campaignList.invalid_email_format');

            return false;
        }

        if (! $this->get('affiliate_websites')['is_registered']) {
            $this->message = trans('campaignList.access_denied');

            return false;
        }

        if ($this->get('affiliate_websites')['is_disabled']) {
            $this->message = trans('campaignList.list_disabled');

            return false;
        }

        // Set data for campaign list
        $this->setProperties();

        return true;
    }

    /**
     * Get the response for a forbidden operation.
     */
    public function forbiddenResponse(): Response
    {
        View::share('data', ['message' => $this->message]);
        View::share('redirect_url', $this->redirectUrl());
        View::share('reload_parent_frame', $this->toReloadParentFrame());

        return response()->view('api.campaign_list');
    }

    /**
     * Validate email have correct email format.
     */
    protected function invalidEmailFormat(): bolean
    {
        if (! filter_var($this->get('email'), FILTER_VALIDATE_EMAIL)) {
            return true;
        }

        return false;
    }

    /**
     * [setErrorPageData description]
     *
     * @method setErrorPageData
     *
     * @param  [type]
     */
    protected function setErrorPageData()
    {
        // Save to global variable
        if ($this->has('redirect_url')) {
            $this->redirectUrl = $this->get('redirect_url');
        }
        if ($this->has('reload_parent_frame')) {
            $this->reloadParentFrame = $this->get('reload_parent_frame');
        }
    }

    /**
     * [setProperties description]
     *
     * @method setProperties
     *
     * @param  [type]
     */
    protected function setProperties()
    {
        // Save to global variable
        if ($this->has('target_url')) {
            $this->targetUrl = $this->get('target_url');
        }
        if ($this->has('filter')) {
            $this->filter = $this->get('filter');
        }
        // $this->settings = $this->get('settings');
        $this->limit = $this->get('limit');

        // Remove some indexes in $user_details,
        // Precautions: Might destroy the function of creating excel(excel: not sure if this feature did exixts, :D)
        $this->filterUserdata();
        $this->filterBrowserdata();
        $this->filterDevicedata();
    }

    /**
     * Update browser details with request data
     *
     * @var array
     */
    protected function filterBrowserdata()
    {
        $this->browser = collect($this->browser)->map(function ($detail, $key) {
            return ($this->has($key)) ? $this->get($key) : $detail;
        })->toArray();
    }

    /**
     * Update device details with request data
     *
     * @var array
     */
    protected function filterDevicedata()
    {
        $this->device = collect($this->device)->map(function ($detail, $key) {
            return ($this->has($key)) ? $this->get($key) : $detail;
        })->toArray();
    }
}
