<?php

namespace App\Http\Requests\AuthRequest;

use Illuminate\Foundation\Http\FormRequest;

class ForgotPasswordRequest extends FormRequest
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
            'email' =>  'required|email'
        ];
    }

    public function messages(): array
    {
        return [
            '*.required'    =>  ':attribute is required',
            'email.email'   =>  ':attribute should be in email format',
        ];
    }

    public function attributes(): array
    {
        return [
            'email' =>  'Email Address',
        ];
    }
}
