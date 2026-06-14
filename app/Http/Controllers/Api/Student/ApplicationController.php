<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\StoreApplicationRequest;
use App\Http\Resources\Student\ApplicationResource;
use App\Models\Application;
use App\Models\StudentProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ApplicationController extends Controller
{
    public function index(): JsonResponse
    {
        $profile = StudentProfile::where('user_id', auth()->id())->first();
        
        if (!$profile) {
            return response()->json([
                'success' => true,
                'data' => [],
            ], Response::HTTP_OK);
        }

        $applications = Application::where('student_id', $profile->id)->with('university')->paginate(10);

        return response()->json([
            'success' => true,
            'data' => ApplicationResource::collection($applications),
        ], Response::HTTP_OK);
    }

    public function store(StoreApplicationRequest $request): JsonResponse
    {
        $profile = StudentProfile::where('user_id', auth()->id())->first();
        
        if (!$profile) {
            return response()->json([
                'success' => false,
                'message' => 'Please complete your student profile before submitting an application.'
            ], Response::HTTP_BAD_REQUEST);
        }

        $validated = $request->validated();
        $validated['student_id'] = $profile->id;

        $application = Application::create($validated);

        return response()->json([
            'success' => true,
            'data' => new ApplicationResource($application),
            'message' => 'Application submitted successfully.'
        ], Response::HTTP_CREATED);
    }

    public function show(Application $application): JsonResponse
    {
        // Ensure student owns the application
        $profile = StudentProfile::where('user_id', auth()->id())->first();
        
        if (!$profile || $application->student_id !== $profile->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }

        return response()->json([
            'success' => true,
            'data' => new ApplicationResource($application->load('university')),
        ], Response::HTTP_OK);
    }
}
