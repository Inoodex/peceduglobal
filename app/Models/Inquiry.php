<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

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
        'additional_info_file_name',
        'additional_info_file_path',
        'status',
        'admin_notes',
    ];

    protected $casts = [
        'additional_info' => 'array',
    ];

    protected $appends = [
        'additional_info_file_url',
    ];

    public function getAdditionalInfoFileUrlAttribute()
    {
        return $this->additional_info_file_path ? Storage::url($this->additional_info_file_path) : null;
    }
}
