<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EcrDetailRequest extends FormRequest
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
            // 'description_of_change' => 'required',
            // 'reason_of_change' => 'required',
            'change_imp_date' => 'required',
            'type_of_part' => 'required',
            'doc_sub_date' => 'required',
            'doc_to_be_sub' => 'required',
        ];
    }
}
