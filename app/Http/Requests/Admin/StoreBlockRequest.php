<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreBlockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'page_id' => 'required|exists:pages,id',
            'block_type' => 'required|string',
            'section_title' => 'nullable|string|max:255',
            'section_description' => 'nullable|string',
            'sort_order' => 'nullable|integer',
            'settings' => 'nullable|array',
        ];
    }
}
