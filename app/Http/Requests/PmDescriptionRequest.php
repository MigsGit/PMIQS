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

            itemNo
            partcodeType
            descriptionItemName
            matSpecsLength
            matSpecsWidth
            matSpecsHeight
            matRawType
            matRawThickness
            matRawWidth
        ];
    }
    public function rules()
    {
        return [
            'itemNo.0' => ['required','integer', 'min:1'],
            'partcodeType.0' => ['required'],
            'descriptionItemName.0' => ['required'],
            // 'matSpecsLength.0' => ['required', 'integer', 'min:1'],
            // 'matSpecsWidth.0' => ['required', 'integer', 'min:1'],
            // 'matSpecsHeight.0' => ['required', 'integer', 'min:1'],
            // 'matRawType.0' => ['required', 'integer', 'min:1'],
            // 'matRawThickness.0' => ['required', 'integer', 'min:1'],
            // 'matRawWidth.0' => ['required', 'integer', 'min:1'],
        ];
    }
    public function messages()
    {
        return [
            'itemNo.0' => 'The Item No is required.',
            'partcodeType.0' => 'The Partcode Type is required.',
            'descriptionItemName.0' => 'The Description ItemName By is required.',
            // 'matSpecsLength.0' => 'The Prepared By is required.',
            // 'matSpecsWidth.0' => 'The Checked By is required.',
            // 'matSpecsHeight.0' => 'The Approved By is required.',
            // 'matRawType.0' => 'The Prepared By is required.',
            // 'matRawThickness.0' => 'The Checked By is required.',
            // 'matRawWidth.0' => 'The Approved By is required.',
        ];
    }

}
