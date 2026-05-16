<?php

namespace App\Http\Resources\Frontend;

use Illuminate\Http\Resources\Json\JsonResource;

class UniversityResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'logo' => $this->logo ? (str_starts_with($this->logo, '/storage/') ? $this->logo : '/storage/' . $this->logo) : null,
            'banner' => $this->banner ? (str_starts_with($this->banner, '/storage/') ? $this->banner : '/storage/' . $this->banner) : null,
            'country' => $this->country ? [
                'id' => $this->country->id,
                'name' => $this->country->name,
            ] : null,
            'location' => $this->location,
            'ranking' => $this->ranking,
            'tuition_range' => $this->tuition_range,
            'intake_months' => $this->intake_months,
            'website' => $this->website,
            'description' => $this->description,
            'social_links' => $this->social_links,
            'courses' => $this->whenLoaded('courses', function() {
                return $this->courses->map(function($course) {
                    return [
                        'id' => $course->id,
                        'name' => $course->name,
                        'level' => $course->courseLevel?->name,
                        'tuition_fee' => $course->tuition_fee,
                        'duration' => $course->duration,
                    ];
                });
            }),
        ];
    }
}
