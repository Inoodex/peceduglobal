<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Application extends Model
{
    use HasFactory;

    protected static function booted()
    {
        static::creating(function ($application) {
            if (empty($application->application_number)) {
                $application->application_number = 'APP-' . strtoupper(Str::random(8));
            }
        });
    }

    protected $fillable = [
        'student_id',
        'consultant_id',
        'university_id',
        'country_id',
        'course_id',
        'course_level_id',
        'intake_id',
        'application_number',
        'university_reference_id',
        'course_name',
        'status',
        'notes',
        'rejection_reason',
        'applied_at',
    ];

    protected $casts = [
        'applied_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(StudentProfile::class, 'student_id');
    }

    public function consultant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'consultant_id');
    }

    public function university(): BelongsTo
    {
        return $this->belongsTo(University::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function courseLevel(): BelongsTo
    {
        return $this->belongsTo(CourseLevel::class, 'course_level_id');
    }

    public function intake(): BelongsTo
    {
        return $this->belongsTo(CourseIntake::class, 'intake_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }
}
