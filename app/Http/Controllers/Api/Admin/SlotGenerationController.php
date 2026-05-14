<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ScheduleTemplate;
use App\Models\TimeSlot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class SlotGenerationController extends Controller
{
    public function generateMonthlySlots(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'year' => 'required|integer|min:2024|max:2030',
            'month' => 'required|integer|min:1|max:12',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $year = $request->year;
            $month = str_pad($request->month, 2, '0', STR_PAD_LEFT);
            $startDate = Carbon::createFromDate($year, $request->month, 1);
            $endDate = $startDate->copy()->endOfMonth();
            
            $templates = ScheduleTemplate::where('is_active', true)->get();
            
            if ($templates->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active templates found. Please create schedule templates first.'
                ], 400);
            }

            DB::beginTransaction();

            $generatedCount = 0;
            $currentDate = $startDate->copy();

            while ($currentDate <= $endDate) {
                // If it's a past date, skip
                if ($currentDate->isPast() && !$currentDate->isToday()) {
                    $currentDate->addDay();
                    continue;
                }

                $dayOfWeek = $currentDate->format('l');
                $template = $templates->firstWhere('day_of_week', $dayOfWeek);
                
                if ($template) {
                    $slots = $this->generateTimeSlotsForDay($currentDate, $template);
                    foreach ($slots as $slotData) {
                        $existing = TimeSlot::where('slot_date', $slotData['slot_date'])
                            ->where('start_time', $slotData['start_time'])
                            ->where('end_time', $slotData['end_time'])
                            ->first();

                        if (!$existing) {
                            TimeSlot::create($slotData);
                            $generatedCount++;
                        }
                    }
                }
                
                $currentDate->addDay();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Successfully generated {$generatedCount} slots for {$startDate->format('F Y')}",
                'data' => [
                    'total_generated' => $generatedCount,
                    'month' => $startDate->format('F Y'),
                    'year' => $year,
                    'month_number' => $request->month
                ]
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate slots: ' . $e->getMessage()
            ], 500);
        }
    }

    private function generateTimeSlotsForDay(Carbon $date, ScheduleTemplate $template): array
    {
        $slots = [];
        $startTime = Carbon::createFromTimeString($template->start_time);
        $endTime = Carbon::createFromTimeString($template->end_time);
        $breakStart = $template->break_start ? Carbon::createFromTimeString($template->break_start) : null;
        $breakEnd = $template->break_end ? Carbon::createFromTimeString($template->break_end) : null;
        $duration = $template->slot_duration;
        $buffer = $template->buffer_time ?? 0;

        $currentSlotStart = $startTime->copy();

        while ($currentSlotStart < $endTime) {
            $currentSlotEnd = $currentSlotStart->copy()->addMinutes($duration);
            
            if ($currentSlotEnd <= $endTime) {
                // Skip slots that fall within break time
                if ($breakStart && $breakEnd) {
                    if ($currentSlotStart >= $breakStart && $currentSlotStart < $breakEnd) {
                        $currentSlotStart = $breakEnd->copy();
                        continue;
                    }
                    if ($currentSlotEnd > $breakStart && $currentSlotEnd <= $breakEnd) {
                         $currentSlotStart = $breakEnd->copy();
                         continue;
                    }
                }
                
                $slots[] = [
                    'slot_date' => $date->format('Y-m-d'),
                    'start_time' => $currentSlotStart->format('H:i:s'),
                    'end_time' => $currentSlotEnd->format('H:i:s'),
                    'day_of_week' => $date->format('l'),
                    'status' => 'open',
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }
            
            // Move to next slot start, adding duration + buffer
            $currentSlotStart = $currentSlotEnd->copy()->addMinutes($buffer);
        }

        return $slots;
    }

    public function getGeneratedMonths()
    {
        try {
            $months = TimeSlot::selectRaw('YEAR(slot_date) as year, MONTH(slot_date) as month, MONTHNAME(slot_date) as month_name')
                ->groupBy('year', 'month', 'month_name')
                ->orderBy('year', 'desc')
                ->orderBy('month', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $months
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
