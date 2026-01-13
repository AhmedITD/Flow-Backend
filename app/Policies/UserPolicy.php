<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Admin can view any user.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Users can view their own profile, admins can view any.
     */
    public function view(User $user, User $model): bool
    {
        return $user->isAdmin() || $user->id === $model->id;
    }

    /**
     * Only admin can create users directly.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Users can update their own profile, admins can update any.
     */
    public function update(User $user, User $model): bool
    {
        return $user->isAdmin() || $user->id === $model->id;
    }

    /**
     * Only admin can delete users.
     */
    public function delete(User $user, User $model): bool
    {
        // Admin can delete, but not themselves
        return $user->isAdmin() && $user->id !== $model->id;
    }

    /**
     * Only admin can manage user roles.
     */
    public function manageRole(User $user, User $model): bool
    {
        return $user->isAdmin() && $user->id !== $model->id;
    }
}
