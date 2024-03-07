<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCampaignRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
            ],
            'advertiser' => [
                'required',
            ],
            'lead_type' => [
                'required',
            ],
            'lead_value' => [
                'required_unless:lead_type,0',
                'numeric',
            ],
            'priority' => [
                'required',
            ],
            'default_payout' => [
                'sometimes',
                'numeric',
            ],
            'default_received' => [
                'sometimes',
                'numeric',
            ],
            'status' => [
                'required',
            ],
            'campaign_type' => [
                'required',
            ],
            'category' => [
                'required',
            ],
            'linkout_offer_id' => [
                'required_if:campaign_type,5',
                'numeric',
            ],
            'program_id' => [
                'numeric',
            ],
        ];
    }
}
