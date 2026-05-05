<?php

namespace App\Http\Requests\Admin\Education;

use Illuminate\Foundation\Http\FormRequest;

class StoreScholarshipRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'slug' => 'required|string|unique:scholarships,slug',
            'description' => 'nullable|string',
            'amount' => 'nullable|string',
            'deadline' => 'nullable|date',
            'is_active' => 'boolean',
        ];
    }
}
