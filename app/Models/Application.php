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
        'university_id',
        'course_name',
        'status',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(StudentProfile::class);
    }

    public function university(): BelongsTo
    {
        return $this->belongsTo(University::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }
}
