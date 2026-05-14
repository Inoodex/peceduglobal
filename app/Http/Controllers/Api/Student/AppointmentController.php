<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\ConsultantAvailability;
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
            $consultantId = $request->consultant_id;
            $date = $request->date;

            $slots = ConsultantAvailability::where('consultant_id', $consultantId)
                ->where('status', 'available')
                ->whereHas('slot', function($query) use ($date) {
                    $query->where('slot_date', $date)
                          ->where('status', 'open');
                })
                ->with('slot')
                ->get()
                ->map(function($availability) {
                    return [
                        'availability_id' => $availability->id,
                        'slot_id' => $availability->slot_id,
                        'date' => $availability->slot->slot_date,
                        'start_time' => $availability->slot->start_time,
                        'end_time' => $availability->slot->end_time,
                        'day_of_week' => $availability->slot->day_of_week
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
            $consultantId = $request->consultant_id;
            $today = now()->toDateString();

            $nextDate = ConsultantAvailability::where('consultant_id', $consultantId)
                ->where('status', 'available')
                ->whereHas('slot', function($query) use ($today) {
                    $query->where('slot_date', '>', $today)
                          ->where('status', 'open');
                })
                ->join('time_slots', 'consultant_availability.slot_id', '=', 'time_slots.id')
                ->orderBy('time_slots.slot_date')
                ->select('time_slots.slot_date')
                ->first();

            if (!$nextDate) {
                return response()->json([
                    'success' => true,
                    'message' => 'No future availability found for this consultant',
                    'data' => null
                ], 200);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'next_available_date' => $nextDate->slot_date,
                    'formatted_date' => Carbon::parse($nextDate->slot_date)->format('F j, Y')
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
            'availability_id' => 'required|exists:consultant_availability,id',
            'meeting_type' => 'required|in:online,physical',
            'remarks' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $studentId = auth()->id();
            $availabilityId = $request->availability_id;

            // Check if slot is already booked
            $existingBooking = Appointment::where('availability_id', $availabilityId)->first();
            if ($existingBooking) {
                return response()->json([
                    'success' => false,
                    'message' => 'This slot is already booked'
                ], 400);
            }

            // Check if student already has booking for this consultant on same date
            $availability = ConsultantAvailability::with('slot')->findOrFail($availabilityId);
            $existingStudentBooking = Appointment::where('student_id', $studentId)
                ->whereHas('availability.slot', function($query) use ($availability) {
                    $query->where('slot_date', $availability->slot->slot_date);
                })
                ->whereHas('availability', function($query) use ($availability) {
                    $query->where('consultant_id', $availability->consultant_id);
                })
                ->first();

            if ($existingStudentBooking) {
                return response()->json([
                    'success' => false,
                    'message' => 'You already have a booking with this consultant on this date'
                ], 400);
            }

            // Create appointment
            $appointment = Appointment::create([
                'availability_id' => $availabilityId,
                'student_id' => $studentId,
                'status' => 'confirmed',
                'meeting_type' => $request->meeting_type,
                'remarks' => $request->remarks
            ]);

            // Update availability status
            $availability->update(['status' => 'booked']);

            return response()->json([
                'success' => true,
                'message' => 'Appointment booked successfully',
                'data' => $appointment->load(['availability.slot', 'availability.consultant:id,full_name,email'])
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
                ->with(['availability.slot', 'availability.consultant:id,full_name,email'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function($appointment) {
                    return [
                        'id' => $appointment->id,
                        'status' => $appointment->status,
                        'meeting_type' => $appointment->meeting_type,
                        'date' => $appointment->availability->slot->slot_date->format('Y-m-d'),
                        'start_time' => $appointment->availability->slot->start_time,
                        'end_time' => $appointment->availability->slot->end_time,
                        'consultant' => $appointment->availability->consultant->only(['id', 'full_name', 'email']),
                        'meeting_link' => $appointment->meeting_link,
                        'remarks' => $appointment->remarks,
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
