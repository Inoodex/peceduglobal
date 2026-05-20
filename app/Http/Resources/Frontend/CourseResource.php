<?php

namespace App\Http\Resources\Frontend;

use Illuminate\Http\Resources\Json\JsonResource;

class CourseResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'university' => [
                'id' => $this->university?->id,
                'name' => $this->university?->name,
                'logo' => $this->university?->logo ? (str_starts_with($this->university->logo, '/storage/') ? $this->university->logo : '/storage/' . $this->university->logo) : null,
                'country' => $this->university?->country?->name,
            ],
            'level' => $this->courseLevel?->name ?? ($this->level ?? null),
            'intake' => $this->intake ?? null,
            'duration' => $this->duration,
            'tuition_fee' => $this->tuition_fee,
            'requirements' => $this->requirements,
            'description' => $this->description,
        ];
    }
}
