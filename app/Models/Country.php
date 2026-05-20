<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'iso_code',
        'phone_code',
        'thumbnail',
        'is_popular',
    ];

    protected $casts = [
        'is_popular' => 'boolean',
    ];

    public function universities()
    {
        return $this->hasMany(University::class);
    }

    public function guidePage()
    {
        return $this->hasOne(Page::class)->where('page_type', 'country_guide')->where('is_active', true);
    }
}
