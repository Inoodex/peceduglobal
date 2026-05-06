<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreCountryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string',
            'iso_code' => 'required|string|max:3',
            'phone_code' => 'required|string|max:10',
            'thumbnail' => 'nullable|image|max:2048',
        ];
    }
}
