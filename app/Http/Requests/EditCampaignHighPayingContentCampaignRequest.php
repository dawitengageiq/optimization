<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EditCampaignHighPayingContentCampaignRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
'name'        => [
                'required',
            ],
'description' => [
                'required',
            ],
'sticker'     => [
                'required',
            ],
'deal'        => [
                'required',
            ],
];
    }
}
