<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\User\DeleteUserAction;
use App\Actions\Admin\User\ListUsersAction;
use App\Actions\Admin\User\ShowUserAction;
use App\Actions\Admin\User\UpdateUserAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ListUsersRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    /**
     * List all users with pagination.
     */
    public function index(ListUsersRequest $request, ListUsersAction $action): JsonResponse
    {
        $users = $action->execute($request->validated());

        return response()->json([
            'success' => true,
            'data' => $users,
        ]);
    }

    /**
     * Show a specific user.
     */
    public function show(string $id, ShowUserAction $action): JsonResponse
    {
        $user = $action->execute($id);

        return response()->json([
            'success' => true,
            'data' => $user,
        ]);
    }

    /**
     * Update a user.
     */
    public function update(UpdateUserRequest $request, string $id, UpdateUserAction $action): JsonResponse
    {
        try {
            $user = User::findOrFail($id);
            $updatedUser = $action->execute($user, $request->validated(), $request->user());

            return response()->json([
                'success' => true,
                'message' => 'User updated successfully.',
                'data' => $updatedUser,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 403);
        }
    }

    /**
     * Delete a user.
     */
    public function destroy(string $id, DeleteUserAction $action): JsonResponse
    {
        try {
            $user = User::findOrFail($id);
            $action->execute($user, request()->user());

            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully.',
            ]);
        } catch (\Exception $e) {
            $statusCode = str_contains($e->getMessage(), 'balance') ? 422 : 403;
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $statusCode);
        }
    }
}
