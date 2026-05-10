<?php

namespace App\Http\Resources\Admin\Education;

use Illuminate\Http\Resources\Json\JsonResource;

class UniversityResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'logo' => $this->logo ? asset('storage/' . $this->logo) : null,
            'banner' => $this->banner ? asset('storage/' . $this->banner) : null,
            'country_id' => $this->country_id,
            'country' => $this->whenLoaded('country', function() {
                return [
                    'id' => $this->country->id,
                    'name' => $this->country->name,
                ];
            }),
            'is_popular' => (bool)$this->is_popular,
            'is_partner' => (bool)$this->is_partner,
            'location' => $this->location,
            'ranking' => $this->ranking,
            'tuition_range' => $this->tuition_range,
            'intake_months' => $this->intake_months,
            'website' => $this->website,
            'description' => $this->description,
            'social_links' => $this->social_links,
        ];
    }
}
