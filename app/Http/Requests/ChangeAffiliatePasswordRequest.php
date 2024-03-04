<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChangeAffiliatePasswordRequest extends FormRequest
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
        Validator::extend('existing_password_match', function ($attribute, $value, $parameters) {

            $userEmail = Auth::user()->email;

            return Auth::once(['email' => $userEmail, 'password' => $value]);
        });

        return [
            'existing_password' => 'required|old_password_match',
            'password' => 'required|confirmed|min:5',
        ];
    }
}
