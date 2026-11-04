<?php

namespace App\Http\Resources\Admin;

use App\Models\Post;
use Illuminate\Http\Request;

/**
 * Everything the post editor and preview need.
 *
 * @mixin Post
 */
class PostDetailResource extends PostResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            ...parent::toArray($request),
            'body' => $this->body,
            'cover_image_alt' => $this->cover_image_alt,
            'created_by' => $this->whenLoaded('admin', fn (): ?string => $this->admin?->name),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
