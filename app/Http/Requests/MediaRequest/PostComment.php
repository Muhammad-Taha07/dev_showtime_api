<?php

namespace App\Http\Requests\MediaRequest;

use Illuminate\Foundation\Http\FormRequest;

class PostComment extends FormRequest
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
            'media_id'  => 'required|exists:media_collections,id',
            'comment'   => 'required|string',
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
            '*.required'        =>   ':attribute is required',
            'comment.string'    =>   ':attribute should be in string',
            'media_id.exists'   =>   'The selected :attribute is not found.',
        ];
    }

/**
* Get custom attributes for validator errors.
*
* @return array
*/
    public function attributes(): array
    {
        return [
            'comment'   =>  'Comment',
            'media_id'  =>  'Media File',
        ];
    }
}
