<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $fillable = [
        'schedule_id',
        'student_id',
        'status',
        'meeting_type',
        'meeting_link',
        'student_notes',
        'consultant_notes'
    ];

    public function schedule()
    {
        return $this->belongsTo(ConsultantSchedule::class, 'schedule_id');
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Get the consultant through schedule
     */
    public function consultant()
    {
        return $this->hasOneThrough(
            User::class,
            ConsultantSchedule::class,
            'id',            // Local key on consultant_schedules
            'id',            // Local key on users
            'schedule_id',   // Foreign key on appointments
            'consultant_id'  // Foreign key on consultant_schedules
        );
    }
}
