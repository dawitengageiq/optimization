<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLeadUserBannedRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
'first_name' => 'required',
'last_name'  => 'required',
'email'      => 'email|required_without_all:phone',
'phone'      => 'numeric|required_without_all:email',
];
    }
}
