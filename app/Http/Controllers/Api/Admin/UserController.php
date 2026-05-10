<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Update user role.
     */
    public function updateRole(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'role' => 'required|in:student,counselor,admin',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'data' => $validator->errors()
            ], 422);
        }

        $user->update([
            'role' => $request->role
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User role updated successfully',
            'data' => $user
        ]);
    }

    /**
     * Update user permissions.
     */
    public function updatePermissions(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'permissions' => 'required|array',
            'permissions.*' => 'integer', // Expecting permission IDs now
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'data' => $validator->errors()
            ], 422);
        }

        // Sync permissions using the pivot table
        $user->permissions()->sync($request->permissions);

        return response()->json([
            'success' => true,
            'message' => 'User permissions updated successfully',
            'data' => $user->load('permissions')
        ]);
    }

    /**
     * List all users.
     */
    public function index()
    {
        $users = User::with('permissions')->get();
        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }
}
