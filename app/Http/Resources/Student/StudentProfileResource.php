<?php

namespace App\Http\Resources\Student;

use Illuminate\Http\Resources\Json\JsonResource;

class StudentProfileResource extends JsonResource
{
    public function toArray($request): array
    {
        $user = $this->user;
        
        // Split full name into first name and last name
        $nameParts = explode(' ', $user->full_name ?? '', 2);
        $firstName = $nameParts[0] ?? '';
        $lastName = $nameParts[1] ?? '';

        // Unpack documents
        $documents = [];
        if ($this->documents) {
            $files = is_array($this->documents) ? $this->documents : (json_decode($this->documents, true) ?: []);
            foreach ($files as $file) {
                if ($file) {
                    $documents[] = [
                        'file_name' => basename($file),
                        'path' => $file,
                        'url' => asset('storage/' . $file),
                    ];
                }
            }
        }

        // Unpack translation documents
        $translationDocuments = [];
        if ($this->translation_documents) {
            $files = is_array($this->translation_documents) ? $this->translation_documents : (json_decode($this->translation_documents, true) ?: []);
            foreach ($files as $file) {
                if ($file) {
                    $translationDocuments[] = [
                        'file_name' => basename($file),
                        'path' => $file,
                        'url' => asset('storage/' . $file),
                    ];
                }
            }
        }

        return [
            'id' => $this->id,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'full_name' => $user->full_name ?? '',
            'email' => $user->email ?? '',
            'phone' => $user->phone ?? $this->phone,
            'country' => $user->country_of_origin ?? '',
            'nationality' => $user->nationality ?? '',
            'profile_photo_url' => $user->profile_photo_url ? (str_starts_with($user->profile_photo_url, 'http') ? $user->profile_photo_url : asset($user->profile_photo_url)) : null,
            'address' => $this->address,
            
            // Rich academic details
            'cgpa' => $this->cgpa,
            'last_education_level' => $this->last_education_level,
            'ielts_score' => $this->ielts_score,
            'father_name' => $this->father_name,
            'mother_name' => $this->mother_name,
            'sponsor_phone' => $this->sponsor_phone,
            'passport_number' => $this->passport_number,
            'passport_validity' => $this->passport_validity,
            'date_of_birth' => $this->date_of_birth,
            'country_id' => $this->country_id,
            'course_level_id' => $this->course_level_id,
            'university_id' => $this->university_id,
            'course_id' => $this->course_id,
            'course_intake_id' => $this->course_intake_id,

            // Document Lists
            'documents' => $documents,
            'translation_documents' => $translationDocuments,
        ];
    }
}
