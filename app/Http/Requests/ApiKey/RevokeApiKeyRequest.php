<?php

namespace App\Http\Requests\ApiKey;

use Illuminate\Foundation\Http\FormRequest;

class RevokeApiKeyRequest extends FormRequest
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
            // No validation needed for revoke endpoint
        ];
    }
}
