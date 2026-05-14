<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\ConsultantSchedule;
use App\Models\Appointment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class AppointmentController extends Controller
{
    public function searchConsultantsByName(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:2'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $name = $request->name;
            $consultants = User::where('role', 'consultant')
                ->where(function($query) use ($name) {
                    $query->where('full_name', 'LIKE', "%{$name}%")
                          ->orWhere('email', 'LIKE', "%{$name}%");
                })
                ->select('id', 'full_name', 'email', 'profile_photo_url')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $consultants
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getConsultantAvailableSlots(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'consultant_id' => 'required|exists:users,id',
            'date' => 'required|date|after_or_equal:today'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $slots = ConsultantSchedule::where('consultant_id', $request->consultant_id)
                ->where('slot_date', $request->date)
                ->where('status', 'available')
                ->get()
                ->map(function($schedule) {
                    return [
                        'schedule_id' => $schedule->id,
                        'date' => $schedule->slot_date,
                        'start_time' => $schedule->start_time,
                        'end_time' => $schedule->end_time,
                        'day_of_week' => $schedule->day_of_week
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $slots
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getNextAvailableDate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'consultant_id' => 'required|exists:users,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $today = now()->toDateString();

            $nextSchedule = ConsultantSchedule::where('consultant_id', $request->consultant_id)
                ->where('status', 'available')
                ->where('slot_date', '>', $today)
                ->orderBy('slot_date')
                ->first();

            if (!$nextSchedule) {
                return response()->json([
                    'success' => true,
                    'message' => 'No future availability found for this consultant',
                    'data' => null
                ], 200);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'next_available_date' => $nextSchedule->slot_date,
                    'formatted_date' => Carbon::parse($nextSchedule->slot_date)->format('F j, Y')
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function bookAppointment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'schedule_id' => 'required|exists:consultant_schedules,id',
            'meeting_type' => 'required|in:online,physical',
            'student_notes' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $studentId = auth()->id();
            $scheduleId = $request->schedule_id;

            $schedule = ConsultantSchedule::findOrFail($scheduleId);

            // Check if slot is already booked
            if ($schedule->status === 'booked') {
                return response()->json([
                    'success' => false,
                    'message' => 'This slot is already booked'
                ], 400);
            }

            // Check duplicate booking for same date & consultant
            $existingBooking = Appointment::where('student_id', $studentId)
                ->whereHas('schedule', function($q) use ($schedule) {
                    $q->where('consultant_id', $schedule->consultant_id)
                      ->where('slot_date', $schedule->slot_date);
                })
                ->first();

            if ($existingBooking) {
                return response()->json([
                    'success' => false,
                    'message' => 'You already have a booking with this consultant on this date'
                ], 400);
            }

            // Create appointment
            $appointment = Appointment::create([
                'schedule_id' => $scheduleId,
                'student_id' => $studentId,
                'status' => 'confirmed',
                'meeting_type' => $request->meeting_type,
                'student_notes' => $request->student_notes
            ]);

            // Update schedule status
            $schedule->update(['status' => 'booked']);

            return response()->json([
                'success' => true,
                'message' => 'Appointment booked successfully',
                'data' => $appointment->load(['schedule.consultant:id,full_name,email'])
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getMyAppointments()
    {
        try {
            $studentId = auth()->id();

            $appointments = Appointment::where('student_id', $studentId)
                ->with(['schedule.consultant:id,full_name,email'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function($appointment) {
                    return [
                        'id' => $appointment->id,
                        'status' => $appointment->status,
                        'meeting_type' => $appointment->meeting_type,
                        'date' => $appointment->schedule->slot_date,
                        'start_time' => $appointment->schedule->start_time,
                        'end_time' => $appointment->schedule->end_time,
                        'consultant' => $appointment->schedule->consultant->only(['id', 'full_name', 'email']),
                        'meeting_link' => $appointment->meeting_link,
                        'student_notes' => $appointment->student_notes,
                        'created_at' => $appointment->created_at->format('Y-m-d H:i:s')
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $appointments
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getConsultantAppointments()
    {
        try {
            $consultantId = auth()->id();

            $appointments = Appointment::whereHas('schedule', function($query) use ($consultantId) {
                    $query->where('consultant_id', $consultantId);
                })
                ->with(['schedule', 'student:id,full_name,email,profile_photo_url'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function($appointment) {
                    return [
                        'id' => $appointment->id,
                        'status' => $appointment->status,
                        'meeting_type' => $appointment->meeting_type,
                        'date' => $appointment->schedule->slot_date,
                        'start_time' => $appointment->schedule->start_time,
                        'end_time' => $appointment->schedule->end_time,
                        'student' => $appointment->student->only(['id', 'full_name', 'email', 'profile_photo_url']),
                        'meeting_link' => $appointment->meeting_link,
                        'consultant_notes' => $appointment->consultant_notes,
                        'created_at' => $appointment->created_at->format('Y-m-d H:i:s')
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $appointments
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
