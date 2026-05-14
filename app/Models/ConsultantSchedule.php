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
}
