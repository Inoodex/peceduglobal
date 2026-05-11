<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Application extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'consultant_id',
        'university_id',
        'course_id',
        'intake_id',
        'application_number',
        'course_name',
        'application_type',
        'status',
        'document_checklist',
        'consultant_remarks',
        'remarks',
    ];

    protected $casts = [
        'document_checklist' => 'array',
        'consultant_remarks' => 'array',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(StudentProfile::class);
    }

    public function consultant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'consultant_id');
    }

    public function university(): BelongsTo
    {
        return $this->belongsTo(University::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function intake(): BelongsTo
    {
        return $this->belongsTo(CourseIntake::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }
}
