<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\StoreProfileRequest;
use App\Http\Requests\Student\UpdatePasswordRequest;
use App\Http\Resources\Student\StudentProfileResource;
use App\Models\StudentProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    /**
     * GET /auth/student/profile
     * Return the authenticated student's profile.
     */
    public function show(): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        // Find existing profile WITHOUT auto-creating it (avoids NOT NULL DB error)
        $profile = StudentProfile::where('user_id', $user->id)->first();

        if (!$profile) {
            // Return safe empty structure using user data only
            $nameParts = explode(' ', $user->full_name ?? '', 2);
            return response()->json([
                'success' => true,
                'data' => [
                    'id'                    => null,
                    'first_name'            => $nameParts[0] ?? '',
                    'last_name'             => $nameParts[1] ?? '',
                    'full_name'             => $user->full_name ?? '',
                    'email'                 => $user->email ?? '',
                    'phone'                 => $user->phone ?? '',
                    'country'               => $user->country_of_origin ?? '',
                    'nationality'           => $user->nationality ?? '',
                    'profile_photo_url'     => $user->profile_photo_url
                        ? (str_starts_with($user->profile_photo_url, 'http')
                            ? $user->profile_photo_url
                            : asset($user->profile_photo_url))
                        : null,
                    'address'               => null,
                    'cgpa'                  => null,
                    'ielts_score'           => null,
                    'father_name'           => null,
                    'mother_name'           => null,
                    'sponsor_phone'         => null,
                    'passport_number'       => null,
                    'passport_validity'     => null,
                    'date_of_birth'         => null,
                    'country_id'            => null,
                    'university_id'         => null,
                    'course_id'             => null,
                    'course_intake_id'      => null,
                    'documents'             => [],
                    'translation_documents' => [],
                ],
            ], Response::HTTP_OK);
        }

        return response()->json([
            'success' => true,
            'data'    => new StudentProfileResource($profile),
        ], Response::HTTP_OK);
    }

    /**
     * PUT /auth/profile
     * Update personal info for any authenticated user.
     * CGPA / IELTS / address / academic details are updated only if the user is a student.
     */
    public function update(StoreProfileRequest $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user      = auth()->user();
        $validated = $request->validated();

        // Combine first + last name into full_name
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

        // Update student_profiles only for students
        $profile = null;
        if ($user->role === 'student') {
            $profile = StudentProfile::firstOrCreate(
                ['user_id' => $user->id],
                ['phone' => $validated['phone'] ?? ''] // Satisfy NOT NULL constraint on first create
            );

            // Unpack new documents
            $newDocs = $this->storeUploadedDocuments($request->file('documents'));
            $newTrans = $this->storeUploadedDocuments($request->file('translation_docs'));

            $existingDocs = is_array($profile->documents) ? $profile->documents : (json_decode($profile->documents, true) ?: []);
            $existingTrans = is_array($profile->translation_documents) ? $profile->translation_documents : (json_decode($profile->translation_documents, true) ?: []);

            $profile->update([
                'phone'             => $validated['phone'] ?? $profile->phone,
                'address'           => $validated['address'] ?? $profile->address,
                'cgpa'              => $validated['cgpa'] ?? $profile->cgpa,
                'ielts_score'       => $validated['ielts_score'] ?? $profile->ielts_score,

                // Rich academic fields
                'father_name'       => $validated['father_name'] ?? $profile->father_name,
                'mother_name'       => $validated['mother_name'] ?? $profile->mother_name,
                'sponsor_phone'     => $validated['sponsor_phone'] ?? $profile->sponsor_phone,
                'passport_number'   => $validated['passport_number'] ?? $profile->passport_number,
                'passport_validity' => $validated['passport_validity'] ?? $profile->passport_validity,
                'date_of_birth'     => $validated['date_of_birth'] ?? $profile->date_of_birth,
                'country_id'        => $validated['country_id'] ?? $profile->country_id,
                'university_id'     => $validated['university_id'] ?? $profile->university_id,
                'course_id'         => $validated['course_id'] ?? $profile->course_id,
                'course_intake_id'  => $validated['course_intake_id'] ?? $profile->course_intake_id,

                // Merged documents arrays
                'documents'             => $newDocs ? array_merge($existingDocs, $newDocs) : $existingDocs,
                'translation_documents' => $newTrans ? array_merge($existingTrans, $newTrans) : $existingTrans,
            ]);
        }

        return response()->json([
            'success' => true,
            'data'    => $profile ? new StudentProfileResource($profile->fresh()) : null,
            'message' => 'Profile updated successfully.',
        ], Response::HTTP_OK);
    }

    /**
     * DELETE /auth/profile/document
     * Remove a specific document path from the authenticated student's profile.
     */
    public function removeDocument(\Illuminate\Http\Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'required|in:documents,translation_documents',
            'path' => 'required|string',
        ]);

        try {
            $profile = StudentProfile::where('user_id', auth()->id())->firstOrFail();
            $column  = $request->type;
            $files   = $profile->$column ?? [];

            if (!is_array($files)) {
                $files = json_decode($files, true) ?: [];
            }

            $files = array_filter($files, fn($path) => $path !== $request->path);

            Storage::disk('public')->delete($request->path);
            $profile->$column = array_values($files);
            $profile->save();

            return response()->json(['success' => true, 'message' => 'Document removed.'], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * PUT /auth/student/profile/password
     * Update password only – completely separate from profile info.
     */
    public function updatePassword(UpdatePasswordRequest $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        // Verify current password first
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect.',
                'errors'  => ['current_password' => ['The provided current password does not match our records.']],
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user->update([
            'password' => bcrypt($request->password),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password updated successfully.',
        ], Response::HTTP_OK);
    }

    protected function storeUploadedDocuments($files): ?array
    {
        if (!$files) return null;
        $list = is_array($files) ? $files : [$files];
        $paths = [];
        foreach ($list as $file) {
            if (!$file) continue;
            $name = (string) Str::uuid() . '.' . $file->getClientOriginalExtension();
            $paths[] = $file->storeAs('students/profiles', $name, 'public');
        }
        return $paths === [] ? null : $paths;
    }
}
