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
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'cgpa' => 'nullable|numeric|between:0,5',
            'ielts_score' => 'nullable|integer|between:0,9',
        ];
    }
}
