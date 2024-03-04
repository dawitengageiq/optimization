<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RevenueTrackerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'website' => 'required',
            'affiliate' => 'required|integer',
            'campaign' => 'required|integer',
            'offer' => 'required|integer',
            'revenue_tracker' => 'required|integer',
            // 's1'                => 'integer',
            // 's2'                => 'integer',
            // 's3'                => 'integer',
            // 's4'                => 'integer',
            // 's5'                => 'integer',
            'link' => 'required|url',
            // 'notes'             => 'required',
            'crg_limit' => 'integer',
            'ext_limit' => 'integer',
            'lnk_limit' => 'integer',
        ];
    }
}
