<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddCampaignFilterGroupCampaignRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
'filter_group_name'   => [
                'required',
                'not_in:' . $campaign_filter_groups,
            ],
'filter_group_status' => [
                'required',
            ],
];
    }
}
