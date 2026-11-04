<?php

namespace App\Http\Resources\Admin;

use App\Http\Resources\CategoryResource;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A post as the admin lists it, drafts and scheduled posts included.
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
            'excerpt' => $this->excerpt,
            'author_name' => $this->author_name,
            'status' => $this->status->value,
            'status_label' => $this->statusLabel(),
            'is_live' => $this->isLive(),
            'is_scheduled' => $this->isScheduled(),
            'category_id' => $this->category_id,
            'category' => CategoryResource::make($this->whenLoaded('category')),
            'cover_image' => $this->coverImage(),
            'reading_time' => $this->readingTime(),
            'url' => $this->frontendUrl(),
            'published_at' => $this->published_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }

    /**
     * Cloudinary renditions sized for the list, the editor and the preview.
     *
     * @return array{thumbnail: string, editor: string, full: string}|null
     */
    private function coverImage(): ?array
    {
        if (! $this->cover_image_url) {
            return null;
        }

        return [
            'thumbnail' => $this->coverUrl('c_fill,g_auto,w_128,h_88'),
            'editor' => $this->coverUrl('c_fill,g_auto,w_640,h_400'),
            'full' => $this->coverUrl('c_limit,w_1600'),
        ];
    }
}
