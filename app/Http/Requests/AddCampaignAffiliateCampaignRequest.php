<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddCampaignAffiliateCampaignRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'lead_cap_type' => [
                'required',
            ],
            'lead_cap_value' => [
                'numeric',
            ],
        ];
    }
}
