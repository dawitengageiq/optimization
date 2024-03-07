<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddCampaignFilterGroupCampaignRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
'filter_group_name'   => 'required|not_in:' . $campaign_filter_groups,
'filter_group_status' => 'required',
];
    }
}
