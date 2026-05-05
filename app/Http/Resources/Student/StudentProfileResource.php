<?php

namespace App\Http\Resources\Student;

use Illuminate\Http\Resources\Json\JsonResource;

class StudentProfileResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'phone' => $this->phone,
            'address' => $this->address,
            'cgpa' => $this->cgpa,
            'ielts_score' => $this->ielts_score,
        ];
    }
}
