<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

class BlockResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'page_id' => $this->page_id,
            'block_type' => $this->block_type,
            'section_title' => $this->section_title,
            'section_description' => $this->section_description,
            'sort_order' => $this->sort_order,
            'settings' => $this->settings,
            'elements' => BlockElementResource::collection($this->whenLoaded('elements')),
        ];
    }
}
