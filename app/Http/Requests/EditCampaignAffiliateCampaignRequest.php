<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EditCampaignAffiliateCampaignRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
'edit_lead_cap_type'  => 'required',
'edit_lead_cap_value' => 'numeric',
'selected_affiliate'  => 'required',
];
    }
}
