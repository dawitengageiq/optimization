<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EditWebsiteAffiliateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
'website_name'       => [
                'required',
            ],
'website_payout'     => [
                'required',
                'numeric',
            ],
'revenue_tracker_id' => [
                'required',
                'numeric',
            ],
];
    }
}
