<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CampaignConfigInterfaceCampaignRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
'post_url'     => [
                'required',
                'url',
            ],
'post_method'  => [
                'required',
            ],
'post_success' => [
                'required',
            ],
];
    }
}
