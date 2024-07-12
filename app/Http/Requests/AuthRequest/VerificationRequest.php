<?php

namespace App\Http\Requests\AuthRequest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class VerificationRequest extends FormRequest
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
            'email'     =>      'required|email',
            'verify_code'   =>  'required|integer|digits:4',
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
            '*.required'    =>  ':attribute is required',
            'verify_code.integer'   =>  ':attribute must be numbers ',
            'verify_code.digits'    =>  ':attribute must be of :digits digits',
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
            'email'         =>   'Email',
            'verify_code'   =>   'OTP Code',
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
