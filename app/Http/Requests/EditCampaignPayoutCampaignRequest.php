<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EditCampaignPayoutCampaignRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
'edit_payout_payable'    => 'required|numeric',
'edit_payout_receivable' => 'required|numeric',
'selected_payout'        => 'required',
];
    }
}
