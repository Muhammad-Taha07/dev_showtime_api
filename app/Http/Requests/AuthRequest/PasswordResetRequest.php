<?php

namespace App\Http\Requests\AuthRequest;

use Illuminate\Validation\Rules\Password;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class PasswordResetRequest extends FormRequest
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
            'email'         => 'required|email|exists:users,email',
            'password'      =>  [
                'required',
                Password::min(8)->mixedCase()->symbols()
            ],
        ];
    }

/**
 * Get the validation rules that apply to the request.
 *
 * @return array
 */
    public function messages(): array
    {
        return [
            '*.required'                => ':attribute cannot be left empty',
            'email.email'               => 'Please enter a valid :attribute',
            '*.min'                     => ':attribute must be at least 8 characters',
        ];
    }

/**
* Get the error messages for the defined validation rules.
*
* @return array
*/
    public function attributes(): array
    {
        return [
            'email'         => 'Email Address',
            'password'      => 'Password',
        ];
    }

/**
*
* @return json
*/
    public function failedValidation(Validator $validator)
    {
        $error = [
            "status"    =>  400,
            "success"   =>  false,
            "message"   =>  $validator->errors()->first()
        ];

        throw new HttpResponseException(response()->json($error, 400));
    }
}
