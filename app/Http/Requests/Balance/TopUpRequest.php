<?php

namespace App\Http\Requests\Balance;

use Illuminate\Foundation\Http\FormRequest;

class TopUpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:1000'], // Minimum 1000 IQD
            'currency' => ['sometimes', 'string', 'size:3'],
            'description' => ['sometimes', 'string', 'max:255'],
            'return_url' => ['sometimes', 'url'],
            'callback_url' => ['sometimes', 'url'],
            'metadata' => ['sometimes', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'amount.min' => 'Minimum top-up amount is 1000 IQD',
        ];
    }
}
