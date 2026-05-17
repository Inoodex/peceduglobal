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
            'cgpa' => $this->cgpa,
            'ielts_score' => $this->ielts_score,
        ];
    }
}
