<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $fillable = [
        'availability_id',
        'student_id',
        'status',
        'meeting_type',
        'meeting_link',
        'remarks'
    ];

    public function availability()
    {
        return $this->belongsTo(ConsultantAvailability::class, 'availability_id');
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Get the consultant through availability
     */
    public function consultant()
    {
        return $this->hasOneThrough(
            User::class,
            ConsultantAvailability::class,
            'id', // Local key on consultant_availability
            'id', // Local key on users
            'availability_id', // Foreign key on appointments
            'consultant_id' // Foreign key on consultant_availability
        );
    }
}
