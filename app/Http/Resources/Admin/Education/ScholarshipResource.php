<?php

namespace App\Http\Resources\Admin\Education;

use Illuminate\Http\Resources\Json\JsonResource;

class ScholarshipResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'amount' => $this->amount,
            'deadline' => $this->deadline,
            'is_active' => $this->is_active,
        ];
    }
}
