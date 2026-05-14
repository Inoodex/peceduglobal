<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ScheduleTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ScheduleTemplateController extends Controller
{
    public function index()
    {
        try {
            $templates = ScheduleTemplate::orderBy('day_of_week')->get();
            return response()->json([
                'success' => true,
                'data' => $templates
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'day_of_week' => 'required|in:Sunday,Monday,Tuesday,Wednesday,Thursday,Friday,Saturday',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'slot_duration' => 'required|integer|min:15|max:240',
            'buffer_time' => 'nullable|integer|min:0|max:60',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $existing = ScheduleTemplate::where('day_of_week', $request->day_of_week)->first();
            if ($existing) {
                return response()->json([
                    'success' => false,
                    'message' => 'Template already exists for this day'
                ], 400);
            }

            $template = ScheduleTemplate::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Template created successfully',
                'data' => $template
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $template = ScheduleTemplate::findOrFail($id);
            $template->delete();

            return response()->json([
                'success' => true,
                'message' => 'Template deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
