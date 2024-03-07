<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePathRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
'name' => [
                'required',
                'unique:paths',
                'max:100',
            ],
'url'  => [
                'required',
                'unique:paths',
                'max:255',
                'url',
            ],
];
    }
}