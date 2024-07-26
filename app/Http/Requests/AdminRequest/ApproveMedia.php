<?php

namespace App\Http\Requests\AdminRequest;

use Illuminate\Foundation\Http\FormRequest;

class ApproveMedia extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

/**
 * Get the validation rules that apply to the request.
 *
 * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
 */
    public function rules(): array
    {
        return [
            'media_id' => 'required|exists:media_collections,id',
        ];
    }

/**
* Get the error messages for the defined validation rules.
*
* @return array
*/
    public function messages(): array
    {
        return [
            '*.required'      =>    ':attribute is required',
            'media_id.exists' =>    ':attribute does not exist',
        ];
    }
    
/**
* Get the error attributes for the defined validation fields.
*
* @return array
*/
    public function attributes(): array
    {
        return [
            'media_id'  =>  'Media File',
        ];
    }
}
