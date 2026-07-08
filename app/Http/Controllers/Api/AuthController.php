<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\ResetPasswordOtpMail;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    /**
     * Number of minutes a password-reset OTP stays valid.
     */
    const RESET_OTP_TTL_MINUTES = 60;

    /**
     * Request a password-reset OTP. Works for admin/consultant (dashboard) AND
     * student (public) since all of them live in the same `users` table and the
     * only input is an email address.
     *
     * Stores a 6-digit OTP in the `password_reset_tokens` table (reusing the
     * standard Laravel table — token column holds the OTP, created_at holds the
     * expiry) and emails it to the user. Always responds success (even for
     * unknown emails) so attackers can't enumerate which emails are registered.
     */
    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'data' => $validator->errors()
            ], 422);
        }

        $email = $request->email;
        $user = User::where('email', $email)->first();

        // Always return success — never reveal whether an email is registered.
        if (!$user) {
            return response()->json([
                'success' => true,
                'message' => 'If that email exists, a reset code has been sent.',
            ]);
        }

        // Generate a 6-digit OTP. Keep one entry per email (delete old, insert new).
        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        DB::table('password_reset_tokens')->where('email', $email)->delete();
        DB::table('password_reset_tokens')->insert([
            'email' => $email,
            'token' => $otp,
            'created_at' => now(),
        ]);

        // Best-effort email send — don't fail the request if mail is misconfigured.
        try {
            Mail::to($email)->send(new ResetPasswordOtpMail(
                $user->full_name,
                $otp,
                self::RESET_OTP_TTL_MINUTES,
            ));
        } catch (\Throwable $e) {
            // Mail failure is non-fatal; the OTP is in the DB and (in dev) the log.
        }

        return response()->json([
            'success' => true,
            'message' => 'If that email exists, a reset code has been sent.',
        ]);
    }

    /**
     * Reset a password using the OTP emailed by forgotPassword().
     *
     * Validates the OTP + expiry against password_reset_tokens, then updates the
     * user's password and deletes the OTP so it can't be reused. Returns a fresh
     * JWT so the SPA can log the user straight in without a second round-trip.
     */
    public function resetPassword(Request $request)
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
                'data' => $validator->errors()
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

        // Expiry check: created_at + TTL minutes must still be in the future.
        $expiresAt = \Carbon\Carbon::parse($record->created_at)
            ->addMinutes(self::RESET_OTP_TTL_MINUTES);
        if (now()->gt($expiresAt)) {
            DB::table('password_reset_tokens')->where('email', $email)->delete();
            return response()->json([
                'success' => false,
                'message' => 'This reset code has expired. Please request a new one.',
            ], 400);
        }

        $user = User::where('email', $email)->first();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'No account found for this email.',
            ], 404);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        // Burn the OTP so it's single-use.
        DB::table('password_reset_tokens')->where('email', $email)->delete();

        // Hand back a fresh JWT so the SPA can log in immediately.
        $token = JWTAuth::fromUser($user);

        return response()->json([
            'success' => true,
            'message' => 'Password reset successfully.',
            'data' => $user,
            'token' => $token,
        ]);
    }

    /**
     * Register a new user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'role' => 'nullable|string|in:student,consultant',
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
            'password' => Hash::make($request->password),
            'role' => $request->role ?? 'student',
        ]);

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'success' => true,
            'message' => 'User registered successfully',
            'data' => $user,
            'token' => $token
        ], 201);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        /** @var \PHPOpenSourceSaver\JWTAuth\JWTGuard $guard */
        $guard = auth('api');
        if (!$token = $guard->attempt($credentials)) {
            ActivityLog::create([
                'user_id' => null,
                'event' => 'failed_login',
                'description' => "Failed login attempt for {$request->email}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'loggable_type' => User::class,
                'loggable_id' => 0,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
                'data' => null
            ], 401);
        }

        $user = $guard->user();

        if (!$user->is_active) {
            $guard->logout();
            return response()->json([
                'success' => false,
                'message' => 'Your account has been deactivated. Contact an administrator.',
                'data' => null
            ], 403);
        }

        $user->update(['last_seen_at' => now()]);

        \App\Models\UserSession::create([
            'user_id' => $user->id,
            'login_at' => now(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        ActivityLog::create([
            'user_id' => $user->id,
            'event' => 'login',
            'description' => "Login successful for {$user->full_name}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'loggable_type' => User::class,
            'loggable_id' => $user->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => $user->load('permissions'),
            'token' => $token
        ]);
    }

    /**
     * Special login for students to access backend dashboard.
     */
    public function studentLogin(Request $request)
    {
        $credentials = $request->only('email', 'password');

        /** @var \PHPOpenSourceSaver\JWTAuth\JWTGuard $guard */
        $guard = auth('api');
        
        if (!$token = $guard->attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials',
                'data' => null
            ], 401);
        }

        $user = $guard->user();

        if (!$user->is_active) {
            $guard->logout();
            return response()->json([
                'success' => false,
                'message' => 'Your account has been deactivated. Contact an administrator.',
                'data' => null
            ], 403);
        }

        // Check if the user is actually a student
        if ($user->role !== 'student') {
            return response()->json([
                'success' => false,
                'message' => 'Only students can login through this portal',
                'data' => null
            ], 403);
        }

        $user->update(['last_seen_at' => now()]);

        \App\Models\UserSession::create([
            'user_id' => $user->id,
            'login_at' => now(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        ActivityLog::create([
            'user_id' => $user->id,
            'event' => 'login',
            'description' => "Student login successful for {$user->full_name}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'loggable_type' => User::class,
            'loggable_id' => $user->id,
        ]);

        // Dynamic redirect URL based on environment
        $redirectUrl = config('app.env') === 'production' 
            ? 'https://apps.peceduglobal.com/dashboard' 
            : 'http://127.0.0.1:8000/dashboard';

        return response()->json([
            'success' => true,
            'message' => 'Student login successful',
            'data' => $user,
            'token' => $token,
            'redirect_url' => $redirectUrl
        ]);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        /** @var \PHPOpenSourceSaver\JWTAuth\JWTGuard $guard */
        $guard = auth('api');
        return response()->json([
            'success' => true,
            'message' => 'User profile retrieved',
            'data' => $guard->user()->load('permissions')
        ]);
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        /** @var \PHPOpenSourceSaver\JWTAuth\JWTGuard $guard */
        $guard = auth('api');
        $user = $guard->user();
        $user?->update(['last_seen_at' => null]);

        if ($user) {
            \App\Models\UserSession::where('user_id', $user->id)
                ->whereNull('logout_at')
                ->orderByDesc('login_at')
                ->first()
                ?->update(['logout_at' => now()]);

            ActivityLog::create([
                'user_id' => $user->id,
                'event' => 'logout',
                'description' => "Logout successful for {$user->full_name}",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'loggable_type' => User::class,
                'loggable_id' => $user->id,
            ]);
        }

        $guard->logout();

        return response()->json([
            'success' => true,
            'message' => 'Successfully logged out',
            'data' => null
        ]);
    }

    /**
     * Get the session history for a specific user.
     */
    public function getUserSessions($id)
    {
        $sessions = \App\Models\UserSession::where('user_id', $id)
            ->orderByDesc('login_at')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $sessions
        ]);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        /** @var \PHPOpenSourceSaver\JWTAuth\JWTGuard $guard */
        $guard = auth('api');
        return response()->json([
            'success' => true,
            'message' => 'Token refreshed',
            'data' => $guard->user()->load('permissions'),
            'token' => $guard->refresh()
        ]);
    }
}
