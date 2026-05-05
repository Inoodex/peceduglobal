<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

class BlockElementResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'page_block_id' => $this->page_block_id,
            'element_title' => $this->element_title,
            'element_body' => $this->element_body,
            'image_path' => $this->image_path,
            'link_url' => $this->link_url,
            'sort_order' => $this->sort_order,
        ];
    }
}
