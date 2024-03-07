<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EditCampaignPayoutCampaignRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'edit_payout_payable' => [
                'required',
                'numeric',
            ],
            'edit_payout_receivable' => [
                'required',
                'numeric',
            ],
            'selected_payout' => [
                'required',
            ],
        ];
    }
}
