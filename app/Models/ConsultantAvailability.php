<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConsultantAvailability extends Model
{
    protected $table = 'consultant_availability';

    protected $fillable = [
        'slot_id',
        'consultant_id',
        'status'
    ];

    public function slot()
    {
        return $this->belongsTo(TimeSlot::class, 'slot_id');
    }

    public function consultant()
    {
        return $this->belongsTo(User::class, 'consultant_id');
    }

    public function appointment()
    {
        return $this->hasOne(Appointment::class, 'availability_id');
    }
}
