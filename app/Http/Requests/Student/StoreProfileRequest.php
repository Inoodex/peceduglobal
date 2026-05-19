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
            'first_name'        => 'required|string|max:255',
            'last_name'         => 'required|string|max:255',
            'email'             => 'required|email|max:255|unique:users,email,' . auth()->id() . ',id',
            'phone'             => 'nullable|string|max:20',
            'country'           => 'nullable|string|max:255',
            'nationality'       => 'nullable|string|max:255',
            'image'             => 'nullable|file|mimes:jpeg,png,jpg,gif,svg,webp,avif|max:2048',
            'address'           => 'nullable|string',
            
            // Rich academic fields
            'cgpa'              => 'nullable|numeric|between:0,5',
            'ielts_score'       => 'nullable|numeric|between:0,9',
            'father_name'       => 'nullable|string|max:255',
            'mother_name'       => 'nullable|string|max:255',
            'sponsor_phone'     => 'nullable|string|max:255',
            'passport_number'   => 'nullable|string|max:255',
            'passport_validity' => 'nullable|date',
            'date_of_birth'     => 'nullable|date',
            'country_id'        => 'nullable|integer',
            'university_id'     => 'nullable|integer',
            'course_id'         => 'nullable|integer',
            'course_intake_id'  => 'nullable|integer',

            // Dynamic uploaded documents list
            'documents'         => 'nullable|array',
            'documents.*'       => 'file|max:10240', // 10MB limit per doc
            'translation_docs'  => 'nullable|array',
            'translation_docs.*'=> 'file|max:10240',
        ];
    }
}
