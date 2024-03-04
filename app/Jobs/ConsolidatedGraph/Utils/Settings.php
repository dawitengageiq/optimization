<?php

namespace App\Jobs\ConsolidatedGraph\Utils;

final class Settings
{
    /**
     * Default variables
     */
    protected $settings = [];

    protected $benchmarks = [];

    protected $campaignTypes = [];

    /**
     * Intantiate, dependency injection of model
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     */
    public function __construct(\App\Setting $model)
    {
        $this->campaignTypes = config('constants.CAMPAIGN_TYPES');

        $this->settings = $model->select(['code', 'string_value', 'integer_value', 'description'])->get()->keyBy('code')->toArray();

        $this->setBenchmarks();
    }

    /**
     * Retrieve all settings
     */
    public function details()
    {
        return $this->settings;
    }

    /**
     * Set Benchmarks,benchmarks has campaign id per campaign type
     */
    public function setBenchmarks()
    {
        if (array_key_exists('campaign_type_benchmarks', $this->settings)) {
            $this->benchmarks = json_decode($this->settings['campaign_type_benchmarks']['description'], true);
        } else {
            $this->benchmarks = [];
        }
    }

    /**
     * Retrieve path limit
     */
    public function campaignTypeBenchmarks()
    {
        return $this->benchmarks;
    }

    /**
     * Get the benchmark of a campagn type
     *
     * @return integet|empty
     */
    public function benchmark($campaignType)
    {
        if ($campaignID = array_search($campaignType, $this->campaignTypes)) {
            return (array_key_exists($campaignID, $this->benchmarks)) ? $this->benchmarks[$campaignID] : '';
        }
    }

    /**
     * Admin email
     *
     * @return string
     */
    public function adminEmail()
    {
        if (array_key_exists('default_admin_email', $this->settings)) {
            return $this->settings['default_admin_email']['string_value'];
        }

        return '';
    }
}
