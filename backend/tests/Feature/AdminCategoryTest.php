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
            ->post('/admin/categories', ['name' => 'Policy Briefs'])
            ->assertRedirect('/admin/categories');

        $category = Category::sole();
        $this->assertSame('policy-briefs', $category->slug);

        $this->actingAs($this->admin, 'admin')->get('/admin/categories')->assertOk()->assertSee('Policy Briefs');
        $this->actingAs($this->admin, 'admin')->get('/admin/categories/create')->assertOk();
        $this->actingAs($this->admin, 'admin')->get("/admin/categories/{$category->id}/edit")->assertOk();

        $this->actingAs($this->admin, 'admin')
            ->put("/admin/categories/{$category->id}", ['name' => 'Briefs', 'slug' => 'briefs'])
            ->assertSessionHasNoErrors();

        $this->assertSame('briefs', $category->fresh()->slug);
    }

    public function test_category_names_are_unique(): void
    {
        Category::factory()->create(['name' => 'Security']);

        $this->actingAs($this->admin, 'admin')
            ->post('/admin/categories', ['name' => 'Security'])
            ->assertSessionHasErrors('name');
    }

    public function test_deleting_a_category_keeps_its_posts(): void
    {
        $category = Category::factory()->create();
        $post = Post::factory()->for($category)->create();

        $this->actingAs($this->admin, 'admin')
            ->delete("/admin/categories/{$category->id}")
            ->assertRedirect('/admin/categories');

        $this->assertModelMissing($category);
        $this->assertNull($post->fresh()->category_id);
    }

    public function test_guests_cannot_manage_categories(): void
    {
        $this->post('/admin/categories', ['name' => 'Nope'])->assertRedirect('/admin/login');

        $this->assertDatabaseCount('categories', 0);
    }
}
