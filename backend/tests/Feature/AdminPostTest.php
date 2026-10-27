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

    public function test_guests_cannot_reach_the_admin(): void
    {
        $this->get('/admin/posts')->assertRedirect('/admin/login');
        $this->post('/admin/posts', ['title' => 'Nope'])->assertRedirect('/admin/login');
    }

    public function test_the_posts_screens_render(): void
    {
        $post = Post::factory()->published()->withCover()->create();

        $this->actingAs($this->admin, 'admin')->get('/admin/posts')->assertOk()->assertSee($post->title);
        $this->actingAs($this->admin, 'admin')->get('/admin/posts/create')->assertOk()->assertSee('value="Ada Editor"', false);
        $this->actingAs($this->admin, 'admin')->get("/admin/posts/{$post->id}/edit")->assertOk()->assertSee($post->title);
        $this->actingAs($this->admin, 'admin')->get("/admin/posts/{$post->id}")->assertOk()->assertSee($post->title);
    }

    public function test_the_list_can_be_filtered_to_scheduled_posts(): void
    {
        $scheduled = Post::factory()->scheduled()->create();
        $live = Post::factory()->published()->create();

        $this->actingAs($this->admin, 'admin')
            ->get('/admin/posts?status=scheduled')
            ->assertOk()
            ->assertSee($scheduled->title)
            ->assertDontSee($live->title);
    }

    public function test_the_list_can_be_filtered_by_category_and_counts_each_status(): void
    {
        $security = Category::factory()->create(['name' => 'Security']);
        $inCategory = Post::factory()->published()->for($security)->create();
        $elsewhere = Post::factory()->create();

        $this->actingAs($this->admin, 'admin')
            ->get("/admin/posts?category={$security->id}")
            ->assertOk()
            ->assertSee($inCategory->title)
            ->assertDontSee($elsewhere->title)
            ->assertViewHas('statusCounts', ['' => 2, 'published' => 1, 'scheduled' => 0, 'draft' => 1]);
    }

    public function test_the_edit_screen_shows_who_created_the_post(): void
    {
        $post = Post::factory()->for($this->admin)->create();

        $this->actingAs($this->admin, 'admin')
            ->get("/admin/posts/{$post->id}/edit")
            ->assertOk()
            ->assertSeeInOrder(['Created by', 'Ada Editor']);
    }

    public function test_an_editor_can_publish_a_post_with_a_cover_image(): void
    {
        $this->fakeCloudinaryUpload('cagad/blog/covers/new-cover');
        $category = Category::factory()->create();

        $response = $this->actingAs($this->admin, 'admin')->post('/admin/posts', [
            'title' => 'Budget Transparency in Nigerian States',
            'category_id' => $category->id,
            'author_name' => 'Dr. Example',
            'body' => '<p>Findings from our review.</p>',
            'cover_image' => UploadedFile::fake()->image('cover.jpg', 1600, 900),
            'cover_image_alt' => 'A state assembly in session',
            'status' => 'published',
        ]);

        $post = Post::sole();
        $response->assertRedirect("/admin/posts/{$post->id}/edit")->assertSessionHasNoErrors();

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
            ->post('/admin/posts', ['title' => 'Idea', 'status' => 'draft'])
            ->assertSessionHasNoErrors();

        $this->actingAs($this->admin, 'admin')
            ->post('/admin/posts', ['title' => 'Too soon', 'status' => 'published'])
            ->assertSessionHasErrors('body');

        $this->assertSame(['Idea'], Post::pluck('title')->all());
    }

    public function test_a_future_publish_date_schedules_the_post(): void
    {
        $this->actingAs($this->admin, 'admin')->post('/admin/posts', [
            'title' => 'Coming soon',
            'body' => '<p>Later.</p>',
            'status' => 'published',
            'published_at' => now()->addWeek()->format('Y-m-d\TH:i'),
        ]);

        $post = Post::sole();
        $this->assertTrue($post->isScheduled());
        $this->assertFalse($post->isLive());
    }

    public function test_post_html_is_sanitised_before_it_is_stored(): void
    {
        $this->actingAs($this->admin, 'admin')->post('/admin/posts', [
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

        $this->actingAs($this->admin, 'admin')->post('/admin/posts', ['title' => 'Long read', 'status' => 'draft', 'body' => $body]);

        $this->assertSame(strlen($body), strlen(Post::sole()->body));
        $this->assertSame(50, Post::sole()->readingTime());
    }

    public function test_duplicate_titles_get_unique_slugs(): void
    {
        $this->actingAs($this->admin, 'admin')->post('/admin/posts', ['title' => 'Quarterly Review', 'status' => 'draft']);
        $this->actingAs($this->admin, 'admin')->post('/admin/posts', ['title' => 'Quarterly Review', 'status' => 'draft']);

        $this->assertEqualsCanonicalizing(['quarterly-review', 'quarterly-review-2'], Post::pluck('slug')->all());
    }

    public function test_a_custom_slug_is_normalised_and_must_be_unique(): void
    {
        Post::factory()->create(['slug' => 'taken']);

        $this->actingAs($this->admin, 'admin')
            ->post('/admin/posts', ['title' => 'A', 'slug' => 'Taken', 'status' => 'draft'])
            ->assertSessionHasErrors('slug');

        $this->actingAs($this->admin, 'admin')
            ->post('/admin/posts', ['title' => 'B', 'slug' => 'My Custom URL', 'status' => 'draft'])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('posts', ['title' => 'B', 'slug' => 'my-custom-url']);
    }

    public function test_replacing_a_cover_deletes_the_old_image_from_cloudinary(): void
    {
        $post = Post::factory()->withCover()->create();
        $oldPublicId = $post->cover_image_public_id;
        $this->fakeCloudinaryUpload('cagad/blog/covers/replacement');

        $this->actingAs($this->admin, 'admin')->put("/admin/posts/{$post->id}", [
            'title' => $post->title,
            'status' => 'draft',
            'cover_image' => UploadedFile::fake()->image('new.jpg'),
        ])->assertSessionHasNoErrors();

        $this->assertSame('cagad/blog/covers/replacement', $post->fresh()->cover_image_public_id);
        Http::assertSent(fn (Request $request): bool => str_ends_with($request->url(), '/image/destroy')
            && $request['public_id'] === $oldPublicId);
    }

    public function test_removing_a_cover_clears_it_and_deletes_it_from_cloudinary(): void
    {
        $post = Post::factory()->withCover()->create();
        $oldPublicId = $post->cover_image_public_id;
        Http::fake(['api.cloudinary.com/*/image/destroy' => Http::response(['result' => 'ok'])]);

        $this->actingAs($this->admin, 'admin')->put("/admin/posts/{$post->id}", [
            'title' => $post->title,
            'status' => 'draft',
            'remove_cover' => '1',
        ])->assertSessionHasNoErrors();

        $this->assertNull($post->fresh()->cover_image_url);
        Http::assertSent(fn (Request $request): bool => $request['public_id'] === $oldPublicId);
    }

    public function test_saving_without_touching_the_cover_keeps_it(): void
    {
        $post = Post::factory()->withCover()->create();

        $this->actingAs($this->admin, 'admin')->put("/admin/posts/{$post->id}", [
            'title' => 'Renamed',
            'status' => 'draft',
        ])->assertSessionHasNoErrors();

        $this->assertSame($post->cover_image_url, $post->fresh()->cover_image_url);
        Http::assertNothingSent();
    }

    public function test_a_failed_upload_keeps_the_editor_on_the_form(): void
    {
        Http::fake(['api.cloudinary.com/*' => Http::response(['error' => ['message' => 'Invalid image file']], 400)]);

        $this->actingAs($this->admin, 'admin')
            ->from('/admin/posts/create')
            ->post('/admin/posts', [
                'title' => 'Broken upload',
                'status' => 'draft',
                'cover_image' => UploadedFile::fake()->image('cover.jpg'),
            ])
            ->assertRedirect('/admin/posts/create')
            ->assertSessionHasErrors(['cover_image' => 'Upload failed: Invalid image file']);

        $this->assertDatabaseCount('posts', 0);
    }

    public function test_non_images_are_rejected_as_covers(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->post('/admin/posts', [
                'title' => 'PDF cover',
                'status' => 'draft',
                'cover_image' => UploadedFile::fake()->create('report.pdf', 100, 'application/pdf'),
            ])
            ->assertSessionHasErrors('cover_image');

        Http::assertNothingSent();
    }

    public function test_deleting_a_post_removes_its_cover_from_cloudinary(): void
    {
        $post = Post::factory()->withCover()->create();
        Http::fake(['api.cloudinary.com/*/image/destroy' => Http::response(['result' => 'ok'])]);

        $this->actingAs($this->admin, 'admin')
            ->delete("/admin/posts/{$post->id}")
            ->assertRedirect('/admin/posts');

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
