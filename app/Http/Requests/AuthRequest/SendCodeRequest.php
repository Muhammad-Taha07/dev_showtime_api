<?php

namespace App\Http\Requests\AuthRequest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class SendCodeRequest extends FormRequest
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
            'email' => 'required|email|exists:users,email'
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
            '*.required'    => ':attribute is required',
            'email.email'   => 'Please enter a valid :attribute',
            'email.exists'  =>  ':attribute does not exist',
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
            'email' =>  'Email Address',
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
