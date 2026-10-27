<?php

namespace App\Http\Resources;

use App\Models\Post;
use Illuminate\Http\Request;

/**
 * A single post with its full, already-sanitised HTML body.
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
            'url' => $this->frontendUrl(),
        ];
    }
}
