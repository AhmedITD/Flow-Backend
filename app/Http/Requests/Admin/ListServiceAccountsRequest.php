<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ListServiceAccountsRequest extends FormRequest
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
            'per_page' => 'sometimes|integer|min:1|max:100',
            'status' => 'sometimes|string|in:active,suspended,closed',
            'min_balance' => 'sometimes|numeric|min:0',
            'max_balance' => 'sometimes|numeric|min:0',
        ];
    }
}
