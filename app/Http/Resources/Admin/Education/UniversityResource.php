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
            'logo' => $this->logo,
            'country_id' => $this->country_id,
            'is_popular' => $this->is_popular,
            'is_partner' => $this->is_partner,
        ];
    }
}
