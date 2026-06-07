<?php

namespace App\Http\Controllers\Api\Admin\Education;

use App\Http\Controllers\Controller;
use App\Models\CourseLevel;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class CourseLevelController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = min(max((int) $request->query('per_page', 100), 1), 500);
        $levels = CourseLevel::paginate($perPage);
        return response()->json([
            'success' => true,
            'data' => $levels,
            'pagination' => [
                'total' => $levels->total(),
                'current_page' => $levels->currentPage(),
                'last_page' => $levels->lastPage(),
                'per_page' => $levels->perPage(),
                'from' => $levels->firstItem(),
                'to' => $levels->lastItem(),
            ],
        ], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:course_levels,name',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        $level = CourseLevel::create($validated);

        return response()->json([
            'success' => true,
            'data' => $level,
            'message' => 'Course Level created successfully.'
        ], 201);
    }

    public function update(Request $request, CourseLevel $courseLevel): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:course_levels,name,' . $courseLevel->id,
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        $courseLevel->update($validated);

        return response()->json([
            'success' => true,
            'data' => $courseLevel,
            'message' => 'Course Level updated successfully.'
        ], 200);
    }

    public function destroy(CourseLevel $courseLevel): JsonResponse
    {
        // Check if level is used by any course
        if ($courseLevel->courses()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete level as it is being used by courses.'
            ], 422);
        }

        $courseLevel->delete();
        return response()->json([
            'success' => true,
            'message' => 'Course Level deleted successfully.'
        ], 200);
    }
}
