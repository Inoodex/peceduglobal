<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Store a new user.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'role' => 'required|string|in:student,consultant,admin,editor',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'data' => $validator->errors()
            ], 422);
        }

        $fullName = $request->first_name . ' ' . $request->last_name;

        $user = User::create([
            'full_name' => $fullName,
            'email' => $request->email,
            'password' => \Illuminate\Support\Facades\Hash::make($request->password),
            'role' => $request->role,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User created successfully',
            'data' => $user
        ], 201);
    }

    /**
     * Update user role.
     */
    public function updateRole(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'role' => 'required|in:student,consultant,admin,editor',
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
     * Toggle user active status.
     */
    public function toggleActive(User $user)
    {
        $user->update(['is_active' => !$user->is_active]);

        return response()->json([
            'success' => true,
            'message' => $user->is_active ? 'User activated successfully' : 'User deactivated successfully',
            'data' => $user
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
