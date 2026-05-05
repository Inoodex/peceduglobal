<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BlogPostResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'blog_category_id' => $this->blog_category_id,
            'category' => new BlogCategoryResource($this->whenLoaded('category')),
            'excerpt' => $this->excerpt,
            'content' => $this->content,
            'featured_image_url' => $this->featured_image_url,
            'featured_image_alt' => $this->featured_image_alt,
            'status' => $this->status,
            'published_at' => $this->published_at ? $this->published_at->toDateTimeString() : null,
            'author' => new UserResource($this->whenLoaded('author')),
            'reviewer' => new UserResource($this->whenLoaded('reviewer')),
            'seo' => [
                'meta_title' => $this->meta_title,
                'meta_description' => $this->meta_description,
                'focus_keyword' => $this->focus_keyword,
            ],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
