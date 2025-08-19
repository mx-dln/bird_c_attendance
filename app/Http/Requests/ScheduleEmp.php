<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ScheduleEmp extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'slug' => 'required|string|min:3|max:32|alpha_dash',
            'time_in_am' => 'required|date_format:H:i|before:time_out_am',
            'time_out_am' => 'required|date_format:H:i',
            'time_in_pm' => 'required|date_format:H:i|before:time_out_pm',
            'time_out_pm' => 'required|date_format:H:i',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'time_in_am' => 'time in AM',
            'time_out_am' => 'time out AM',
            'time_in_pm' => 'time in PM',
            'time_out_pm' => 'time out PM',
        ];
    }
}
