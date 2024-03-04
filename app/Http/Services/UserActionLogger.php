<?php

namespace App\Http\Services;

use Log;
use Illuminate\Support\Facades\Auth;
class UserActionLogger
{
    protected $user;

    protected $section_types;

    public function __construct()
    {
        $this->user = Auth::user() ;
        $this->section_types = config('constants.USER_ACTION_SECTION_TYPE');
    }

    public function logger($section, $sub_section, $reference_id, $old_value, $new_value, $user_summary = null, $callback = null)
    {
        $disregard = [
            'created_at',
            'updated_at',
        ];

        if (is_callable($callback)) {
            [$old_value, $new_value] = $callback($old_value, $new_value);
        }

        $reference = $old_value;
        if ($reference == null) {
            $reference = $new_value;
        }

        //key
        foreach ($reference as $key => $val) {
            if (in_array($key, $disregard)) {
                unset($old_value[$key]);
                unset($new_value[$key]);
            } elseif (strpos($key, '_') !== false) {
                $new_key = str_replace('_', ' ', $key);
                if (isset($old_value[$key])) {
                    //old value
                    $old_value[$new_key] = $val;
                    unset($old_value[$key]);
                }
                if (isset($new_value[$key])) {
                    //new value
                    $new_value[$new_key] = $new_value[$key];
                    unset($new_value[$key]);
                }
            }
        }

        // Log::info($old_value);
        // Log::info($new_value);

        $section_name = isset($this->section_types[$section]) ? $this->section_types[$section] : '';
        if ($sub_section != null) {
            $section_name = isset($this->section_types[$sub_section]) ? $this->section_types[$sub_section] : $section_name;
        }

        if ($old_value == null || $new_value == null) {
            $summary = '';
            $severity_id = 2;
            if ($old_value == null && $new_value != null) { //Add
                $summary = 'Add ';
            } elseif ($old_value != null && $new_value == null) { //Delete
                $summary = 'Delete ';
                $severity_id = 3;
            }

            if ($user_summary != null && $user_summary != '') {
                $summary = $user_summary;
            } else {
                $summary .= $section_name.'.';
            }

            event(new \App\Events\UserActionEvent([
                'section_id' => $section,
                'sub_section_id' => $sub_section,
                'user_id' => $this->user->id,
                'reference_id' => $reference_id,
                'change_severity' => $severity_id,
                'summary' => $summary,
                'old_value' => $old_value == null ? null : json_encode($old_value),
                'new_value' => $new_value == null ? null : json_encode($new_value),
            ]));

        } else { //Update Values

            $updated = [];
            $summary = 'Update '.$section_name.'. Column: ';

            foreach ($old_value as $name => $value) {
                if ($value != $new_value[$name]) {
                    // Log::info("NEW: $name : $value => $new_value[$name]");

                    if ($user_summary != null && $user_summary != '') {
                        $update_summary = $user_summary.'. Column: '.$name.'.';
                    } else {
                        $update_summary = $summary.$name.'.';
                    }

                    $updated[] = [
                        'section_id' => $section,
                        'sub_section_id' => $sub_section,
                        'user_id' => $this->user->id,
                        'reference_id' => $reference_id,
                        'change_severity' => 2,
                        'summary' => $update_summary,
                        'old_value' => $value,
                        'new_value' => $new_value[$name],
                    ];
                }
            }

            if (count($updated) > 0) {
                event(new \App\Events\UserActionEvent($updated));
            }
        }
    }

