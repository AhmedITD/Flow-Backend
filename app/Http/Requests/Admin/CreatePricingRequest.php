<?php

namespace App\Http\Requests\Admin;

use App\Enums\ServiceType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreatePricingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'service_type' => ['required', Rule::in(ServiceType::values())],
            'price_per_1k_tokens' => 'required|numeric|min:0',
            'min_tokens' => 'sometimes|integer|min:1',
            'currency' => 'sometimes|string|size:3',
            'effective_from' => 'required|date',
            'effective_until' => 'nullable|date|after:effective_from',
        ];
    }
}
