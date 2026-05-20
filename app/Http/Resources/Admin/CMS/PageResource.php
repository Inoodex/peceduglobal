<?php

namespace App\Http\Resources\Admin\CMS;

use Illuminate\Http\Resources\Json\JsonResource;

class PageResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'page_type' => $this->page_type,
            'country_id' => $this->country_id,
            'country' => $this->whenLoaded('country'),
            'university_id' => $this->university_id,
            'university' => $this->whenLoaded('university'),
            'parent_id' => $this->parent_id,
            'parent' => $this->whenLoaded('parent'),
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'thumbnail' => $this->thumbnail,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
        ];
    }
}
