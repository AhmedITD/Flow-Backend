<?php

namespace App\Actions\Admin\User;

use App\Models\User;

final class UpdateUserAction
{
    /**
     * Update a user.
     */
    public function execute(User $user, array $data, User $currentUser): User
    {
        // Prevent admin from changing their own role
        if (isset($data['role']) && $currentUser->id === $user->id) {
            throw new \Exception('You cannot change your own role.');
        }

        $user->update($data);

        return $user->fresh();
    }
}
