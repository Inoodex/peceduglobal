<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\StoreProfileRequest;
use App\Http\Requests\Student\UpdatePasswordRequest;
use App\Http\Resources\Student\StudentProfileResource;
use App\Models\StudentProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class StudentProfileController extends Controller
{
    /**
     * GET /api/auth/student/profile
     * Return the authenticated user's profile data.
     */
    public function show(): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user    = auth()->user();
        
        // Provide user's phone or blank string as default if student profile doesn't exist yet
        $profile = StudentProfile::firstOrCreate(
            ['user_id' => $user->id],
            ['phone' => $user->phone ?? '']
        );

        return response()->json([
            'success' => true,
            'data'    => new StudentProfileResource($profile),
        ], Response::HTTP_OK);
    }

    /**
     * POST /api/auth/student/profile  (with _method=PUT)
     * Update profile info — name, email, phone, country, nationality, image.
     * CGPA / IELTS / address updated only for students.
     */
    public function update(StoreProfileRequest $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user      = auth()->user();
        $validated = $request->validated();

        // Merge first + last name into full_name
        $fullName = trim($validated['first_name'] . ' ' . $validated['last_name']);

        // Handle profile photo upload
        $profilePhotoUrl = $user->profile_photo_url;
        if ($request->hasFile('image')) {
            if ($user->profile_photo_url) {
                $oldPath = str_replace('/storage/', '', $user->profile_photo_url);
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }
            $path            = $request->file('image')->store('users/profiles', 'public');
            $profilePhotoUrl = '/storage/' . $path;
        }

        // Update users table (all roles)
        $user->update([
            'full_name'         => $fullName,
            'email'             => $validated['email'],
            'phone'             => $validated['phone'] ?? null,
            'country_of_origin' => $validated['country'] ?? null,
            'nationality'       => $validated['nationality'] ?? null,
            'profile_photo_url' => $profilePhotoUrl,
        ]);

        // Update student_profiles table only for student role
        $profile = null;
        if ($user->role === 'student') {
            $profile = StudentProfile::firstOrCreate(
                ['user_id' => $user->id],
                ['phone' => $validated['phone'] ?? $user->phone ?? '']
            );
            $profile->update([
                'phone'       => $validated['phone'] ?? null,
                'address'     => $validated['address'] ?? null,
                'cgpa'        => $validated['cgpa'] ?? null,
                'ielts_score' => $validated['ielts_score'] ?? null,
            ]);
        }

        return response()->json([
            'success' => true,
            'data'    => $profile ? new StudentProfileResource($profile->fresh()) : null,
            'message' => 'Profile updated successfully.',
        ], Response::HTTP_OK);
    }

    /**
     * PUT /api/auth/student/profile/password
     * Update password only — completely separate from profile info.
     */
    public function updatePassword(UpdatePasswordRequest $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        // Verify current password
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect.',
                'errors'  => ['current_password' => ['The provided current password does not match our records.']],
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user->update(['password' => bcrypt($request->password)]);

        return response()->json([
            'success' => true,
            'message' => 'Password updated successfully.',
        ], Response::HTTP_OK);
    }
}
