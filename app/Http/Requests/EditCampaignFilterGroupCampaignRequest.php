<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EditCampaignFilterGroupCampaignRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'filter_group_name' => [
                'required',
            ],
            'filter_group_status' => [
                'required',
            ],
        ];
    }
}
