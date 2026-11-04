<?php

namespace Tests\Feature;

use App\Enums\PostStatus;
use App\Models\Admin;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AdminPostTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Admin::factory()->create(['name' => 'Ada Editor']);
    }

    public function test_guests_cannot_reach_the_posts(): void
    {
        $this->getJson('/api/v1/admin/posts')->assertUnauthorized();
        $this->postJson('/api/v1/admin/posts', ['title' => 'Nope', 'status' => 'draft'])->assertUnauthorized();

        $this->assertDatabaseCount('posts', 0);
    }

    public function test_the_list_includes_drafts_and_counts_each_status(): void
    {
        $live = Post::factory()->published()->withCover()->create();
        $draft = Post::factory()->create();

        $this->actingAs($this->admin, 'admin')
            ->getJson('/api/v1/admin/posts')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.status_counts', ['all' => 2, 'published' => 1, 'scheduled' => 0, 'draft' => 1])
            ->assertJsonFragment(['title' => $draft->title, 'status_label' => 'Draft'])
            ->assertJsonFragment(['title' => $live->title, 'status_label' => 'Published', 'is_live' => true]);
    }

    public function test_the_list_can_be_filtered_to_scheduled_posts(): void
    {
        $scheduled = Post::factory()->scheduled()->create();
        Post::factory()->published()->create();

        $this->actingAs($this->admin, 'admin')
            ->getJson('/api/v1/admin/posts?status=scheduled')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', $scheduled->title)
            ->assertJsonPath('data.0.status_label', 'Scheduled');
    }

    public function test_the_list_can_be_filtered_by_category_and_title(): void
    {
        $security = Category::factory()->create(['name' => 'Security']);
        $match = Post::factory()->published()->for($security)->create(['title' => 'Border security review']);
        Post::factory()->for($security)->create(['title' => 'Something else']);
        Post::factory()->create(['title' => 'Security elsewhere']);

        $this->actingAs($this->admin, 'admin')
            ->getJson("/api/v1/admin/posts?category={$security->id}&q=security")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $match->id);
    }

    public function test_a_single_post_includes_its_body_and_who_created_it(): void
    {
        $post = Post::factory()->for($this->admin)->create(['body' => '<p>Hello</p>']);

        $this->actingAs($this->admin, 'admin')
            ->getJson("/api/v1/admin/posts/{$post->id}")
            ->assertOk()
            ->assertJsonPath('data.body', '<p>Hello</p>')
            ->assertJsonPath('data.created_by', 'Ada Editor');
    }

    public function test_an_editor_can_publish_a_post_with_a_cover_image(): void
    {
        $this->fakeCloudinaryUpload('cagad/blog/covers/new-cover');
        $category = Category::factory()->create();

        $response = $this->actingAs($this->admin, 'admin')->postJson('/api/v1/admin/posts', [
            'title' => 'Budget Transparency in Nigerian States',
            'category_id' => $category->id,
            'author_name' => 'Dr. Example',
            'body' => '<p>Findings from our review.</p>',
            'cover_image' => UploadedFile::fake()->image('cover.jpg', 1600, 900),
            'cover_image_alt' => 'A state assembly in session',
            'status' => 'published',
        ]);

        $post = Post::sole();
        $response->assertCreated()
            ->assertJsonPath('data.id', $post->id)
            ->assertJsonPath('message', 'Post created.');

        $this->assertSame('budget-transparency-in-nigerian-states', $post->slug);
        $this->assertSame(PostStatus::Published, $post->status);
        $this->assertTrue($post->isLive());
        $this->assertTrue($post->admin->is($this->admin));
        $this->assertSame('cagad/blog/covers/new-cover', $post->cover_image_public_id);
        $this->assertSame('https://res.cloudinary.com/test-cloud/image/upload/v1/cagad/blog/covers/new-cover.jpg', $post->cover_image_url);

        Http::assertSent(fn (Request $request): bool => str_ends_with($request->url(), '/image/upload')
            && collect($request->data())->firstWhere('name', 'folder')['contents'] === 'cagad/blog/covers');
    }

    public function test_a_draft_can_be_saved_without_a_body_but_not_published(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->postJson('/api/v1/admin/posts', ['title' => 'Idea', 'status' => 'draft'])
            ->assertCreated();

        $this->actingAs($this->admin, 'admin')
            ->postJson('/api/v1/admin/posts', ['title' => 'Too soon', 'status' => 'published'])
            ->assertJsonValidationErrors(['body' => 'Write the post before publishing it, or save it as a draft.']);

        $this->assertSame(['Idea'], Post::pluck('title')->all());
    }

    public function test_a_future_publish_date_schedules_the_post(): void
    {
        $this->actingAs($this->admin, 'admin')->postJson('/api/v1/admin/posts', [
            'title' => 'Coming soon',
            'body' => '<p>Later.</p>',
            'status' => 'published',
            'published_at' => now()->addWeek()->format('Y-m-d\TH:i'),
        ])->assertJsonPath('data.status_label', 'Scheduled');

        $post = Post::sole();
        $this->assertTrue($post->isScheduled());
        $this->assertFalse($post->isLive());
    }

    public function test_post_html_is_sanitised_before_it_is_stored(): void
    {
        $this->actingAs($this->admin, 'admin')->postJson('/api/v1/admin/posts', [
            'title' => 'Sanitised',
            'status' => 'draft',
            'body' => '<p onclick="steal()">Hello</p><script>alert(1)</script><a href="javascript:alert(1)">x</a>'
                .'<img src="https://res.cloudinary.com/x/image/upload/a.jpg" onerror="alert(1)" alt="A">',
        ]);

        $body = Post::sole()->body;

        $this->assertStringNotContainsString('script', $body);
        $this->assertStringNotContainsString('onclick', $body);
        $this->assertStringNotContainsString('onerror', $body);
        $this->assertStringNotContainsString('javascript:', $body);
        $this->assertStringContainsString('<p>Hello</p>', $body);
        $this->assertStringContainsString('src="https://res.cloudinary.com/x/image/upload/a.jpg"', $body);
    }

    public function test_long_posts_are_not_truncated_by_the_sanitiser(): void
    {
        $body = str_repeat('<p>'.str_repeat('word ', 200).'</p>', 50);

        $this->actingAs($this->admin, 'admin')->postJson('/api/v1/admin/posts', ['title' => 'Long read', 'status' => 'draft', 'body' => $body]);

        $this->assertSame(strlen($body), strlen(Post::sole()->body));
        $this->assertSame(50, Post::sole()->readingTime());
    }

    public function test_duplicate_titles_get_unique_slugs(): void
    {
        $this->actingAs($this->admin, 'admin')->postJson('/api/v1/admin/posts', ['title' => 'Quarterly Review', 'status' => 'draft']);
        $this->actingAs($this->admin, 'admin')->postJson('/api/v1/admin/posts', ['title' => 'Quarterly Review', 'status' => 'draft']);

        $this->assertEqualsCanonicalizing(['quarterly-review', 'quarterly-review-2'], Post::pluck('slug')->all());
    }

    public function test_a_custom_slug_is_normalised_and_must_be_unique(): void
    {
        Post::factory()->create(['slug' => 'taken']);

        $this->actingAs($this->admin, 'admin')
            ->postJson('/api/v1/admin/posts', ['title' => 'A', 'slug' => 'Taken', 'status' => 'draft'])
            ->assertJsonValidationErrors(['slug' => 'Another post already uses this URL slug.']);

        $this->actingAs($this->admin, 'admin')
            ->postJson('/api/v1/admin/posts', ['title' => 'B', 'slug' => 'My Custom URL', 'status' => 'draft'])
            ->assertCreated();

        $this->assertDatabaseHas('posts', ['title' => 'B', 'slug' => 'my-custom-url']);
    }

    /**
     * The React admin sends edits as multipart POSTs with _method=PUT, since
     * PHP only parses uploaded files on POST requests.
     */
    public function test_replacing_a_cover_deletes_the_old_image_from_cloudinary(): void
    {
        $post = Post::factory()->withCover()->create();
        $oldPublicId = $post->cover_image_public_id;
        $this->fakeCloudinaryUpload('cagad/blog/covers/replacement');

        $this->actingAs($this->admin, 'admin')->postJson("/api/v1/admin/posts/{$post->id}", [
            '_method' => 'PUT',
            'title' => $post->title,
            'status' => 'draft',
            'cover_image' => UploadedFile::fake()->image('new.jpg'),
        ])->assertOk()->assertJsonPath('message', 'Post saved.');

        $this->assertSame('cagad/blog/covers/replacement', $post->fresh()->cover_image_public_id);
        Http::assertSent(fn (Request $request): bool => str_ends_with($request->url(), '/image/destroy')
            && $request['public_id'] === $oldPublicId);
    }

    public function test_removing_a_cover_clears_it_and_deletes_it_from_cloudinary(): void
    {
        $post = Post::factory()->withCover()->create();
        $oldPublicId = $post->cover_image_public_id;
        Http::fake(['api.cloudinary.com/*/image/destroy' => Http::response(['result' => 'ok'])]);

        $this->actingAs($this->admin, 'admin')->putJson("/api/v1/admin/posts/{$post->id}", [
            'title' => $post->title,
            'status' => 'draft',
            'remove_cover' => '1',
        ])->assertOk()->assertJsonPath('data.cover_image', null);

        $this->assertNull($post->fresh()->cover_image_url);
        Http::assertSent(fn (Request $request): bool => $request['public_id'] === $oldPublicId);
    }

    public function test_saving_without_touching_the_cover_keeps_it(): void
    {
        $post = Post::factory()->withCover()->create();

        $this->actingAs($this->admin, 'admin')->putJson("/api/v1/admin/posts/{$post->id}", [
            'title' => 'Renamed',
            'status' => 'draft',
        ])->assertOk();

        $this->assertSame($post->cover_image_url, $post->fresh()->cover_image_url);
        Http::assertNothingSent();
    }

    public function test_a_failed_upload_is_reported_against_the_cover_and_nothing_is_saved(): void
    {
        Http::fake(['api.cloudinary.com/*' => Http::response(['error' => ['message' => 'Invalid image file']], 400)]);

        $this->actingAs($this->admin, 'admin')
            ->postJson('/api/v1/admin/posts', [
                'title' => 'Broken upload',
                'status' => 'draft',
                'cover_image' => UploadedFile::fake()->image('cover.jpg'),
            ])
            ->assertJsonValidationErrors(['cover_image' => 'Upload failed: Invalid image file']);

        $this->assertDatabaseCount('posts', 0);
    }

    public function test_non_images_are_rejected_as_covers(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->postJson('/api/v1/admin/posts', [
                'title' => 'PDF cover',
                'status' => 'draft',
                'cover_image' => UploadedFile::fake()->create('report.pdf', 100, 'application/pdf'),
            ])
            ->assertJsonValidationErrors('cover_image');

        Http::assertNothingSent();
    }

    public function test_deleting_a_post_removes_its_cover_from_cloudinary(): void
    {
        $post = Post::factory()->withCover()->create();
        Http::fake(['api.cloudinary.com/*/image/destroy' => Http::response(['result' => 'ok'])]);

        $this->actingAs($this->admin, 'admin')
            ->deleteJson("/api/v1/admin/posts/{$post->id}")
            ->assertOk()
            ->assertJsonPath('message', "“{$post->title}” was deleted.");

        $this->assertModelMissing($post);
        Http::assertSent(fn (Request $request): bool => $request['public_id'] === $post->cover_image_public_id);
    }

    private function fakeCloudinaryUpload(string $publicId): void
    {
        Http::fake([
            'api.cloudinary.com/*/image/upload' => Http::response([
                'public_id' => $publicId,
                'secure_url' => "https://res.cloudinary.com/test-cloud/image/upload/v1/{$publicId}.jpg",
                'width' => 1600,
                'height' => 900,
            ]),
            'api.cloudinary.com/*/image/destroy' => Http::response(['result' => 'ok']),
        ]);
    }
}
