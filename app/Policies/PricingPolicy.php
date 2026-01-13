<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Pricing;

class PricingPolicy
{
    /**
     * Anyone can view pricing.
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    /**
     * Only admin can create pricing.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Only admin can update pricing.
     */
    public function update(User $user, Pricing $pricing): bool
    {
        return $user->isAdmin();
    }

    /**
     * Only admin can delete pricing.
     */
    public function delete(User $user, Pricing $pricing): bool
    {
        return $user->isAdmin();
    }
}
