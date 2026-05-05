<?php

namespace App\Http\Resources\Student;

use Illuminate\Http\Resources\Json\JsonResource;

class ApplicationResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'university' => $this->university ? [
                'id' => $this->university->id,
                'name' => $this->university->name,
            ] : null,
            'course_name' => $this->course_name,
            'status' => $this->status,
            'created_at' => $this->created_at,
        ];
    }
}
