<?php

namespace App\Actions\Admin\User;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

final class ListUsersAction
{
    /**
     * List all users with filters and pagination.
     */
    public function execute(array $filters = []): LengthAwarePaginator
    {
        $perPage = $filters['per_page'] ?? 15;
        $role = $filters['role'] ?? null;
        $search = $filters['search'] ?? null;

        $query = User::query()
            ->with(['serviceAccount:id,user_id,balance,status'])
            ->withCount('apiKeys');

        if ($role) {
            $query->where('role', $role);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone_number', 'like', "%{$search}%");
            });
        }

        return $query->latest()->paginate($perPage);
    }
}
