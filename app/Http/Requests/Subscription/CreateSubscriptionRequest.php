<?php

namespace App\Http\Requests\Subscription;

use Illuminate\Foundation\Http\FormRequest;

class CreateSubscriptionRequest extends FormRequest
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
            'plan_id' => 'required|uuid|exists:plans,id',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'plan_id.required' => 'Plan ID is required',
            'plan_id.uuid' => 'Plan ID must be a valid UUID',
            'plan_id.exists' => 'Selected plan does not exist',
        ];
    }
}
