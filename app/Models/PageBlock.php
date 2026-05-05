<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PageBlock extends Model
{
    use HasFactory;

    protected $fillable = [
        'page_id',
        'block_type',
        'section_title',
        'section_description',
        'sort_order',
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
    ];

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    public function elements(): HasMany
    {
        return $this->hasMany(BlockElement::class)->orderBy('sort_order');
    }
}
