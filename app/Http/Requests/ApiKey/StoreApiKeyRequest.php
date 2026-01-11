<?php

namespace App\Http\Requests\ApiKey;

use Illuminate\Foundation\Http\FormRequest;

class StoreApiKeyRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'environment' => 'nullable|in:live,test',
            'services' => 'nullable|array',
            'services.*' => 'required|in:call_center,hr',
            'expires_at' => 'nullable|date|after:now',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'API key name is required',
            'name.max' => 'API key name must not exceed 255 characters',
            'environment.in' => 'Environment must be either "live" or "test"',
            'services.array' => 'Services must be an array',
            'services.*.in' => 'Each service must be either "call_center" or "hr"',
            'expires_at.date' => 'Expiration date must be a valid date',
            'expires_at.after' => 'Expiration date must be in the future',
        ];
    }
}
