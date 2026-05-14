<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HeroSlider extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'subtitle',
        'button_text',
        'button_url',
        'background_image',
        'floating_images',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'floating_images' => 'array',
    ];
}
