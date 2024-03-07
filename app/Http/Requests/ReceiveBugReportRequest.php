<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReceiveBugReportRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'bug_summary' => [
                'required',
            ],
            'bug_description' => [
                'required',
            ],
        ];
    }
}
