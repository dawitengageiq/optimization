<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLeadUserBannedRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
'first_name' => [
                'required',
            ],
'last_name'  => [
                'required',
            ],
'email'      => [
                'required_without_all:phone',
                'email',
            ],
'phone'      => [
                'required_without_all:email',
                'numeric',
            ],
];
    }
}
