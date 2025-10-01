<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EcrRequest extends FormRequest
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
            'ecr_no'=> 'required',
            'category'=> 'required',
            'internal_external'=> 'required',
            'customer_name'=> 'required',
            'part_no'=> 'required',
            'part_name'=> 'required',
            'device_name'=> 'required',
            'product_line'=> 'required',
            'section'=> 'required',
            'customer_ec_no'=> 'required',
            'date_of_request'=> 'required',
        ];
    }
}
