<?php

namespace App\Http\Requests\UserRequest;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProfile extends FormRequest
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
            'gender'  => [
                'required',
                'string',
                Rule::in(["male", "female"])
            ],
            'user_age'  =>  'required',
        ];
    }

    public function messages(): array
    {
        return [
            '*.required'    =>  ':attribute is required',
            'gender.in'     =>  'The :attribute field must be either "male" or "female".',
        ];
    }

    public function attributes(): array
    {
        return [
            'gender'    =>  'Gender',
            'user_age'  =>  'User Age',
        ];
    }
}
