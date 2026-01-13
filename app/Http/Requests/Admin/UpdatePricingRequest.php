<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePricingRequest extends FormRequest
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
            'price_per_1k_tokens' => 'sometimes|numeric|min:0',
            'min_tokens' => 'sometimes|integer|min:1',
            'effective_until' => 'nullable|date',
        ];
    }
}
