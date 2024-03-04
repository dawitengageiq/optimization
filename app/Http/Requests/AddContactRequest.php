<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Validator;

class AddContactRequest extends FormRequest
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
        Validator::extend('affiliate_advertiser_chosen', function ($attribute, $value, $parameters) {

            $inputs = $this->all();

            if ($inputs['affiliate_id'] == 0 && $inputs['advertiser_id'] == 0) {
                return false;
            }

            return true;

        });

        return [
            'affiliate_id' => 'affiliate_advertiser_chosen',
            'advertiser_id' => 'affiliate_advertiser_chosen',
            'first_name' => 'required',
            //'middle_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email|unique:users',
            'mobile_number' => 'numeric',
            'phone_number' => 'numeric',
            'password' => 'required|confirmed|min:5',
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'Email is already taken as a affiliate/advertiser contact or as a user.',
            'affiliate_id.affiliate_advertiser_chosen' => 'You need to assign this contact to affiliate or advertiser.',
            'advertiser_id.affiliate_advertiser_chosen' => 'You need to assign this contact to affiliate or advertiser.',
        ];
    }

    protected function getValidatorInstance()
    {
        $inputs = $this->all();

        if (! isset($inputs['affiliate_id'])) {
            //$this->merge(['affiliate_id' => 0]);
            $inputs['affiliate_id'] = 0;
        }

        if (! isset($inputs['advertiser_id'])) {
            //$this->merge(['advertiser_id' => 0]);
            $inputs['advertiser_id'] = 0;
        }

        $this->getInputSource()->replace($inputs);

        return parent::getValidatorInstance();
    }
}
