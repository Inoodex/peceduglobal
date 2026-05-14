<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimeSlot extends Model
{
    protected $fillable = [
        'slot_date',
        'start_time',
        'end_time',
        'day_of_week',
        'status'
    ];

    protected $casts = [
        'slot_date' => 'date'
    ];

    public function availabilities()
    {
        return $this->hasMany(ConsultantAvailability::class, 'slot_id');
    }
}
