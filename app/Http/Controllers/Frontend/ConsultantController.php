<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\ConsultantSchedule;
use App\Models\Appointment;
use App\Models\User;
use App\Services\NotificationService;
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
            // Get all slots for the consultant on the specified date
            $slots = ConsultantSchedule::where('consultant_id', $request->consultant_id)
                ->where('slot_date', $request->date)
                ->with('appointment') // Load appointment data to check if booked
                ->get()
                ->map(function ($slot) {
                    // Add a computed field to indicate if the slot is booked
                    $slot->is_booked = $slot->status === 'booked' || $slot->appointment !== null;
                    return $slot;
                });

            return response()->json(['success' => true, 'data' => $slots], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Get all available slots for any consultant on a specific date.
     */
    public function getSlotsByDate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $slots = ConsultantSchedule::where('slot_date', $request->date)
                ->where('status', 'available')
                ->orderBy('start_time')
                ->get()
                ->map(function ($slot) {
                    return [
                        'id'         => $slot->id,
                        'slot_date'  => $slot->slot_date,
                        'start_time' => $slot->start_time,
                        'end_time'   => $slot->end_time,
                    ];
                });

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
     * Get available dates for a specific consultant.
     */
    public function getAvailableDates(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'consultant_id' => 'required|exists:users,id',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $startDate = Carbon::create($request->year, $request->month, 1);
            $endDate = $startDate->copy()->endOfMonth();
            
            $availableDates = ConsultantSchedule::where('consultant_id', $request->consultant_id)
                ->whereBetween('slot_date', [$startDate, $endDate])
                ->where('status', 'available')
                ->select('slot_date')
                ->distinct()
                ->pluck('slot_date');

            return response()->json(['success' => true, 'data' => $availableDates], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }



    /**
     * Book an appointment (Public/Frontend side).
     * Guest users: name, email, phone stored directly in appointments table.
     * Logged-in users: student_id is used.
     */
    public function bookAppointment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'schedule_id'   => 'required|exists:consultant_schedules,id',
            'meeting_type'  => 'nullable|in:online,physical',
            'student_id'    => 'nullable|exists:users,id',
            'name'          => 'required_without:student_id|string|max:255',
            'email'         => 'required_without:student_id|email|max:255',
            'phone'         => 'nullable|string|max:20',
            'student_notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            return \DB::transaction(function () use ($request) {
                $schedule = ConsultantSchedule::where('id', $request->schedule_id)
                    ->lockForUpdate()
                    ->first();

                if (!$schedule) {
                    return response()->json(['success' => false, 'message' => 'Schedule not found.'], 404);
                }

                if ($schedule->status === 'booked') {
                    return response()->json(['success' => false, 'message' => 'This slot is already booked.'], 400);
                }

                $existingAppointment = Appointment::where('schedule_id', $schedule->id)->first();
                if ($existingAppointment) {
                    $schedule->update(['status' => 'booked']);
                    return response()->json(['success' => false, 'message' => 'This slot is already booked.'], 400);
                }

                // Build appointment data
                $appointmentData = [
                    'schedule_id'   => $schedule->id,
                    'student_id'    => $request->student_id ?? null,
                    'status'        => 'pending',
                    'meeting_type'  => $request->meeting_type ?? 'online',
                    'student_notes' => $request->student_notes,
                ];

                // If no logged-in student, store guest contact info directly
                if (!$request->student_id) {
                    $appointmentData['guest_name']  = $request->name;
                    $appointmentData['guest_email'] = $request->email;
                    $appointmentData['guest_phone'] = $request->phone;
                }

                $appointment = Appointment::create($appointmentData);
                $schedule->update(['status' => 'booked']);
                $appointment->load(['schedule.consultant', 'student']);

                // Notify the consultant about the new booking
                $bookerName  = $appointment->student?->full_name ?? $request->name ?? 'A guest';
                $bookerEmail = $appointment->student?->email ?? $request->email ?? '';
                NotificationService::forUser(
                    $schedule->consultant_id,
                    'appointment_booked',
                    "New appointment booked by {$bookerName}",
                    "Email: {$bookerEmail} · Date: {$schedule->slot_date} {$schedule->start_time}",
                    '/dashboard/consultant/appointments',
                    ['appointmentId' => $appointment->id]
                );

                // Notify all admins about the new booking
                NotificationService::toAllAdmins(
                    'appointment_booked',
                    "New appointment booked by {$bookerName}",
                    "Email: {$bookerEmail} · Date: {$schedule->slot_date} {$schedule->start_time}",
                    '/dashboard/booking-manager',
                    ['appointmentId' => $appointment->id]
                );

                return response()->json([
                    'success' => true,
                    'message' => 'Appointment booked successfully! We will contact you soon.',
                    'data'    => $appointment
                ], 201);
            });
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Booking failed. Please try again.'], 500);
        }
    }
}
