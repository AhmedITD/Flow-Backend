<?php

namespace App\Http\Requests\Usage;

use Illuminate\Foundation\Http\FormRequest;

class CalculateUsageCostRequest extends FormRequest
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
            'service_type' => 'nullable|in:call_center,hr',
        ];
    }
}
