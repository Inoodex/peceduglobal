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
        $imagePathRule = $this->hasFile('image_path')
            ? 'nullable|file|mimetypes:image/*|max:2048'
            : 'nullable|string';

        return [
            'page_block_id' => 'required|exists:page_blocks,id',
            'element_title' => 'nullable|string|max:255',
            'element_body' => 'nullable|string',
            'image_path' => $imagePathRule,
            'link_url' => 'nullable|url',
            'sort_order' => 'nullable|integer',
        ];
    }
}
