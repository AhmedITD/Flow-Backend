<?php

namespace App\Http\Requests\Payment;

use Illuminate\Foundation\Http\FormRequest;

class InitiatePaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by auth middleware
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'sometimes|string|size:3',
            'description' => 'sometimes|string|max:500',
            'return_url' => 'sometimes|url',
            'callback_url' => 'sometimes|url',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'amount.required' => 'Payment amount is required',
            'amount.numeric' => 'Payment amount must be a number',
            'amount.min' => 'Payment amount must be at least 0.01',
            'currency.size' => 'Currency code must be exactly 3 characters',
            'description.max' => 'Description must not exceed 500 characters',
            'return_url.url' => 'Return URL must be a valid URL',
            'callback_url.url' => 'Callback URL must be a valid URL',
        ];
    }
}
