<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\ConsultantSchedule;
use App\Models\Appointment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class ConsultantController extends Controller
{
    /**
     * Search consultants by name or email.
     */
    public function search(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:2'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $name = $request->name;
            $consultants = User::where('role', 'consultant')
                ->where('is_active', true)
                ->where(function($query) use ($name) {
                    $query->where('full_name', 'LIKE', "%{$name}%")
                          ->orWhere('email', 'LIKE', "%{$name}%");
                })
                ->select('id', 'full_name', 'email', 'profile_photo_url')
                ->get();

            return response()->json(['success' => true, 'data' => $consultants], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Get available slots for a specific consultant and date.
     */
    public function getSlots(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'consultant_id' => 'required|exists:users,id',
            'date' => 'required|date|after_or_equal:today'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $slots = ConsultantSchedule::where('consultant_id', $request->consultant_id)
                ->where('slot_date', $request->date)
                ->where('status', 'available')
                ->get();

            return response()->json(['success' => true, 'data' => $slots], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Get unified availability for all consultants.
     */
    public function getGlobalAvailability(Request $request)
    {
        try {
            $date = $request->query('date', now()->toDateString());
            
            $consultants = User::where('role', 'consultant')
                ->where('is_active', true)
                ->with(['consultantSchedules' => function($query) use ($date) {
                    $query->where('slot_date', $date)
                          ->where('status', 'available')
                          ->orderBy('start_time');
                }])
                ->get()
                ->map(function($user) use ($date) {
                    $nextAvailable = ConsultantSchedule::where('consultant_id', $user->id)
                        ->where('status', 'available')
                        ->where('slot_date', '>=', $date)
                        ->orderBy('slot_date')
                        ->first();

                    return [
                        'id' => $user->id,
                        'full_name' => $user->full_name,
                        'profile_photo_url' => $user->profile_photo_url,
                        'available_slots' => $user->consultantSchedules,
                        'next_available_date' => $nextAvailable ? $nextAvailable->slot_date : null
                    ];
                });

            return response()->json(['success' => true, 'data' => $consultants], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Book an appointment (Public/Frontend side).
     */
    public function bookAppointment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'schedule_id' => 'required|exists:consultant_schedules,id',
            'meeting_type' => 'required|in:online,physical',
            'student_id' => 'required|exists:users,id', // If auth is handled separately
            'student_notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $schedule = ConsultantSchedule::findOrFail($request->schedule_id);
            if ($schedule->status === 'booked') {
                return response()->json(['success' => false, 'message' => 'Slot already booked'], 400);
            }

            $appointment = Appointment::create([
                'schedule_id' => $schedule->id,
                'student_id' => $request->student_id,
                'status' => 'confirmed',
                'meeting_type' => $request->meeting_type,
                'student_notes' => $request->student_notes
            ]);

            $schedule->update(['status' => 'booked']);

            return response()->json(['success' => true, 'message' => 'Booked!', 'data' => $appointment], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
