<?php

namespace App\Http\Requests\AuthRequest;

use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
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
            'password'   =>  [
                'required',
                Password::min(8)->mixedCase()->symbols()
            ],
        ];
    }

    public function messages(): array
    {
        return [
            '*.required'    =>  ':attribute is required',
        ];
    }

    public function attributes(): array
    {
        return [
            'password'  =>  'Password',
        ];
    }
}
