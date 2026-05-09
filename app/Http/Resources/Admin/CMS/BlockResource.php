<?php

namespace App\Http\Resources\Admin\CMS;

use Illuminate\Http\Resources\Json\JsonResource;

class BlockResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'page_id' => $this->page_id,
            'page' => new PageResource($this->whenLoaded('page')),
            'block_type' => $this->block_type,
            'section_title' => $this->section_title,
            'section_description' => $this->section_description,
            'sort_order' => $this->sort_order,
            'settings' => $this->settings,
            'elements' => BlockElementResource::collection($this->whenLoaded('elements')),
        ];
    }
}
