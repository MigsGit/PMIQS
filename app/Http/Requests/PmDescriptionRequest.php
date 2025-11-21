<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PmDescriptionRequest extends FormRequest
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
            'itemNo.0' => ['required','integer', 'min:1'],
            'partcodeType.0' => ['required'],
            'descriptionItemName.0' => ['required'],
            'matSpecsLength.0' => ['required', 'integer', 'min:0'],
            'matSpecsWidth.0' => ['required', 'integer', 'min:0'],
            'matSpecsHeight.0' => ['required', 'integer', 'min:0'],
            'matRawType.0' => ['required', 'integer', 'min:0'],
            'matRawThickness.0' => ['required', 'integer', 'min:0'],
            'matRawWidth.0' => ['required', 'integer', 'min:0'],
        ];
    }
    public function messages()
    {
        return [
            'itemNo.0.required' => 'The Item No is required.',
            'partcodeType.0.required' => 'The Partcode Type is required.',
            'descriptionItemName.0.required' => 'The Description ItemName By is required.',
            'matSpecsLength.0.required' => 'The MatSpecsLength By is required.',
            'matSpecsWidth.0.required' => 'The MatSpecsWidth By is required.',
            'matSpecsHeight.0.required' => 'The MatSpecsHeight is required.',
            'matRawType.0.required' => 'The MatRawType is required.',
            'matRawThickness.0.required' => 'The MatRawThickness is required.',
            'matRawWidth.0.required' => 'The MatRawWidth is required.',
        ];
    }

}
