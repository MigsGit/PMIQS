<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PmiExternalApprovalRequest extends FormRequest
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
            'external_prepared_by.0' => ['required', 'integer', 'min:1'], // index 0 must be = 1
            'external_checked_by.0' => ['required', 'integer', 'min:1'],
            'external_approved_by.0' => ['required', 'integer', 'min:1'],
        ];
    }
    public function messages()
    {
        return [
            'external_prepared_by.0.min' => 'The QC Head is required.',
            'external_checked_by.0.min' => 'The Section Head is required.',
            'external_approved_by.0.min' => 'The QA Head is required.',
        ];
    }
}
