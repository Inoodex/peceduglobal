<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inquiry extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'type',
        'additional_info',
        'status',
        'admin_notes',
    ];

    protected $casts = [
        'additional_info' => 'array',
    ];
}
