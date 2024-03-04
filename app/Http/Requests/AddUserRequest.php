<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddUserRequest extends FormRequest
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
            'first_name' => 'required',
            'middle_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email|unique:users',
            'role_id' => 'required',
            'mobile_number' => 'numeric',
            'phone_number' => 'numeric',
            'password' => 'required|confirmed|min:5',
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'Email is already taken as a user or as a affiliate/advertiser contact.',
        ];
    }
}
