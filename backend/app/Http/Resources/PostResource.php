<?php

namespace App\Http\Resources;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A post as it appears in listings: everything except the body.
 *
 * @mixin Post
 */
class PostResource extends JsonResource
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
            'title' => $this->title,
            'slug' => $this->slug,
            'excerpt' => $this->summary(),
            'author' => $this->author_name,
            'category' => CategoryResource::make($this->whenLoaded('category')),
            'cover_image' => $this->coverImage(),
            'reading_time' => $this->readingTime(),
            'published_at' => $this->published_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }

    /**
     * Ready-to-use Cloudinary renditions so the frontend never builds URLs itself.
     *
     * @return array{original: string, alt: string, thumbnail: string, card: string, hero: string}|null
     */
    private function coverImage(): ?array
    {
        if (! $this->cover_image_url) {
            return null;
        }

        return [
            'original' => $this->cover_image_url,
            'alt' => $this->cover_image_alt ?: $this->title,
            'thumbnail' => $this->coverUrl('c_fill,g_auto,w_400,h_260'),
            'card' => $this->coverUrl('c_fill,g_auto,w_800,h_500'),
            'hero' => $this->coverUrl('c_limit,w_1600'),
        ];
    }
}
