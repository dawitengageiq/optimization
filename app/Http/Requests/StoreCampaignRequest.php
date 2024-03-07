<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCampaignRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
    'name'             => [
                'required',
            ],
    'advertiser'       => [
                'required',
                'exists:advertisers,id',
            ],
    'lead_type'        => [
                'required',
            ],
    'lead_value'       => [
                'required_unless:lead_type,0',
                'numeric',
            ],
    // 'default_payout'    => 'required|numeric',
    // 'default_received'  => 'required|numeric',
    //'image'             => 'image|mimes:jpeg,bmp,png',
    //'description'   => 'required',
    'status'           => [
                'required',
            ],
    'campaign_type'    => [
                'required',
            ],
    'category'         => [
                'required',
            ],
    'linkout_offer_id' => [
                'required_if:campaign_type,5',
                'numeric',
            ],
    'program_id'       => [
                'numeric',
            ],
];
    }
}
