<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

class CountryResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'iso_code' => $this->iso_code,
            'phone_code' => $this->phone_code,
            'thumbnail' => $this->thumbnail,
        ];
    }
}