<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseIntake extends Model
{
    protected $fillable = [
        'course_id',
        'university_id',
        'intake_name',
        'application_start_date',
        'application_deadline',
        'class_start_date',
        'status',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function university(): BelongsTo
    {
        return $this->belongsTo(University::class);
    }
}
