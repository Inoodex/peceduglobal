<?php

namespace App\Http\Controllers\Api\Admin\Education;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class CourseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = min(max((int) $request->query('per_page', 100), 1), 500);
        $query = Course::with(['university', 'country', 'courseLevel']);
        if ($request->filled('university_id')) {
            $query->where('university_id', (int) $request->query('university_id'));
        }
        $courses = $query->paginate($perPage);
        return response()->json([
            'success' => true,
            'data' => $courses,
            'pagination' => [
                'total' => $courses->total(),
                'current_page' => $courses->currentPage(),
                'last_page' => $courses->lastPage(),
                'per_page' => $courses->perPage(),
                'from' => $courses->firstItem(),
                'to' => $courses->lastItem(),
            ],
        ], Response::HTTP_OK);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'university_id' => 'required|exists:universities,id',
            'course_level_id' => 'required|exists:course_levels,id',
            'country_id' => 'nullable|exists:countries,id',
            'name' => 'required|string|max:255',
            'intake' => 'nullable|string',
            'duration' => 'nullable|string',
            'ielts_requirement' => 'nullable|string',
            'tuition_fee' => 'nullable|numeric',
            'requirements' => 'nullable|string',
            'is_popular' => 'nullable',
        ]);

        // Auto-generate slug
        $validated['slug'] = Str::slug($validated['name']);
        $originalSlug = $validated['slug'];
        $count = 1;
        while (Course::where('slug', $validated['slug'])->exists()) {
            $validated['slug'] = $originalSlug . '-' . $count++;
        }

        $course = Course::create($validated);

        return response()->json([
            'success' => true,
            'data' => $course,
            'message' => 'Course created successfully.'
        ], Response::HTTP_CREATED);
    }

    public function show(Course $course): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $course->load(['university', 'country', 'courseLevel']),
        ], Response::HTTP_OK);
    }

    public function update(Request $request, Course $course): JsonResponse
    {
        $validated = $request->validate([
            'university_id' => 'required|exists:universities,id',
            'course_level_id' => 'required|exists:course_levels,id',
            'country_id' => 'nullable|exists:countries,id',
            'name' => 'required|string|max:255',
            'intake' => 'nullable|string',
            'duration' => 'nullable|string',
            'ielts_requirement' => 'nullable|string',
            'tuition_fee' => 'nullable|numeric',
            'requirements' => 'nullable|string',
            'is_popular' => 'nullable',
        ]);

        // Update slug if name changed
        if (isset($validated['name']) && $validated['name'] !== $course->name) {
            $validated['slug'] = Str::slug($validated['name']);
            $originalSlug = $validated['slug'];
            $count = 1;
            while (Course::where('slug', $validated['slug'])->where('id', '!=', $course->id)->exists()) {
                $validated['slug'] = $originalSlug . '-' . $count++;
            }
        }

        $course->update($validated);

        return response()->json([
            'success' => true,
            'data' => $course,
            'message' => 'Course updated successfully.'
        ], Response::HTTP_OK);
    }

    public function destroy(Course $course): JsonResponse
    {
        $course->delete();
        return response()->json([
            'success' => true,
            'message' => 'Course deleted successfully.'
        ], Response::HTTP_OK);
    }
}
