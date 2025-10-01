<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SpecialInspectionRequest extends FormRequest
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
            "ecrs_id" => 'required',
            'product_detail' => 'required',
            'lot_qty' => 'required',
            'samples' => 'required',
            'mod' => 'required',
            'mod_qty' => 'required',
            'judgement' => 'required',
            'inspection_date' => 'required',
            'inspector' => 'required',
            'lqc_section_head' => 'required',
            'remarks' => 'required',
        ];
    }
}
