<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class University extends Model
{
    use HasFactory;

    protected $fillable = [
        'country_id',
        'name',
        'slug',
        'logo',
        'banner',
        'social_links',
        'location',
        'ranking',
        'tuition_range',
        'total_students',
        'intake_months',
        'description',
        'website',
        'is_popular',
        'is_partner',
    ];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function courses()
    {
        return $this->hasMany(Course::class);
    }

    protected $casts = [
        'social_links' => 'array',
        'is_popular' => 'boolean',
        'is_partner' => 'boolean',
    ];
}
