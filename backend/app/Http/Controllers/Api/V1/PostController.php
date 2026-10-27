<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostDetailResource;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PostController extends Controller
{
    private const DEFAULT_PER_PAGE = 9;

    private const MAX_PER_PAGE = 24;

    /**
     * Published posts, newest first. Filter with ?category={slug} and ?search={term}.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = min(max($request->integer('per_page', self::DEFAULT_PER_PAGE), 1), self::MAX_PER_PAGE);

        $posts = Post::query()
            ->published()
            ->with('category')
            ->when($request->string('category')->trim()->value(), function (Builder $query, string $category): void {
                $query->whereRelation('category', 'slug', $category);
            })
            ->when($request->string('search')->trim()->value(), function (Builder $query, string $search): void {
                $query->where(fn (Builder $query) => $query
                    ->where('title', 'like', "%{$search}%")
                    ->orWhere('excerpt', 'like', "%{$search}%"));
            })
            ->latest('published_at')
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();

        return PostResource::collection($posts);
    }

    /**
     * One published post by slug, plus three more to read next: posts from the
     * same category first, topped up with the latest posts from any category.
     */
    public function show(string $slug): PostDetailResource
    {
        $post = Post::query()->published()->with('category')->where('slug', $slug)->firstOrFail();

        $related = Post::query()
            ->published()
            ->with('category')
            ->whereKeyNot($post->getKey())
            ->when($post->category_id, fn (Builder $query, int $categoryId) => $query
                ->orderByRaw('CASE WHEN category_id = ? THEN 0 ELSE 1 END', [$categoryId]))
            ->latest('published_at')
            ->limit(3)
            ->get();

        return PostDetailResource::make($post)->additional([
            'related' => PostResource::collection($related),
        ]);
    }
}
