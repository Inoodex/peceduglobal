<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Mail\ResetPasswordOtpMail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Carbon\Carbon;

class StudentAuthController extends Controller
{
    /**
     * Number of minutes a password-reset OTP stays valid.
     */
    const RESET_OTP_TTL_MINUTES = 60;
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

    /**
     * Request a password-reset OTP for students.
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $email = $request->email;
        $user = User::where('email', $email)->first();

        // Always return success to prevent email enumeration, but only send OTP to students.
        if (!$user || $user->role !== 'student') {
            return response()->json([
                'success' => true,
                'message' => 'If that email exists, a reset code has been sent.',
            ]);
        }

        // Generate a 6-digit OTP
        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        DB::table('password_reset_tokens')->where('email', $email)->delete();
        DB::table('password_reset_tokens')->insert([
            'email' => $email,
            'token' => $otp,
            'created_at' => now(),
        ]);

        try {
            Mail::to($email)->send(new ResetPasswordOtpMail(
                $user->full_name,
                $otp,
                self::RESET_OTP_TTL_MINUTES,
            ));
        } catch (\Throwable $e) {
            // Mail failure is non-fatal in dev
        }

        return response()->json([
            'success' => true,
            'message' => 'If that email exists, a reset code has been sent.',
        ]);
    }

    /**
     * Reset a student password using the OTP.
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'otp'      => 'required|string|size:6',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $email = $request->email;
        $otp   = $request->otp;

        $record = DB::table('password_reset_tokens')->where('email', $email)->first();

        if (!$record || !hash_equals((string) $record->token, (string) $otp)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired reset code.',
            ], 400);
        }

        // Expiry check
        $expiresAt = Carbon::parse($record->created_at)->addMinutes(self::RESET_OTP_TTL_MINUTES);
        if (now()->gt($expiresAt)) {
            DB::table('password_reset_tokens')->where('email', $email)->delete();
            return response()->json([
                'success' => false,
                'message' => 'This reset code has expired. Please request a new one.',
            ], 400);
        }

        $user = User::where('email', $email)->first();
        if (!$user || $user->role !== 'student') {
            return response()->json([
                'success' => false,
                'message' => 'No student account found for this email.',
            ], 404);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        // Delete OTP
        DB::table('password_reset_tokens')->where('email', $email)->delete();

        // Authenticate student directly
        $token = JWTAuth::fromUser($user);

        return response()->json([
            'success' => true,
            'message' => 'Password reset successfully.',
            'data' => $user,
            'token' => $token,
        ]);
    }
}
