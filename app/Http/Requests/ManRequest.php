<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ManRequest extends FormRequest
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
            'ecrs_id' => 'required',
            'first_assign' => 'required',
            'long_interval' => 'required',
            'change' => 'required',
            'process_name' => 'required',
            'working_time' => 'required',
            'qc_inspector_operator' => 'required',
            'trainer' => 'required',
            'lqc_supervisor' => 'required',
        ];
    }
}
