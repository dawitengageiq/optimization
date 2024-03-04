<?php

namespace App\Http\Services\Campaigns\Repos;

final class Settings
{
    /**
     * Default variables
     */
    protected $settings;

    /**
     * Intantiate, dependency injection of model
     */
    public function __construct(\App\Setting $model)
    {
        $this->model = $model;
    }

    public function self()
    {
        return $this;
    }

    public function get()
    {
        $this->settings = $this->model->select(['code', 'string_value', 'integer_value', 'description'])->get()->keyBy('code')->toArray();
    }

    public function hasSettings()
    {
        if ($this->settings) {
            return true;
        }

        return false;
    }

    public function setSettings($settings)
    {
        $this->settings = $settings;
    }

    /**
     * Retrieve all settings
     */
    public function details()
    {
        return $this->settings;
    }

    /**
     * Retrieve path limit
     */
    public function hasPathTypeLimit()
    {
        if (! $this->settings['campaign_type_path_limit']['description']) {
            return false;
        }

        return true;
    }

    /**
     * Retrieve path limit
     */
    public function pathTypeLimit()
    {
        return $this->settings['campaign_type_path_limit']['description'];
    }

    /**
     * Retrieve campaign type ordering
     */
    public function campaignTypeOrder()
    {
        return $this->settings['stack_path_campaign_type_order']['string_value'];
    }

    /**
     * Retrieve number of no limit
     */
    public function campaignNoLimit()
    {
        return $this->settings['user_nos_before_not_displaying_campaign']['integer_value'];
    }

    /**
     * Retrieve filter process ststus
     */
    public function filterProcessStatus()
    {
        return $this->settings['campaign_filter_process_status']['integer_value'];
    }
}
