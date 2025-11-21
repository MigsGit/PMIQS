<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PmClassificationRequest extends FormRequest
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
            'descriptionsId.0' => ['required', 'integer', 'min:1'],
            'classification.0' => ['required'],
            'qty.0' => ['required', 'integer', 'min:0'],
            'uom.0' => ['required'],
            'unitPrice.0' => ['required', 'integer', 'min:0'],
        ];
    }
    public function messages()
    {
        return [
            'descriptionsId.0' => 'The Descriptions Id is required.',
            'classification.0' => 'The Classification By is required.',
            'qty.0' => 'The Qty By is required.',
            'uom.0' => 'The Uom By is required.',
            'unitPrice.0' => 'The UnitPrice By is required.',
        ];
    }
}
