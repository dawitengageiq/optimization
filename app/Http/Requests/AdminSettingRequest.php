<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Request;
use Validator;

class AdminSettingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        Validator::extend('num_greater_than', function ($attribute, $value, $parameters) {
            $other = Request::get($parameters[0]);

            return isset($other) and intval($value) > intval($other);
        });

        // Validator::extend('num_less_than', function($attribute, $value, $parameters) {
        //     $other = Request::get($parameters[0]);
        //     return isset($other) and intval($value) < intval($other);
        // });

        return [
            'send_pending_lead_cron_expiration' => 'required|integer',
            // 'number_of_campaign_per_stack'      => 'required|integer',
            'campaign_filter_process_status' => 'required',
            'leads_archiving_age_in_days' => 'required|integer',
            'min_high_reject_rate' => 'required|numeric|max:99',
            'max_high_reject_rate' => 'required|numeric|max:99|num_greater_than:min_high_reject_rate',
            'num_leads_to_process_for_send_pending_leads' => 'required|integer',
            'campaign_type_view_count' => 'required|integer',
            'user_nos_before_not_displaying_campaign' => 'required|integer',
            // 'min_critical_reject_rate'          => 'required|numeric|max:99.9|num_greater_than:max_high_reject_rate',
            // 'max_critical_reject_rate'          => 'required|numeric|max:100|num_greater_than:min_critical_reject_rate',
        ];
    }

    public function messages(): array
    {
        return [
            'max_high_reject_rate.num_greater_than' => 'Maximum percentage for high rejection rate should be greater than the minimum percentage.',
            // 'min_critical_reject_rate.num_greater_than' =>   'Minimum percentage for critical rejection rate should be greater than the maximum percentage for high rejection rate.',
            // 'max_critical_reject_rate.num_greater_than' =>   'Maximum percentage for critical rejection rate should be greater than the critical percentage.',
        ];
    }
}
