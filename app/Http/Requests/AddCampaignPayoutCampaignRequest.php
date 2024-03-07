<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddCampaignPayoutCampaignRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'this_campaign' => [
                'required',
            ],
            'payout_receivable' => [
                'required',
                'numeric',
            ],
            'payout_payable' => [
                'required',
                'numeric',
            ],
            'payout' => [
                'required',
            ],
        ];
    }
}
