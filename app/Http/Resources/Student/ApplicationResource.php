<?php

namespace App\Http\Resources\Student;

use Illuminate\Http\Resources\Json\JsonResource;

class ApplicationResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'application_number' => $this->application_number,
            'university_reference_id' => $this->university_reference_id,
            'student' => $this->whenLoaded('student', function() {
                return [
                    'id' => $this->student->id,
                    'full_name' => $this->student->user->full_name,
                ];
            }),
            'university' => $this->whenLoaded('university', function() {
                return [
                    'id' => $this->university->id,
                    'name' => $this->university->name,
                ];
            }),
            'country' => $this->whenLoaded('country', function() {
                return [
                    'id' => $this->country->id,
                    'name' => $this->country->name,
                ];
            }),
            'course' => $this->whenLoaded('course', function() {
                return [
                    'id' => $this->course->id,
                    'name' => $this->course->name,
                ];
            }),
            'intake' => $this->whenLoaded('intake', function() {
                return [
                    'id' => $this->intake->id,
                    'name' => $this->intake->intake_name,
                ];
            }),
            'course_name' => $this->course_name,
            'application_type' => $this->application_type,
            'status' => $this->status,
            'notes' => $this->notes,
            'rejection_reason' => $this->rejection_reason,
            'applied_at' => $this->applied_at,
            'created_at' => $this->created_at,
        ];
    }
}
