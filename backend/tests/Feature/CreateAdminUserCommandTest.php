<?php

namespace Tests\Feature;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CreateAdminUserCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_super_admin(): void
    {
        $this->artisan('admin:create', ['--name' => 'Comms Team', '--email' => 'Comms@cagad.org', '--password' => 'a-strong-pass'])
            ->expectsOutputToContain('Created super admin comms@cagad.org')
            ->assertSuccessful();

        $admin = Admin::sole();
        $this->assertSame('Comms Team', $admin->name);
        $this->assertSame('comms@cagad.org', $admin->email);
        $this->assertTrue($admin->isSuperAdmin());
        $this->assertTrue($admin->is_active);
        $this->assertTrue(Hash::check('a-strong-pass', $admin->password));
    }

    public function test_it_resets_the_password_of_an_existing_admin(): void
    {
        $admin = Admin::factory()->create(['email' => 'comms@cagad.org', 'name' => 'Comms Team']);

        $this->artisan('admin:create', ['--email' => 'comms@cagad.org', '--password' => 'brand-new-pass'])
            ->expectsOutputToContain('Updated admin comms@cagad.org')
            ->assertSuccessful();

        $admin->refresh();
        $this->assertSame('Comms Team', $admin->name);
        $this->assertFalse($admin->isSuperAdmin());
        $this->assertTrue(Hash::check('brand-new-pass', $admin->password));
    }

    public function test_it_warns_when_the_account_is_deactivated(): void
    {
        Admin::factory()->inactive()->create(['email' => 'comms@cagad.org']);

        $this->artisan('admin:create', ['--email' => 'comms@cagad.org', '--password' => 'brand-new-pass'])
            ->expectsOutputToContain('This account is deactivated')
            ->assertSuccessful();
    }

    public function test_it_rejects_a_short_password(): void
    {
        $this->artisan('admin:create', ['--name' => 'X', '--email' => 'x@cagad.org', '--password' => 'short'])
            ->assertFailed();

        $this->assertDatabaseCount('admins', 0);
    }
}
