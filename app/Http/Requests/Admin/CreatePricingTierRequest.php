<?php

namespace App\Http\Requests\Admin;

use App\Enums\ServiceType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreatePricingTierRequest extends FormRequest
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
            'min_tokens' => 'required|integer|min:1',
            'discount_percent' => 'required|numeric|min:0|max:100',
            'price_per_1k_tokens' => 'nullable|numeric|min:0',
        ];
    }
}
