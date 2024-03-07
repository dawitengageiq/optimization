<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePathRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
'name' => [
                'required',
                'unique:paths,name,' . $id,
                'max:100',
            ],
'url'  => [
                'required',
                'unique:paths,url,' . $id,
                'max:255',
                'url',
            ],
];
    }
}
