<?php

namespace App\Http\Resources\Frontend;

use Illuminate\Http\Resources\Json\JsonResource;

class BlogResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt,
            // 'content' => $this->content,
            'featured_image' => $this->featured_image_url ? (str_starts_with($this->featured_image_url, '/storage/') ? $this->featured_image_url : '/storage/' . $this->featured_image_url) : null,
            'featured_image_alt' => $this->featured_image_alt,
            'category' => $this->category ? [
                'id' => $this->category->id,
                'name' => $this->category->name,
            ] : null,
            'author' => $this->author ? [
                'id' => $this->author->id,
                'name' => $this->author->full_name,
                'image' => $this->author->profile_photo_url ? (str_starts_with($this->author->profile_photo_url, '/storage/') ? $this->author->profile_photo_url : '/storage/' . $this->author->profile_photo_url) : null,
            ] : null,
            'published_at' => $this->published_at ? $this->published_at->format('M d, Y') : $this->created_at->format('M d, Y'),
            'meta' => [
                'title' => $this->meta_title,
                'description' => $this->meta_description,
                'keyword' => $this->focus_keyword,
            ]
        ];
    }
}
