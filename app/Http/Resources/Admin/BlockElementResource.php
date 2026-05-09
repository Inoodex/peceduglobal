<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BlockElementResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'page_block_id' => $this->page_block_id,
            'element_title' => $this->element_title,
            'element_body' => $this->element_body,
            'image_path' => $this->image_path ? asset('storage/' . $this->image_path) : null,
            'link_url' => $this->link_url,
            'sort_order' => $this->sort_order,
            'page_block' => $this->whenLoaded('pageBlock'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
