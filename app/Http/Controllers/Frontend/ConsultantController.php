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
     * Can be done by logged in user (passing student_id) or guest (passing name, email, phone).
     */
    public function bookAppointment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'schedule_id' => 'required|exists:consultant_schedules,id',
            'meeting_type' => 'required|in:online,physical',
            'student_id' => 'nullable|exists:users,id',
            'name' => 'required_without:student_id|string|max:255',
            'email' => 'required_without:student_id|email',
            'phone' => 'nullable|string|max:20',
            'student_notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            return \DB::transaction(function () use ($request) {
                // If student_id is not provided (Guest booking)
                $studentId = $request->student_id;
                
                if (!$studentId) {
                    // Check if email already exists
                    $existingUser = User::where('email', $request->email)->first();
                    
                    if ($existingUser) {
                        $studentId = $existingUser->id;
                    } else {
                        // Create a new student user
                        $newUser = User::create([
                            'full_name' => $request->name,
                            'email' => $request->email,
                            'phone' => $request->phone,
                            'password' => \Hash::make(\Str::random(10)), // Generate random password
                            'role' => 'student',
                            'is_active' => true
                        ]);
                        $studentId = $newUser->id;
                    }
                }

                $schedule = ConsultantSchedule::where('id', $request->schedule_id)
                    ->lockForUpdate()
                    ->first();

                if (!$schedule) {
                    return response()->json(['success' => false, 'message' => 'Schedule not found'], 404);
                }

                if ($schedule->status === 'booked') {
                    return response()->json(['success' => false, 'message' => 'Slot already booked'], 400);
                }

                $existingAppointment = Appointment::where('schedule_id', $schedule->id)->first();
                if ($existingAppointment) {
                    $schedule->update(['status' => 'booked']);
                    return response()->json(['success' => false, 'message' => 'Slot already booked'], 400);
                }

                $appointment = Appointment::create([
                    'schedule_id' => $schedule->id,
                    'student_id' => $studentId,
                    'status' => 'confirmed',
                    'meeting_type' => $request->meeting_type,
                    'student_notes' => $request->student_notes
                ]);

                $schedule->update(['status' => 'booked']);
                $appointment->load(['schedule.consultant', 'student']);

                return response()->json([
                    'success' => true, 
                    'message' => 'Appointment booked successfully!', 
                    'data' => $appointment
                ], 201);
            });
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Booking failed. Please try again.'], 500);
        }
    }
}
