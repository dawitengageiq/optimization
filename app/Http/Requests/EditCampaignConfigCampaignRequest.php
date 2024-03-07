<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EditCampaignConfigCampaignRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
'post_url'              => 'required|url',
'post_header'           => 'required',
'post_data'             => 'required',
'post_data_map'         => 'required',
'post_method'           => 'required',
'post_success'          => 'required',
'post_data_fixed_value' => 'required',
];
    }
}
