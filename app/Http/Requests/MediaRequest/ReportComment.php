<?php

namespace App\Http\Requests\MediaRequest;

use Illuminate\Foundation\Http\FormRequest;

class ReportComment extends FormRequest
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
            'comment_id'    =>  'required|integer',
        ];
    }

    public function messages(): array
    {
        return [
            '*.required'         =>  ':attribute is required!',
            'comment_id.integer' =>  ':attribute should be number',
            // 'comment_id.exists'  =>  ':attribute not found!',
        ];
    }

    public function attributes(): array
    {
        return [
            'comment_id'    =>   'Comment ID',
        ];
    }
}
