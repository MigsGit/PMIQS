<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PmiApprovalRequest extends FormRequest
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
            'prepared_by.0' => ['required', 'integer', 'min:1'], // index 0 must be = 1
            'checked_by.0' => ['required', 'integer', 'min:1'],
            'approved_by.0' => ['required', 'integer', 'min:1'],
        ];
    }
    public function messages()
    {
        return [
            'prepared_by.0.min' => 'The Prepared By is required.',
            'checked_by.0.min' => 'The Checked By is required.',
            'approved_by.0.min' => 'The Approved By is required.',
        ];
    }

}
