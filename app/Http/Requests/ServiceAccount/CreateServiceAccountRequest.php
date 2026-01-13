<?php

namespace App\Http\Requests\ServiceAccount;

use Illuminate\Foundation\Http\FormRequest;

class CreateServiceAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'initial_balance' => ['sometimes', 'numeric', 'min:0'],
            'currency' => ['sometimes', 'string', 'size:3'],
            'credit_limit' => ['sometimes', 'numeric', 'min:0'],
            'metadata' => ['sometimes', 'array'],
        ];
    }
}
