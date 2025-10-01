<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EcrApprovalRequest extends FormRequest
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
            'requested_by' => ['required', 'array'],
            'requested_by.0' => ['required', 'integer', 'min:1'], // index 0 must be = 1

            'technical_evaluation' => ['required', 'array'],
            'technical_evaluation.0' => ['required', 'integer', 'min:1'],

            // 'reviewed_by' => ['required', 'array'],
            // 'reviewed_by.0' => ['required', 'integer', 'min:1'],

            // 'qad_approved_by_external' => ['required', 'integer', 'min:1'], // index 0 must be = 1
            'qad_checked_by' => ['required', 'integer', 'min:1'],
            'qad_approved_by_internal' => ['required', 'integer', 'min:1'],

        ];
    }
    public function messages()
    {
        return [
            'requested_by.0.min' => 'The Requested By is required.',
            'technical_evaluation.0.min' => 'The first Technical Evaluation is required.',
            // 'reviewed_by.0.min' => 'The Reviewed By / Section Heads is required.',
            // 'qad_checked_by.0.required' => 'The QA Engg is required.',
            // 'qad_approved_by_internal.0.required' => 'The QA Manager is required.',
            'qad_checked_by.min' => 'The QA Engg is required',
            'qad_approved_by_internal.min'  => 'The QA Manager is required',

            // '*.0.min' => 'The approver is required.',
            // '*.min' => 'Please choose Approver..',
        ];
    }
    /*

     */
}
