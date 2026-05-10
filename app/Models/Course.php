<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'university_id',
        'course_level_id',
        'country_id',
        'name',
        'slug',
        'intake',
        'duration',
        'ielts_requirement',
        'tuition_fee',
        'requirements',
        'is_popular',
    ];

    protected $casts = [
        'is_popular' => 'boolean',
    ];

    public function courseLevel(): BelongsTo
    {
        return $this->belongsTo(CourseLevel::class);
    }

    public function university(): BelongsTo
    {
        return $this->belongsTo(University::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }
}
