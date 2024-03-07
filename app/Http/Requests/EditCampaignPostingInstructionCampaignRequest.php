<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EditCampaignPostingInstructionCampaignRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
'sample_code'         => 'required|max:65533',
'posting_instruction' => 'required|max:65533',
];
    }
}
