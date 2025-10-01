<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MaterialRequest extends FormRequest
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
            "pd_material" => 'required',
            "msds" => 'required',
            "icp" => 'required',
            "gp" => 'required',
            "qoutation" => 'required',
            "material_sample" => 'required',
            "material_supplier" => 'required',
            "material_color" => 'required',
            "coc" => 'required',
            "rohs" => 'required',
        ];
    }
}
