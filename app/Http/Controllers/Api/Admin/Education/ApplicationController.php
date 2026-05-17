<?php

namespace App\Http\Controllers\Api\Admin\Education;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Course;
use App\Models\Country;
use App\Models\CourseLevel;
use Illuminate\Http\Request;
use Exception;

class ApplicationController extends Controller
{
    /**
     * Get all applications with relationships.
     */
    /**
     * Get metadata for application forms (Countries, Levels, etc.) in one request.
     */
    public function metadata()
    {
        try {
            return response()->json([
                'success' => true,
                'countries' => Country::orderBy('name')->get(['id', 'name']),
                'course_levels' => CourseLevel::orderBy('name')->get(['id', 'name']),
            ], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Get universities filtered by country for application form.
     */
    public function getUniversities(Request $request)
    {
        try {
            $query = \App\Models\University::query();
            if ($request->filled('country_id')) {
                $query->where('country_id', $request->query('country_id'));
            }
            $universities = $query->orderBy('name')->get(['id', 'name']);
            return response()->json([
                'success' => true,
                'data' => $universities
            ], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Get courses filtered by university for application form.
     */
    public function getCourses(Request $request)
    {
        try {
            $query = \App\Models\Course::query();
            if ($request->filled('university_id')) {
                $query->where('university_id', $request->query('university_id'));
            }
            $courses = $query->orderBy('name')->get(['id', 'name', 'course_level_id']);
            return response()->json([
                'success' => true,
                'data' => $courses
            ], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Get intakes filtered by course for application form.
     */
    public function getIntakes(Request $request)
    {
        try {
            $query = \App\Models\CourseIntake::query();
            if ($request->filled('course_id')) {
                $query->where('course_id', (int) $request->query('course_id'));
            }
            $intakes = $query->latest()->get(['id', 'intake_name', 'course_id']);
            return response()->json([
                'success' => true,
                'data' => $intakes
            ], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function index()
    {
        try {
            $applications = Application::with(['student.user', 'university', 'course', 'country', 'intake', 'courseLevel'])
                ->latest()
                ->paginate(15);

            return response()->json([
                'success' => true,
                'data' => $applications,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve applications.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a new application.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'student_id' => 'required|exists:student_profiles,id',
                'country_id' => 'required|exists:countries,id',
                'university_id' => 'required|exists:universities,id',
                'course_id' => 'required|exists:courses,id',
                'course_level_id' => 'required|exists:course_levels,id',
                'intake_id' => 'nullable|exists:course_intakes,id',
                'status' => 'required|string',
                'notes' => 'nullable|string',
            ]);

            // Auto-fill course name if snapshot is needed
            if (empty($validated['course_name'])) {
                $course = Course::find($validated['course_id']);
                $validated['course_name'] = $course->name;
            }

            $validated['consultant_id'] = auth()->id();

            $application = Application::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Application created successfully.',
                'data' => $application->load(['student.user', 'university', 'course'])
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create application.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show single application details.
     */
    public function show($id)
    {
        try {
            $application = Application::with(['student.user', 'university', 'course', 'country', 'intake', 'documents'])
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $application
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Application not found.'
            ], 404);
        }
    }

    /**
     * Update application status and details.
     */
    public function update(Request $request, $id)
    {
        try {
            $application = Application::findOrFail($id);

            $validated = $request->validate([
                'country_id' => 'sometimes|required|exists:countries,id',
                'university_id' => 'sometimes|required|exists:universities,id',
                'course_id' => 'sometimes|required|exists:courses,id',
                'course_level_id' => 'sometimes|required|exists:course_levels,id',
                'intake_id' => 'nullable|exists:course_intakes,id',
                'status' => 'sometimes|required|string',
                'notes' => 'nullable|string',
                'university_reference_id' => 'nullable|string',
                'rejection_reason' => 'nullable|string',
                'applied_at' => 'nullable|date',
            ]);

            $application->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Application updated successfully.',
                'data' => $application
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update application.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete an application.
     */
    public function destroy($id)
    {
        try {
            $application = Application::findOrFail($id);
            $application->delete();

            return response()->json([
                'success' => true,
                'message' => 'Application deleted successfully.'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete application.'
            ], 500);
        }
    }
}
