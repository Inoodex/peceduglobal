<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConsultantSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'consultant_id',
        'slot_date',
        'start_time',
        'end_time',
        'day_of_week',
        'status'
    ];

    public function consultant()
    {
        return $this->belongsTo(User::class, 'consultant_id');
    }

    public function appointment()
    {
        return $this->hasOne(Appointment::class, 'schedule_id');
    }

    /**
     * Automatically mark slots as 'expired' if their time has passed.
     */
    public function getStatusAttribute($value)
    {
        // Only check slots that are currently 'available'
        if ($value === 'available') {
            // Combine date and time to create a full Carbon instance
            $slotDateTime = \Carbon\Carbon::parse($this->slot_date . ' ' . $this->start_time);
            
            // If the time has already passed, return 'expired' instead of 'available'
            if ($slotDateTime->isPast()) {
                return 'expired';
            }
        }

        return $value;
    }
}
