<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlogApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_lists_only_live_posts_newest_first(): void
    {
        $older = Post::factory()->published(now()->subDays(3))->create();
        $newer = Post::factory()->published(now()->subDay())->create();
        Post::factory()->create();
        Post::factory()->scheduled()->create();

        $response = $this->getJson('/api/v1/posts');

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.slug', $newer->slug)
            ->assertJsonPath('data.1.slug', $older->slug)
            ->assertJsonMissingPath('data.0.body')
            ->assertJsonStructure([
                'data' => [['id', 'title', 'slug', 'excerpt', 'author', 'category', 'cover_image', 'reading_time', 'published_at']],
                'links',
                'meta' => ['current_page', 'last_page', 'per_page', 'total'],
            ]);
    }

    public function test_it_filters_posts_by_category_slug(): void
    {
        $security = Category::factory()->create(['name' => 'Security']);
        $inCategory = Post::factory()->published()->for($security)->create();
        Post::factory()->published()->create();

        $this->getJson('/api/v1/posts?category=security')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.slug', $inCategory->slug)
            ->assertJsonPath('data.0.category.slug', 'security');
    }

    public function test_it_searches_titles_and_excerpts(): void
    {
        $byTitle = Post::factory()->published()->create(['title' => 'Election monitoring in Bayelsa']);
        $byExcerpt = Post::factory()->published()->create(['excerpt' => 'Notes from our election observers.']);
        Post::factory()->published()->create(['title' => 'Unrelated', 'excerpt' => 'Nothing to see']);

        $slugs = $this->getJson('/api/v1/posts?search=election')
            ->assertOk()
            ->json('data.*.slug');

        $this->assertEqualsCanonicalizing([$byTitle->slug, $byExcerpt->slug], $slugs);
    }

    public function test_page_size_is_capped(): void
    {
        $this->getJson('/api/v1/posts?per_page=500')
            ->assertOk()
            ->assertJsonPath('meta.per_page', 24);
    }

    public function test_it_shows_a_live_post_with_its_body_and_related_posts(): void
    {
        $category = Category::factory()->create();
        $post = Post::factory()->published()->for($category)->create(['body' => '<h2>Findings</h2><p>Text</p>']);
        $sameCategory = Post::factory()->published(now()->subDays(10))->for($category)->create();
        $newerElsewhere = Post::factory()->published(now()->subDay())->create();
        Post::factory()->for($category)->create();

        $this->getJson("/api/v1/posts/{$post->slug}")
            ->assertOk()
            ->assertJsonPath('data.slug', $post->slug)
            ->assertJsonPath('data.body', '<h2>Findings</h2><p>Text</p>')
            ->assertJsonPath('data.url', "https://cagad.org/blog/{$post->slug}")
            ->assertJsonCount(2, 'related')
            ->assertJsonPath('related.0.slug', $sameCategory->slug)
            ->assertJsonPath('related.1.slug', $newerElsewhere->slug);
    }

    public function test_drafts_and_scheduled_posts_are_not_public(): void
    {
        $draft = Post::factory()->create();
        $scheduled = Post::factory()->scheduled()->create();

        $this->getJson("/api/v1/posts/{$draft->slug}")->assertNotFound();
        $this->getJson("/api/v1/posts/{$scheduled->slug}")->assertNotFound();
    }

    public function test_cover_images_come_with_cloudinary_renditions(): void
    {
        $post = Post::factory()->published()->withCover()->create();

        $cover = $this->getJson('/api/v1/posts')->assertOk()->json('data.0.cover_image');

        $this->assertSame($post->cover_image_url, $cover['original']);
        $this->assertSame($post->cover_image_alt, $cover['alt']);
        $this->assertStringContainsString('/image/upload/c_fill,g_auto,w_800,h_500,f_auto,q_auto/v1700000000/', $cover['card']);
        $this->assertStringContainsString('/image/upload/c_limit,w_1600,f_auto,q_auto/', $cover['hero']);
    }

    public function test_categories_lists_only_those_with_live_posts(): void
    {
        $active = Category::factory()->create(['name' => 'Governance']);
        Post::factory()->count(2)->published()->for($active)->create();
        Post::factory()->for($active)->create();

        $draftsOnly = Category::factory()->create();
        Post::factory()->for($draftsOnly)->create();

        $this->getJson('/api/v1/categories')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.slug', 'governance')
            ->assertJsonPath('data.0.posts_count', 2);
    }

    public function test_the_api_allows_cross_origin_requests(): void
    {
        $this->getJson('/api/v1/posts', ['Origin' => 'https://cagad.org'])
            ->assertOk()
            ->assertHeader('Access-Control-Allow-Origin', '*');
    }
}
