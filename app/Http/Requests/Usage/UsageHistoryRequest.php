<?php

namespace App\Http\Requests\Usage;

use Illuminate\Foundation\Http\FormRequest;

class UsageHistoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'service_type' => ['sometimes', 'string', 'in:call_center,hr'],
            'action_type' => ['sometimes', 'string'],
            'from' => ['sometimes', 'date'],
            'to' => ['sometimes', 'date', 'after_or_equal:from'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }
}
