<?php

namespace App\Http\Requests\AuthRequest;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class SignupRequest extends FormRequest
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
            'first_name' =>     'required|min:3|max:15|string',
            'last_name'  =>     'required|min:3|max:15|string',
            'email'      =>     'required|email|unique:users',
            'password'   =>  [
                'required',
                Password::min(8)->mixedCase()->symbols()
            ],
            'confirm_password'  => 'required|same:password',
            // 'current_time'      => 'required|date_format:Y-m-d H:i:s',
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
            '*.required'            =>  'Required fields cannot be left empty',
            'first_name.min'        =>  ':attribute should be between :min and :max character',
            'first_name.max'        =>  ':attribute should be between :min and :max character',
            'last_name.max'         =>  ':attribute should be between :min and :max character',
            'last_name.max'         =>  ':attribute should be between :min and :max character',
            'email.unique'          =>  ':attribute already exists',
            'confirm_password.same' =>  ':attribute does not match the password.',
            'password.regex'        =>  ':attribute must be at least 8 characters long and include at least one uppercase letter, one lowercase letter, and one special symbol.',
        ];
    }

    public function attributes(): array
    {
        return [
            'first_name'        =>      'First Name',
            'last_name'         =>      'Last Name',
            'password'          =>      'Password',
            'confirm_password'  =>      'Confirm password field',
            // 'current_time'      =>      'Current time',
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
