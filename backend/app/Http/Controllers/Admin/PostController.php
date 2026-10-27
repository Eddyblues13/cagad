<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PostStatus;
use App\Exceptions\CloudinaryException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PostRequest;
use App\Models\Category;
use App\Models\Post;
use App\Services\Cloudinary;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PostController extends Controller
{
    public function __construct(private readonly Cloudinary $cloudinary) {}

    public function index(Request $request): View
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

        return view('admin.posts.index', [
            'posts' => $posts,
            'categories' => Category::orderBy('name')->get(),
            'statusCounts' => [
                '' => Post::count(),
                'published' => Post::published()->count(),
                'scheduled' => Post::scheduled()->count(),
                'draft' => Post::draft()->count(),
            ],
        ]);
    }

    public function create(Request $request): View
    {
        return view('admin.posts.create', [
            'post' => new Post([
                'status' => PostStatus::Draft,
                'author_name' => $request->user()->name,
            ]),
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function store(PostRequest $request): RedirectResponse
    {
        $post = new Post($request->safe()->except(['cover_image', 'remove_cover']));
        $post->admin()->associate($request->user());

        try {
            $this->applyCoverImage($request, $post);
        } catch (CloudinaryException $exception) {
            return back()->withInput()->withErrors(['cover_image' => "Upload failed: {$exception->getMessage()}"]);
        }

        $post->save();

        return to_route('admin.posts.edit', $post)->with('status', 'Post created.');
    }

    public function show(Post $post): View
    {
        return view('admin.posts.show', ['post' => $post->load('category')]);
    }

    public function edit(Post $post): View
    {
        return view('admin.posts.edit', [
            'post' => $post->load('admin'),
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function update(PostRequest $request, Post $post): RedirectResponse
    {
        $previousCover = $post->cover_image_public_id;

        $post->fill($request->safe()->except(['cover_image', 'remove_cover']));

        try {
            $this->applyCoverImage($request, $post);
        } catch (CloudinaryException $exception) {
            return back()->withInput()->withErrors(['cover_image' => "Upload failed: {$exception->getMessage()}"]);
        }

        $post->save();

        // Only remove the old image once the post points at its replacement.
        if ($previousCover && $previousCover !== $post->cover_image_public_id) {
            $this->cloudinary->destroy($previousCover);
        }

        return to_route('admin.posts.edit', $post)->with('status', 'Post saved.');
    }

    public function destroy(Post $post): RedirectResponse
    {
        $post->delete();

        if ($post->cover_image_public_id) {
            $this->cloudinary->destroy($post->cover_image_public_id);
        }

        return to_route('admin.posts.index')->with('status', "“{$post->title}” was deleted.");
    }

    /**
     * Upload a new cover to Cloudinary, or clear it when the editor removed it.
     *
     * @throws CloudinaryException
     */
    private function applyCoverImage(PostRequest $request, Post $post): void
    {
        if ($request->hasFile('cover_image')) {
            $image = $this->cloudinary->upload($request->file('cover_image'), 'covers');

            $post->cover_image_url = $image['url'];
            $post->cover_image_public_id = $image['public_id'];
        } elseif ($request->boolean('remove_cover')) {
            $post->cover_image_url = null;
            $post->cover_image_public_id = null;
        }
    }
}
