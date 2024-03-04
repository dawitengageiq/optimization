<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Validator;

class AffiliateRequest extends FormRequest
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
        Validator::extend('filter_validate_url', function ($attribute, $value, $parameters) {
            if (preg_match('/^(http|https):\\/\\/[a-z0-9_]+([\\-\\.]{1}[a-z_0-9]+)*\\.[_a-z]{2,5}'.'((:[0-9]{1,5})?\\/.*)?$/i', $value)) {
                return true;
            } else {
                return false;
            }
        });

        return [
            'company' => 'required|max:100',
            'website' => 'required|filter_validate_url',
            'phone' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/',
            'address' => 'required',
            'city' => 'required',
            'state' => 'required|alpha|max:2',
            'zip' => 'required|numeric|min:5',
            'status' => 'required',
            'type' => 'required',
        ];
    }

    public function messages(): array
    {
        return [
            'website.filter_validate_url' => 'Website url should be valid.',
        ];
    }
}
