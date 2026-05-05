<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlockElement extends Model
{
    use HasFactory;

    protected $fillable = [
        'page_block_id',
        'element_title',
        'element_body',
        'image_path',
        'link_url',
        'sort_order',
    ];

    public function block(): BelongsTo
    {
        return $this->belongsTo(PageBlock::class, 'page_block_id');
    }
}
