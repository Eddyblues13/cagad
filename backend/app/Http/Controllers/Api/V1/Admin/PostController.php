<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Exceptions\CloudinaryException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PostRequest;
use App\Http\Resources\Admin\PostDetailResource;
use App\Http\Resources\Admin\PostResource;
use App\Models\Post;
use App\Services\Cloudinary;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\ValidationException;

class PostController extends Controller
{
    public function __construct(private readonly Cloudinary $cloudinary) {}

    /**
     * All posts, newest change first. Filters: q (title), category (id) and
     * status (published, scheduled or draft). meta.status_counts feeds the tabs.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $posts = Post::query()
            ->with('category')
            ->when($request->string('q')->trim()->value(), function (Builder $query, string $search): void {
                $query->where('title', 'like', "%{$search}%");
            })
            ->when($request->integer('category'), function (Builder $query, int $categoryId): void {
                $query->where('category_id', $categoryId);
            })
            ->when($request->query('status'), function (Builder $query, string $status): void {
                match ($status) {
                    'draft' => $query->draft(),
                    'scheduled' => $query->scheduled(),
                    'published' => $query->published(),
                    default => null,
                };
            })
            ->latest('updated_at')
            ->paginate(15)
            ->withQueryString();

        return PostResource::collection($posts)->additional([
            'meta' => [
                'status_counts' => [
                    'all' => Post::count(),
                    'published' => Post::published()->count(),
                    'scheduled' => Post::scheduled()->count(),
                    'draft' => Post::draft()->count(),
                ],
            ],
        ]);
    }

    /**
     * @throws ValidationException
     */
    public function store(PostRequest $request): PostDetailResource
    {
        $post = new Post($request->safe()->except(['cover_image', 'remove_cover']));
        $post->admin()->associate($request->user());

        $this->applyCoverImage($request, $post);

        $post->save();

        return $this->detail($post)->additional(['message' => 'Post created.']);
    }

    public function show(Post $post): PostDetailResource
    {
        return $this->detail($post);
    }

    /**
     * @throws ValidationException
     */
    public function update(PostRequest $request, Post $post): PostDetailResource
    {
        $previousCover = $post->cover_image_public_id;

        $post->fill($request->safe()->except(['cover_image', 'remove_cover']));

        $this->applyCoverImage($request, $post);

        $post->save();

        // Only remove the old image once the post points at its replacement.
        if ($previousCover && $previousCover !== $post->cover_image_public_id) {
            $this->cloudinary->destroy($previousCover);
        }

        return $this->detail($post)->additional(['message' => 'Post saved.']);
    }

    public function destroy(Post $post): JsonResponse
    {
        $post->delete();

        if ($post->cover_image_public_id) {
            $this->cloudinary->destroy($post->cover_image_public_id);
        }

        return response()->json(['message' => "“{$post->title}” was deleted."]);
    }

    private function detail(Post $post): PostDetailResource
    {
        return PostDetailResource::make($post->load(['category', 'admin']));
    }

    /**
     * Upload a new cover to Cloudinary, or clear it when the editor removed it.
     * A failed upload is reported against the cover field and nothing is saved.
     *
     * @throws ValidationException
     */
    private function applyCoverImage(PostRequest $request, Post $post): void
    {
        if ($request->hasFile('cover_image')) {
            try {
                $image = $this->cloudinary->upload($request->file('cover_image'), 'covers');
            } catch (CloudinaryException $exception) {
                throw ValidationException::withMessages(['cover_image' => "Upload failed: {$exception->getMessage()}"]);
            }

            $post->cover_image_url = $image['url'];
            $post->cover_image_public_id = $image['public_id'];
        } elseif ($request->boolean('remove_cover')) {
            $post->cover_image_url = null;
            $post->cover_image_public_id = null;
        }
    }
}
