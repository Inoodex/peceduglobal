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
    ];

    public function universities()
    {
        return $this->hasMany(University::class);
    }
}
