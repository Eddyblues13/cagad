<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_see_the_dashboard(): void
    {
        $this->getJson('/api/v1/admin/dashboard')->assertUnauthorized();
    }

    public function test_it_summarises_the_blog(): void
    {
        $this->travelTo(now()->setDate(2026, 10, 14)->setTime(10, 0));
        $admin = Admin::factory()->create();
        $governance = Category::factory()->create(['name' => 'Governance']);
        Post::factory()->count(2)->published(now()->subDays(2))->for($governance)->create();
        Post::factory()->published(now()->subMonths(3))->create();
        Post::factory()->scheduled()->create(['title' => 'Coming next week']);
        Post::factory()->create(['title' => 'Half-written idea']);

        $response = $this->actingAs($admin, 'admin')->getJson('/api/v1/admin/dashboard');

        $response->assertOk()
            ->assertJsonPath('stats', [
                'total' => 5,
                'published' => 3,
                'scheduled' => 1,
                'drafts' => 1,
                'published_this_month' => 2,
                'categories' => Category::count(),
            ])
            ->assertJsonCount(12, 'activity')
            ->assertJsonPath('activity.0.month', 'November 2025')
            ->assertJsonPath('activity.11', ['label' => 'Oct', 'month' => 'October 2026', 'count' => 2])
            ->assertJsonCount(5, 'recent_posts')
            ->assertJsonPath('upcoming_posts.0.title', 'Coming next week')
            ->assertJsonPath('top_categories.0.name', 'Governance')
            ->assertJsonPath('top_categories.0.posts_count', 2);

        $this->assertSame(3, collect($response->json('activity'))->sum('count'));
    }

    public function test_it_works_for_an_empty_blog(): void
    {
        $this->actingAs(Admin::factory()->create(), 'admin')
            ->getJson('/api/v1/admin/dashboard')
            ->assertOk()
            ->assertJsonPath('stats.total', 0)
            ->assertJsonPath('recent_posts', [])
            ->assertJsonPath('upcoming_posts', [])
            ->assertJsonPath('top_categories', []);
    }
}
