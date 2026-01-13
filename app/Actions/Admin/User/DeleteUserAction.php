<?php

namespace App\Actions\Admin\User;

use App\Models\User;

final class DeleteUserAction
{
    /**
     * Delete a user.
     */
    public function execute(User $user, User $currentUser): void
    {
        // Prevent admin from deleting themselves
        if ($currentUser->id === $user->id) {
            throw new \Exception('You cannot delete your own account.');
        }

        // Check if user has active service accounts with balance
        $serviceAccount = $user->serviceAccount;
        if ($serviceAccount && $serviceAccount->balance > 0) {
            throw new \Exception('Cannot delete user with positive balance. Please refund first.');
        }

        $user->delete();
    }
}
