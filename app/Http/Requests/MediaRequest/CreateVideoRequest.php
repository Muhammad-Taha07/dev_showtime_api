<?php

namespace App\Http\Requests\MediaRequest;

use Illuminate\Foundation\Http\FormRequest;

class CreateVideoRequest extends FormRequest
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
            'title'         => 'required|min:3',
            'description'   => 'required',
            'file'         => 'required|file|mimetypes:video/mp4,audio/mpeg|max:51200'
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
            '*.required'        => ':attribute is required',
            'title.min'         => ':attribute should be minimum of :min characters',
            'file.file'        => 'The :attribute must be a file.',
            'file.mimetypes'   => 'The :attribute must be a video of type: mp4/audio.',
            'file.max'         => 'The :attribute may not be greater than 50 MB in size.',
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
            'title'       => 'Title',
            'description' => 'Description',
            'file'       =>  'Media File',
        ];
    }
}
