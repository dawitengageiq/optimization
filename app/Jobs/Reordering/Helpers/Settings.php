<?php

namespace App\Jobs\Reordering\Helpers;

class Settings
{
    /**
     * Default variables
     */
    protected $settings;

    public function __construct(\App\Setting $model)
    {
        $this->settings = $model->select(['code', 'string_value', 'integer_value', 'description'])->get()->keyBy('code')->toArray();
    }

    public function stringValue($code)
    {
        return $this->settings[$code]['string_value'];
    }

    public function integerValue($code)
    {
        return $this->settings[$code]['integer_value'];
    }

    public function description($code)
    {
        return $this->settings[$code]['description'];
    }
}
