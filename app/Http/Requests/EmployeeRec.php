<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmployeeRec extends FormRequest
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
            // Alphanumeric employee code like MPD-0128-272
            'emp_code' => ['nullable','string','max:64','unique:employees,emp_code','regex:/^[A-Za-z0-9]+(?:-[A-Za-z0-9]+)*$/'],
            'name' => 'required|string|min:3|max:64|alpha_dash',
            'position' => 'required|string|min:3|max:64|alpha_dash',
            'schedule' => 'required|exists:schedules,slug',
        ];
    }
}
