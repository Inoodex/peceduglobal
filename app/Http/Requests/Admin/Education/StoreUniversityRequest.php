<?php

namespace App\Http\Requests\Admin\Education;

use Illuminate\Foundation\Http\FormRequest;

class StoreUniversityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'country_id' => 'required|exists:countries,id',
            'logo' => 'nullable|file|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'banner' => 'nullable|file|mimes:jpeg,png,jpg,gif,svg,webp|max:4096',
            'social_links' => 'nullable|array',
            'is_popular' => 'nullable',
            'is_partner' => 'nullable',
            'location' => 'nullable|string',
            'ranking' => 'nullable|string',
            'tuition_range' => 'nullable|string',
            'intake_months' => 'nullable|string',
            'website' => 'nullable|string',
            'description' => 'nullable|string',
        ];
    }
}
