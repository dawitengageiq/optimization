<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EditCampaignAffiliateCampaignRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
'edit_lead_cap_type'  => [
                'required',
            ],
'edit_lead_cap_value' => [
                'numeric',
            ],
'selected_affiliate'  => [
                'required',
            ],
];
    }
}
