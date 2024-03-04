<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Request;
use Validator;

class CampaignFilterRequest extends FormRequest
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
        Validator::extend('num_greater_than', function ($attribute, $value, $parameters) {
            $other = Request::get($parameters[0]);

            return isset($other) and intval($value) >= intval($other);
        });

        Validator::extend('less_than', function ($attribute, $value, $parameters) {
            $other = Request::get($parameters[0]);

            return isset($other) and intval($value) <= intval($other);
        });

        Validator::extend('date_greater_equal', function ($attribute, $value, $parameters) {

            $max = Carbon::parse($value);
            $min = Carbon::parse(Request::get($parameters[0]));

            // var_dump($max->gte($min));

            // $other = Request::get($parameters[0]);
            return isset($min) and $max->gte($min);
        });

        return [
            'filter_type' => 'required',
            'value_type' => 'required',
            'filter_value_01_text' => 'required_if:value_type,1',
            'filter_value_01_select' => 'required_if:value_type,2|boolean',
            'filter_value_01_date' => 'required_if:value_type,3|date',
            'filter_value_02_date' => 'required_if:value_type,3|date|date_greater_equal:filter_value_01_date',
            'filter_value_01_input' => 'required_if:value_type,4|numeric|',
            'filter_value_02_input' => 'required_if:value_type,4|numeric|num_greater_than:filter_value_01_input',
            'filter_value_01_array' => 'required_if:value_type,5',
            'filter_value_01_time' => 'required_if:value_type,6',
            'filter_value_02_time' => 'required_if:value_type,6|date_greater_equal:filter_value_01_time',
        ];
    }

    public function messages(): array
    {
        return [
            'filter_type.required' => 'Filter Type is required.',
            'value_type.required' => 'Value Type is required.',

            'filter_value_01_text.required_if' => 'Text value is required.',
            'filter_value_01_select.required_if' => 'Boolean value is required.',

            'filter_value_01_date.required_if' => 'Minimum Date is required.',
            'filter_value_02_date.required_if' => 'Maximum Date is required.',
            'filter_value_01_date.date' => 'Minimum Date should be a date.',
            'filter_value_02_date.date' => 'Maximum Date should be a date.',
            'filter_value_02_date.date_greater_equal' => 'Maximum Date should be greater than or equal to Minimum Date.',

            'filter_value_01_input.required_if' => 'Minimum Number is required.',
            'filter_value_02_input.required_if' => 'Maximum Number is required.',
            'filter_value_01_input.numeric' => 'Minimum Number should be a number.',
            'filter_value_02_input.numeric' => 'Maximum Number should be a number.',
            'filter_value_02_input.num_greater_than' => 'Maximum Number should be greater than or equal to Minimum Number.',

            'filter_value_01_time.required_if' => 'Minimum Time is required.',
            'filter_value_02_time.required_if' => 'Maximum Time is required.',
            'filter_value_02_time.date_greater_equal' => 'Maximum Time should be greater than equal to Minimum Time.',

        ];
    }
}
