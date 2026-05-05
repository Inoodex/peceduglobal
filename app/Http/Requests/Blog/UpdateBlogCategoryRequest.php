<?php

namespace App\Http\Requests\Blog;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBlogCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:100|unique:blog_categories,name,' . ($this->route('blog_category')->id ?? $this->route('blog_category')),
            'slug' => 'nullable|string|max:100|unique:blog_categories,slug,' . ($this->route('blog_category')->id ?? $this->route('blog_category')),
            'description' => 'nullable|string',
            'status' => 'in:active,inactive',
            'display_order' => 'integer',
        ];
    }
}
