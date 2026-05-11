<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CourseIntake;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CourseIntakeController extends Controller
{
    public function index()
    {
        $intakes = CourseIntake::with(['course', 'university'])->latest()->get();
        return response()->json(['data' => $intakes]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'course_id' => 'required|exists:courses,id',
            'university_id' => 'required|exists:universities,id',
            'intake_name' => 'required|string|max:255',
            'application_start_date' => 'required|date',
            'application_deadline' => 'required|date',
            'class_start_date' => 'nullable|date',
            'status' => 'required|in:upcoming,open,closed',
        ]);

        if ($validator->fails()) return response()->json($validator->errors(), 422);

        $intake = CourseIntake::create($request->all());
        return response()->json(['data' => $intake], 201);
    }

    public function show($id)
    {
        $intake = CourseIntake::findOrFail($id);
        return response()->json(['data' => $intake]);
    }

    public function update(Request $request, $id)
    {
        $intake = CourseIntake::findOrFail($id);
        $intake->update($request->all());
        return response()->json(['data' => $intake]);
    }

    public function destroy($id)
    {
        CourseIntake::destroy($id);
        return response()->json(['message' => 'Intake deleted successfully']);
    }
}

