<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_sent_to_the_login_page(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
    }

    public function test_it_summarises_the_blog(): void
    {
        $this->travelTo(now()->setDate(2026, 10, 14)->setTime(10, 0));
        $admin = Admin::factory()->create(['name' => 'Ada Obi']);
        $governance = Category::factory()->create(['name' => 'Governance']);
        Post::factory()->count(2)->published(now()->subDays(2))->for($governance)->create();
        Post::factory()->published(now()->subMonths(3))->create();
        Post::factory()->scheduled()->create(['title' => 'Coming next week']);
        Post::factory()->create(['title' => 'Half-written idea']);

        $response = $this->actingAs($admin, 'admin')->get('/admin');

        $response->assertOk()
            ->assertSee('Good morning, Ada')
            ->assertSee('Coming next week')
            ->assertSee('Half-written idea')
            ->assertSee('Governance')
            ->assertViewHas('stats', fn (array $stats): bool => $stats['total'] === 5
                && $stats['published'] === 3
                && $stats['scheduled'] === 1
                && $stats['drafts'] === 1
                && $stats['published_this_month'] === 2)
            ->assertViewHas('activity', fn (Collection $activity): bool => $activity->count() === 12
                && $activity->first()['month'] === 'November 2025'
                && $activity->last()['month'] === 'October 2026'
                && $activity->last()['count'] === 2
                && $activity->sum('count') === 3);
    }

    public function test_it_renders_for_an_empty_blog(): void
    {
        $this->actingAs(Admin::factory()->create(), 'admin')
            ->get('/admin')
            ->assertOk()
            ->assertSee('Nothing has been published in the last 12 months')
            ->assertSee('Nothing scheduled')
            ->assertSee('No posts yet');
    }

    public function test_only_super_admins_get_the_admins_shortcuts(): void
    {
        $this->actingAs(Admin::factory()->create(), 'admin')
            ->get('/admin')
            ->assertOk()
            ->assertDontSee(route('admin.admins.index'));

        $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin')
            ->get('/admin')
            ->assertOk()
            ->assertSee(route('admin.admins.index'))
            ->assertSee(route('admin.admins.create'));
    }
}
