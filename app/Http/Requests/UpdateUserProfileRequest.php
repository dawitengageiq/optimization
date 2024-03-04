<?php

namespace App\Http\Requests;

use App\User;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUserProfileRequest extends FormRequest
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
        $validationEmailRule = 'required|email';

        $inputs = $this->all();
        $user = User::find($inputs['id']);

        if ($user->email != $inputs['email']) {
            $validationEmailRule = 'required|email|unique:users';
        }

        return [
            'first_name' => 'required',
            'middle_name' => 'required',
            'last_name' => 'required',
            'email' => $validationEmailRule,
            'mobile_number' => 'numeric',
            'phone_number' => 'numeric',
            //'password' => 'required|confirmed|min:5' //this is commented because admin should not be able to change any user password
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'Email is already taken as a user or as a affiliate/advertiser contact.',
        ];
    }
}
