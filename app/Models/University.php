<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class University extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'logo',
        'country',
        'is_popular',
        'is_partner',
    ];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }
}
