<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'application_id',
        'phone',
        'father_name',
        'mother_name',
        'date_of_birth',
        'gender',
        'passport_number',
        'passport_validity',
        'nationality',
        'alternative_phone',
        'address',
        'sponsor_phone',
        'country_id',
        'university_id',
        'course_id',
        'course_intake_id',
        'preferred_intake',
        'cgpa',
        'ielts_score',
        'documents',
        'translation_documents',
    ];

    protected function casts(): array
    {
        return [
            'documents' => 'array',
            'translation_documents' => 'array',
            'date_of_birth' => 'date',
            'passport_validity' => 'date',
            'cgpa' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function university(): BelongsTo
    {
        return $this->belongsTo(University::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function courseIntake(): BelongsTo
    {
        return $this->belongsTo(CourseIntake::class);
    }

    public function applications()
    {
        return $this->hasMany(Application::class);
    }
}