    public function log($section, $sub_section, $reference_id, $user_summary, $old_value, $new_value, $key_mask = null, $value_mask = null)
    {

        $disregard = [
            'created_at',
            'updated_at',
        ];

        // Log::info($old_value);
        // Log::info($new_value);

        $reference = $old_value;
        if ($reference == null) {
            $reference = $new_value;
        }

        //value mask
        if ($value_mask != null) {
            foreach ($reference as $key => $val) {
                if (isset($value_mask[$key])) {
                    // Log::info("VM: $key => $val");
                    // if(is_array($old_value) && in_array($key, $old_value))
                    if (isset($old_value[$key])) {
                        // Log::info($old_value[$key]);
                        // Log::info($value_mask[$key]);
                        // Log::info($value_mask[$key][$old_value[$key]]);
                        $old_value[$key] = $value_mask[$key][$old_value[$key]];
                    }
                    // if(is_array($new_value) && in_array($key, $new_value))
                    if (isset($new_value[$key])) {
                        $new_value[$key] = $value_mask[$key][$new_value[$key]];
                        // Log::info("VM: $new_value[$key] => $value_mask[$key][$new_value[$key]]");
                    }
                }
            }
        }

        // Log::info($old_value);
        // Log::info($new_value);

        $reference = $old_value;
        if ($reference == null) {
            $reference = $new_value;
        }

        //key mask
        foreach ($reference as $key => $val) {
            if (! in_array($key, $disregard)) {
                if (is_array($key_mask) && array_key_exists($key, $key_mask)) {
                    if (is_array($old_value) && array_key_exists($key, $old_value)) {
                        $old_value[$key_mask[$key]] = $old_value[$key];
                        // Log::info("KMN - OV: $key => $new_key");
                        unset($old_value[$key]);
                    }

                    if (is_array($new_value) && array_key_exists($key, $new_value)) {
                        $new_value[$key_mask[$key]] = $new_value[$key];
                        // Log::info("KMN - NV: $key => $new_key");
                        unset($new_value[$key]);
                    }
                } elseif (strpos($key, '_') !== false) {
                    // Log::info("KM: $key => $val");
                    $new_key = str_replace('_', ' ', $key);

                    if (is_array($old_value) && array_key_exists($key, $old_value)) {
                        $old_value[$new_key] = $old_value[$key];
                        // Log::info("KMN - OV: $key => $new_key");
                        unset($old_value[$key]);
                    }

                    if (is_array($new_value) && array_key_exists($key, $new_value)) {
                        $new_value[$new_key] = $new_value[$key];
                        // Log::info("KMN - NV: $key => $new_key");
                        unset($new_value[$key]);
                    }
                }
            }
        }

        // Log::info($old_value);
        // Log::info($new_value);

        $section_name = isset($this->section_types[$section]) ? $this->section_types[$section] : '';
        if ($sub_section != null) {
            $section_name = isset($this->section_types[$sub_section]) ? $this->section_types[$sub_section] : $section_name;
        }

        if ($old_value == null || $new_value == null) {
            $summary = '';
            $severity_id = 2;
            if ($old_value == null && $new_value != null) { //Add
                $summary = 'Add ';
            } elseif ($old_value != null && $new_value == null) { //Delete
                $summary = 'Delete ';
                $severity_id = 3;
            }

            if ($user_summary != null && $user_summary != '') {
                $summary = $user_summary;
            } else {
                $summary .= $section_name.'.';
            }

            event(new \App\Events\UserActionEvent([
                'section_id' => $section,
                'sub_section_id' => $sub_section,
                'user_id' => $this->user->id,
                'reference_id' => $reference_id,
                'change_severity' => $severity_id,
                'summary' => $summary,
                'old_value' => $old_value == null ? null : json_encode($old_value),
                'new_value' => $new_value == null ? null : json_encode($new_value),
            ]));

        } else { //Update Values

            $updated = [];
            $summary = 'Update '.$section_name.'. Column: ';
            foreach ($old_value as $name => $value) {
                if (! in_array($name, $disregard)) {
                    // Log::info("$name => $value : $new_value[$name]");
                    if ($value != $new_value[$name]) {
                        // Log::info("NEW: $name : $value => $new_value[$name]");

                        if ($user_summary != null && $user_summary != '') {
                            $update_summary = $user_summary.'. Column: '.$name.'.';
                        } else {
                            $update_summary = $summary.$name.'.';
                        }

                        $updated[] = [
                            'section_id' => $section,
                            'sub_section_id' => $sub_section,
                            'user_id' => Auth::id(),
                            'reference_id' => $reference_id,
                            'change_severity' => 2,
                            'summary' => $update_summary,
                            'old_value' => $value,
                            'new_value' => $new_value[$name],
                        ];
                    }
                }
            }

            if (count($updated) > 0) {
                event(new \App\Events\UserActionEvent($updated));
            }
        }
    }

    /**
     * Create user action log entry array
     *
     * @param $user
     */
    public function createUserActionLogEntry($settingCode, $oldValue, $newValue): ?array
    {
        if ($oldValue != $newValue) {
            return [
                'section_id' => 9,
                'sub_section_id' => null,
                'user_id' => $this->user->id,
                'change_severity' => 2,
                'summary' => "$settingCode is changed.",
                'old_value' => $oldValue,
                'new_value' => $newValue,
            ];
        } else {
            return null;
        }
    }
}
