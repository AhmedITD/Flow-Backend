<?php

namespace App\Policies;

use App\Models\User;
use App\Models\ServiceAccount;

class ServiceAccountPolicy
{
    /**
     * Admin can view any service account.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Users can view their own, admins can view any.
     */
    public function view(User $user, ServiceAccount $serviceAccount): bool
    {
        return $user->isAdmin() || $user->id === $serviceAccount->user_id;
    }

    /**
     * Users can create their own service account.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Only admin can manually adjust balances.
     */
    public function adjustBalance(User $user, ServiceAccount $serviceAccount): bool
    {
        return $user->isAdmin();
    }

    /**
     * Only admin can suspend/close accounts.
     */
    public function changeStatus(User $user, ServiceAccount $serviceAccount): bool
    {
        return $user->isAdmin();
    }
}
