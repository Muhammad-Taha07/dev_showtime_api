<?php

namespace App\Http\Requests\AuthRequest;

use Illuminate\Validation\Rules\Password;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
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
            'email' =>  'required|email|exists:users,email',
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
            'email.email'   =>  ':attribute should be in email form',
            'email.exists'  =>  'Account does not exist',
        ];
    }

}
