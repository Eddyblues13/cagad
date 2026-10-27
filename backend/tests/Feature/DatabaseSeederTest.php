<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Category;
use App\Models\Post;
use Database\Seeders\AdminSeeder;
use Database\Seeders\PostSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeding_creates_the_configured_super_admin_and_the_categories(): void
    {
        config(['admin.seed' => ['name' => 'Site Owner', 'email' => 'Owner@cagad.org', 'password' => 'from-the-env-file']]);

        $this->seed();

        $admin = Admin::sole();
        $this->assertSame('Site Owner', $admin->name);
        $this->assertSame('owner@cagad.org', $admin->email);
        $this->assertTrue($admin->isSuperAdmin());
        $this->assertTrue($admin->is_active);
        $this->assertTrue(Hash::check('from-the-env-file', $admin->password));
        $this->assertSame(7, Category::count());
    }

    public function test_seeding_again_leaves_an_existing_admin_alone(): void
    {
        config(['admin.seed.email' => 'owner@cagad.org']);
        $existing = Admin::factory()->create(['email' => 'owner@cagad.org', 'password' => 'changed-since']);

        $this->seed();
        $this->seed();

        $this->assertSame(1, Admin::count());
        $this->assertTrue(Hash::check('changed-since', $existing->fresh()->password));
        $this->assertSame(7, Category::count());
    }

    public function test_locally_the_default_password_is_password(): void
    {
        config(['admin.seed.password' => null]);
        $this->app->detectEnvironment(fn (): string => 'local');

        $this->seed(AdminSeeder::class);

        $this->assertTrue(Hash::check('password', Admin::sole()->password));
    }

    public function test_elsewhere_a_random_password_is_generated_and_shown_once(): void
    {
        config(['admin.seed.password' => null]);
        $this->app->detectEnvironment(fn (): string => 'production');

        $this->artisan('db:seed', ['--class' => AdminSeeder::class, '--force' => true])
            ->expectsOutputToContain('Change this password')
            ->assertSuccessful();

        $this->assertFalse(Hash::check('password', Admin::sole()->password));
    }

    public function test_sample_posts_are_only_seeded_locally(): void
    {
        $this->seed();
        $this->assertSame(0, Post::count());

        $this->app->detectEnvironment(fn (): string => 'local');
        $this->seed();

        $this->assertTrue(Post::published()->exists());
        $this->assertTrue(Post::scheduled()->exists());
        $this->assertTrue(Post::draft()->exists());
        $this->assertSame(0, Post::whereNull('admin_id')->count());
        $this->assertSame(0, Post::whereNull('category_id')->count());

        $count = Post::count();
        $this->seed(PostSeeder::class);
        $this->assertSame($count, Post::count());
    }
}
