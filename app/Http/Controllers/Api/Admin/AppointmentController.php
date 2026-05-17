<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\ConsultantSchedule;
use App\Models\User;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public function getStats()
    {
        try {
            $totalSlots = ConsultantSchedule::count();
            $availableSlots = ConsultantSchedule::where('status', 'available')->count();
            $bookedSlots = ConsultantSchedule::where('status', 'booked')->count();
            $confirmedAppointments = Appointment::where('status', 'confirmed')->count();

            // Get consultant specific stats
            $consultants = User::where('role', 'consultant')->get()->map(function ($consultant) {
                $slots = ConsultantSchedule::where('consultant_id', $consultant->id)->get();
                return [
                    'id' => $consultant->id,
                    'name' => $consultant->full_name,
                    'total_slots' => $slots->count(),
                    'booked_slots' => $slots->where('status', 'booked')->count(),
                    'available_slots' => $slots->where('status', 'available')->count(),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'overview' => [
                        'total_slots' => $totalSlots,
                        'available_slots' => $availableSlots,
                        'booked_slots' => $bookedSlots,
                        'confirmed_appointments' => $confirmedAppointments
                    ],
                    'consultants' => $consultants
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getAllSchedules()
    {
        try {
            $schedules = ConsultantSchedule::with(['consultant:id,full_name,email', 'appointment.student:id,full_name,email'])
                ->orderBy('slot_date', 'desc')
                ->paginate(50);

            return response()->json([
                'success' => true,
                'data' => $schedules
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function index()
    {
        try {
            $appointments = Appointment::with([
                    'schedule.consultant:id,full_name,email',
                    'student:id,full_name,email'
                ])
                ->orderBy('created_at', 'desc')
                ->paginate(50);

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
