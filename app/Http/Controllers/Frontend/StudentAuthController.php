<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StudentAuthController extends Controller
{
    /**
     * Authenticate a student user and return a JWT.
     * Only users with the role 'student' are allowed to login here.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors()
            ], 422);
        }

        $credentials = $request->only('email', 'password');

        /** @var \PHPOpenSourceSaver\JWTAuth\JWTGuard $guard */
        $guard = auth('api');

        if (!$token = $guard->attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid email or password.',
                'errors'  => [
                    'email' => ['Invalid credentials provided.']
                ]
            ], 401);
        }

        /** @var \App\Models\User $user */
        $user = $guard->user();

        // Strictly enforce student role check for this login endpoint
        if ($user->role !== 'student') {
            $guard->logout();
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Only student accounts can login here.',
                'errors'  => [
                    'email' => ['Only student accounts are authorized to log in via this portal.']
                ]
            ], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'Student login successful',
            'data'    => $user,
            'token'   => $token
        ]);
    }

    /**
     * Invalidate the current student token and log out.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(): JsonResponse
    {
        /** @var \PHPOpenSourceSaver\JWTAuth\JWTGuard $guard */
        $guard = auth('api');
        
        if ($guard->check()) {
            $guard->logout();
        }

        return response()->json([
            'success' => true,
            'message' => 'Student successfully logged out',
            'data'    => null
        ]);
    }
}
