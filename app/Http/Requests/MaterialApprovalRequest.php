<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MaterialApprovalRequest extends FormRequest
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
            'ppc_approved_by' => 'required',
            'ppc_checked_by' => 'required',
            'ppc_prepared_by' => 'required',

            'pr_approved_by' => 'required',
            'pr_checked_by' => 'required',
            'pr_prepared_by' => 'required',

            'ems_prepared_by' => 'required',
            'ems_checked_by' => 'required',
            'ems_approved_by' => 'required',

            'qc_prepared_by' => 'required',
            'qc_checked_by' => 'required',
            'qc_approved_by' => 'required',

            'qa_prepared_by' => 'required',
            'qa_checked_by' => 'required',
            'qa_approved_by' => 'required',
        ];
    }
}
