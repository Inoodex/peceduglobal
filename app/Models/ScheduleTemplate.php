<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduleTemplate extends Model
{
    protected $fillable = [
        'day_of_week',
        'start_time',
        'end_time',
        'break_start',
        'break_end',
        'slot_duration',
        'buffer_time',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'slot_duration' => 'integer',
        'buffer_time' => 'integer'
    ];
}
