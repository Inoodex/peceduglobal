<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\StoreProfileRequest;
use App\Http\Resources\Student\StudentProfileResource;
use App\Models\StudentProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ProfileController extends Controller
{
    public function show(): JsonResponse
    {
        $profile = StudentProfile::where('user_id', auth()->id())->firstOrFail();
        return response()->json([
            'success' => true,
            'data' => new StudentProfileResource($profile),
        ], Response::HTTP_OK);
    }

    public function update(StoreProfileRequest $request): JsonResponse
    {
        $profile = StudentProfile::where('user_id', auth()->id())->firstOrFail();
        $validated = $request->validated();
        $profile->update($validated);

        return response()->json([
            'success' => true,
            'data' => new StudentProfileResource($profile),
            'message' => 'Profile updated successfully.'
        ], Response::HTTP_OK);
    }
}
