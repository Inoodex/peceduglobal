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
            'slug' => 'required|string|unique:universities,slug',
            'country_id' => 'required|exists:countries,id',
            'logo' => 'nullable|image|max:2048',
            'is_popular' => 'boolean',
            'is_partner' => 'boolean',
        ];
    }
}
