<?php

namespace App\Http\Requests\Auth;

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
     */
    public function rules(): array
    {
        return [
            'phone_number' => 'required|string|regex:/^[0-9+\s\-()]+$/|min:10|max:20',
            'code' => 'required|string|size:6|regex:/^[0-9]+$/',
            'password' => 'required|string|min:6',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'phone_number.required' => 'Phone number is required',
            'phone_number.regex' => 'Invalid phone number format',
            'phone_number.min' => 'Phone number must be at least 10 characters',
            'phone_number.max' => 'Phone number must not exceed 20 characters',
            'code.required' => 'Verification code is required',
            'code.size' => 'Verification code must be exactly 6 digits',
            'code.regex' => 'Verification code must contain only numbers',
            'password.required' => 'Password is required',
            'password.min' => 'Password must be at least 6 characters',
        ];
    }
}
