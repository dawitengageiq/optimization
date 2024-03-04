<?php

namespace App\Helpers\Repositories;

use App\Setting;

class Settings implements SettingsInterface
{
    public function getValue($code)
    {
        $setting = Setting::where('code', $code)->first();
        if ($setting->string_value != null) {
            $value = $setting->string_value;
        } elseif ($setting->integer_value != null) {
            $value = $setting->integer_value;
        } elseif ($setting->double_value != null) {
            $value = $setting->double_value;
        } elseif ($setting->date_value != null) {
            $value = $setting->date_value;
        } else {
            $value = null;
        }

        return $value;
    }

    public function getKeyByCode()
    {
        $setting = Setting::select(['code', 'string_value', 'integer_value'])
            ->get()
            ->keyBy('code');

        return $setting;
    }
}
