<?php

namespace App\Http\Requests\Admin;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
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
        $userId = $this->route('id');

        return [
            'name' => 'sometimes|string|max:255',
            'phone_number' => [
                'sometimes',
                'string',
                'max:20',
                Rule::unique('users')->ignore($userId),
            ],
            'role' => [
                'sometimes',
                Rule::in(UserRole::values()),
            ],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $user = $this->route('id') ? \App\Models\User::find($this->route('id')) : null;
            $currentUser = $this->user();

            if ($user && $currentUser && isset($this->role) && $currentUser->id === $user->id) {
                $validator->errors()->add('role', 'You cannot change your own role.');
            }
        });
    }
}
