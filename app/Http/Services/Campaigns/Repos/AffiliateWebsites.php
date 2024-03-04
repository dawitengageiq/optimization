<?php

namespace App\Http\Services\Campaigns\Repos;

use App\WebsitesViewTrackerDuplicate;

final class AffiliateWebsites
{
    /**
     * Default variables
     */
    protected $affiliateWebsite;

    protected $isRegistered = false;

    protected $isDisabled = true;

    protected $emailIsUnique = true;

    protected $model;

    /**
     * Intantiate, dependency injection of model
     */
    public function __construct(\Illuminate\Database\Eloquent\Model $model, \Illuminate\Database\Eloquent\Model $dupeModel)
    {
        $this->model = $model;
        $this->dupeModel = $dupeModel;
    }

    /**
     * Check if registered
     *
     * @param  int  $websiteID
     * @param  int  $affiliateID
     * @param  string  $email
     * @param  int  $timeInterval
     */
    public function pluck($websiteID, $affiliateID, $email, $timeInterval)
    {
        $this->email = $email;
        $this->websiteID = $websiteID;
        $this->timeInterval = $timeInterval;

        if ($this->affiliateWebsite = $this->model->where([
            'id' => $websiteID,
            'affiliate_id' => $affiliateID,
        ])->first()
        ) {
            $this->isRegistered = true;
            $this->isDisabled = ($this->affiliateWebsite->status == 0) ? true : false;

            if ($this->email) {
                $this->trackView();
            }
        }
    }

    /**
     * Check if it is registered
     *
     * @return bolean
     */
    public function isRegistered()
    {
        return $this->isRegistered;
    }

    /**
     * Check if disabled
     *
     * @return bolean
     */
    public function isDisabled()
    {
        return $this->isDisabled;
    }

    /**
     * Check if email is unique
     *
     * @return bolean
     */
    public function isEmailUnique()
    {
        return $this->emailIsUnique;
    }

    /**
     * Track user view
     *
     * @param  string  $email
     * @param  int  $websiteID
     */
    protected function trackView()
    {
        // Track view by email
        // If duplicate save in WebsitesViewTrackerDuplicate.
        if (
            $this->affiliateWebsite->view()->track(
                $this->email,
                $this->websiteID,
                $this->affiliateWebsite->payout,
                $this->timeInterval
            ) !== true) {
            $this->affiliateWebsite->dupe()->save(new WebsitesViewTrackerDuplicate(['website_id' => $this->websiteID, 'email' => $this->email]));
            $this->emailIsUnique = false;
        }
    }
}
