<?php

namespace App\Http\Controllers\Api\Consultant;

use App\Http\Controllers\Controller;
use App\Models\ConsultantSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AvailabilityController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'slot_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $schedule = ConsultantSchedule::create([
                'consultant_id' => auth()->id(),
                'slot_date' => $request->slot_date,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'day_of_week' => date('l', strtotime($request->slot_date)),
                'status' => 'available'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Appointment slot created successfully',
                'data' => $schedule
            ], 201);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function releaseSlot(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'schedule_id' => 'required|exists:consultant_schedules,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $schedule = ConsultantSchedule::where('id', $request->schedule_id)
                ->where('consultant_id', auth()->id())
                ->first();

            if (!$schedule) {
                return response()->json([
                    'success' => false,
                    'message' => 'Schedule not found'
                ], 404);
            }

            if ($schedule->status === 'booked') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete a booked slot'
                ], 400);
            }

            $schedule->delete();

            return response()->json([
                'success' => true,
                'message' => 'Slot deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getMyClaimedSlots(Request $request)
    {
        try {
            $search = $request->query('search');
            $perPage = $request->query('per_page', 10);

            $query = ConsultantSchedule::where('consultant_id', auth()->id())
                ->orderBy('slot_date', 'desc')
                ->orderBy('start_time', 'asc');

            if ($search) {
                $query->where('slot_date', 'like', "%{$search}%");
            }

            $schedules = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $schedules->items(),
                'meta' => [
                    'current_page' => $schedules->currentPage(),
                    'last_page' => $schedules->lastPage(),
                    'total' => $schedules->total(),
                    'per_page' => $schedules->perPage(),
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
