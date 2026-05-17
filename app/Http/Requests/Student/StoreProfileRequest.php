<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;

class StoreProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . auth()->id() . ',id',
            'phone' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:255',
            'nationality' => 'nullable|string|max:255',
            'image' => 'nullable|file|mimes:jpeg,png,jpg,gif,svg,webp,avif|max:2048',
            'address' => 'nullable|string',
            'cgpa' => 'nullable|numeric|between:0,5',
            'ielts_score' => 'nullable|numeric|between:0,9',
        ];
    }
}
