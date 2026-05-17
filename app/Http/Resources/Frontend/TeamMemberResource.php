<?php

namespace App\Http\Resources\Frontend;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeamMemberResource extends JsonResource
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
            'name' => $this->name,
            'designation' => $this->designation,
            'photo' => $this->photo ? (str_starts_with($this->photo, 'http') ? $this->photo : asset($this->photo)) : null,
            'status' => (bool)$this->status,
            'content' => $this->content,
            'social_links' => $this->social_links ?? [
                'facebook' => '',
                'twitter' => '',
                'linkedin' => '',
                'instagram' => ''
            ],
        ];
    }
}
