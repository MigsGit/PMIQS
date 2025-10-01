<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MachineFileRequest extends FormRequest
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

            'machineRefBefore.*' => 'required|file|mimes:jpeg,png|max:2048',
            'machineRefAfter.*' => 'required|file|mimes:jpeg,png|max:2048',
            'ecrsId' => 'required',
            'machinesId' => 'required',
        ];

    }
}
