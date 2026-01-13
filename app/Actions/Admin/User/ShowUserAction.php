<?php

namespace App\Actions\Admin\User;

use App\Models\User;

final class ShowUserAction
{
    /**
     * Show a specific user with related data.
     */
    public function execute(string $id): User
    {
        return User::with([
            'serviceAccount',
            'apiKeys' => fn($q) => $q->latest()->limit(5),
            'payments' => fn($q) => $q->latest()->limit(10),
        ])->findOrFail($id);
    }
}
