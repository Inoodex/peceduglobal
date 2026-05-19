<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreElementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'page_block_id' => 'required|exists:page_blocks,id',
            'element_type' => 'required|string|in:text,image,button,icon',
            'element_title' => 'nullable|string|max:255',
            'element_body' => 'nullable|string',
            'images' => 'nullable|array',
            'images.*' => 'nullable|file|mimetypes:image/jpeg,image/png,image/gif,image/webp,image/avif,image/svg+xml,image/bmp,image/vnd.microsoft.icon,image/x-icon,image/tiff|max:5120',
            'link_url' => 'nullable|url',
            'sort_order' => 'nullable|integer',
        ];
    }
}
