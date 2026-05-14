<?php

namespace App\Http\Controllers\Api\Consultant;

use App\Http\Controllers\Controller;
use App\Models\TimeSlot;
use App\Models\ConsultantAvailability;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class AvailabilityController extends Controller
{
    public function getAvailableSlots(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date|after_or_equal:today'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $date = $request->date;
            $consultantId = auth()->id();

            $slots = TimeSlot::where('slot_date', $date)
                ->where('status', 'open')
                ->with(['availabilities' => function($query) use ($consultantId) {
                    $query->where('consultant_id', $consultantId);
                }])
                ->get()
                ->map(function($slot) use ($consultantId) {
                    $availability = $slot->availabilities->first();
                    return [
                        'id' => $slot->id,
                        'date' => $slot->slot_date,
                        'start_time' => $slot->start_time,
                        'end_time' => $slot->end_time,
                        'is_claimed' => $availability ? true : false,
                        'availability_id' => $availability ? $availability->id : null,
                        'status' => $availability ? $availability->status : 'available_for_claim'
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

    public function claimSlot(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'slot_id' => 'required|exists:time_slots,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $consultantId = auth()->id();
            $slotId = $request->slot_id;

            // Check if already claimed by ANY consultant? 
            // In your design multiple consultants can claim same slot if it's available for claim
            // But usually one consultant only once
            $existing = ConsultantAvailability::where('consultant_id', $consultantId)
                ->where('slot_id', $slotId)
                ->first();

            if ($existing) {
                return response()->json([
                    'success' => false,
                    'message' => 'Slot already claimed by you'
                ], 400);
            }

            // Create availability
            $availability = ConsultantAvailability::create([
                'slot_id' => $slotId,
                'consultant_id' => $consultantId,
                'status' => 'available'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Slot claimed successfully',
                'data' => $availability
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function releaseSlot(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'availability_id' => 'required|exists:consultant_availability,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $consultantId = auth()->id();
            $availability = ConsultantAvailability::where('id', $request->availability_id)
                ->where('consultant_id', $consultantId)
                ->first();

            if (!$availability) {
                return response()->json([
                    'success' => false,
                    'message' => 'Availability not found'
                ], 404);
            }

            // Check if already booked
            if ($availability->status === 'booked') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot release booked slot'
                ], 400);
            }

            $availability->delete();

            return response()->json([
                'success' => true,
                'message' => 'Slot released successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getMyClaimedSlots(Request $request)
    {
        try {
            $consultantId = auth()->id();
            $date = $request->date ?? now()->toDateString();

            $availabilities = ConsultantAvailability::where('consultant_id', $consultantId)
                ->whereHas('slot', function($query) use ($date) {
                    $query->where('slot_date', '>=', $date);
                })
                ->with('slot')
                ->get()
                ->map(function($availability) {
                    return [
                        'id' => $availability->id,
                        'slot_id' => $availability->slot_id,
                        'date' => $availability->slot->slot_date,
                        'start_time' => $availability->slot->start_time,
                        'end_time' => $availability->slot->end_time,
                        'status' => $availability->status,
                        'day_of_week' => $availability->slot->day_of_week
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $availabilities
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
