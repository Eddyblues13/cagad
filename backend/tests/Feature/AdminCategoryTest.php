<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCategoryTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Admin::factory()->create();
    }

    public function test_categories_can_be_listed_created_and_renamed(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->postJson('/api/v1/admin/categories', ['name' => 'Policy Briefs'])
            ->assertCreated()
            ->assertJsonPath('message', 'Category created.');

        $category = Category::sole();
        $this->assertSame('policy-briefs', $category->slug);
        Post::factory()->for($category)->create();

        $this->actingAs($this->admin, 'admin')
            ->getJson('/api/v1/admin/categories')
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Policy Briefs')
            ->assertJsonPath('data.0.posts_count', 1);
        $this->actingAs($this->admin, 'admin')
            ->getJson("/api/v1/admin/categories/{$category->id}")
            ->assertOk()
            ->assertJsonPath('data.slug', 'policy-briefs');

        $this->actingAs($this->admin, 'admin')
            ->putJson("/api/v1/admin/categories/{$category->id}", ['name' => 'Briefs', 'slug' => 'briefs'])
            ->assertOk();

        $this->assertSame('briefs', $category->fresh()->slug);
    }

    public function test_category_names_are_unique(): void
    {
        Category::factory()->create(['name' => 'Security']);

        $this->actingAs($this->admin, 'admin')
            ->postJson('/api/v1/admin/categories', ['name' => 'Security'])
            ->assertJsonValidationErrors('name');
    }

    public function test_deleting_a_category_keeps_its_posts(): void
    {
        $category = Category::factory()->create();
        $post = Post::factory()->for($category)->create();

        $this->actingAs($this->admin, 'admin')
            ->deleteJson("/api/v1/admin/categories/{$category->id}")
            ->assertOk();

        $this->assertModelMissing($category);
        $this->assertNull($post->fresh()->category_id);
    }

    public function test_guests_cannot_manage_categories(): void
    {
        $this->postJson('/api/v1/admin/categories', ['name' => 'Nope'])->assertUnauthorized();

        $this->assertDatabaseCount('categories', 0);
    }
}
