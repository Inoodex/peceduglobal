<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Http\Resources\Frontend\CourseResource;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CourseController extends Controller
{
    /**
     * Display a listing of courses with filters.
     */
    public function index(Request $request)
    {
        $query = Course::with(['university.country', 'level', 'intake']);

        // Search by name
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Filter by university
        if ($request->has('university_id')) {
            $query->where('university_id', $request->university_id);
        }

        // Filter by country
        if ($request->has('country_id')) {
            $query->whereHas('university', function($q) use ($request) {
                $q->where('country_id', $request->country_id);
            });
        }

        // Filter by level
        if ($request->has('level_id')) {
            $query->where('course_level_id', $request->level_id);
        }

        // Filter by intake
        if ($request->has('intake_id')) {
            $query->where('course_intake_id', $request->intake_id);
        }

        $courses = $query->latest()->paginate($request->query('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => CourseResource::collection($courses)->response()->getData(true),
        ], Response::HTTP_OK);
    }

    /**
     * Display the specified course details.
     */
    public function show($id)
    {
        $course = Course::with(['university.country', 'courseLevel'])->find($id);

        if (!$course) {
            return response()->json([
                'success' => false,
                'message' => 'Course not found.',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'success' => true,
            'data' => new CourseResource($course),
        ], Response::HTTP_OK);
    }
}
