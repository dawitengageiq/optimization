<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GetDashboardGraphsStatisticsProcessorAdminRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
'from_date' => 'required_with:to_date|date',
'to_date'   => 'required_with:from_date|date|date_greater_equal:from_date',
];
    }
}
