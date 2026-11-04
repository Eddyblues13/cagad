<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\AdminToken;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_old_backend_admin_links_go_to_the_react_admin(): void
    {
        config(['app.frontend_url' => 'https://cagad.org']);
        $this->refreshApplication();

        $this->get('/')->assertRedirect('https://cagad.org/admin');
        $this->get('/admin/login')->assertRedirect('https://cagad.org/admin');
        $this->get('/admin/posts/1/edit')->assertRedirect('https://cagad.org/admin');
    }

    public function test_an_admin_can_sign_in_with_a_token_and_sign_out(): void
    {
        $this->freezeSecond();
        $admin = Admin::factory()->create(['password' => 'correct-horse']);

        $response = $this->postJson('/api/v1/admin/login', ['email' => $admin->email, 'password' => 'correct-horse'])
            ->assertOk()
            ->assertJsonPath('admin.email', $admin->email)
            ->assertJsonPath('expires_at', now()->addDay()->toIso8601String());

        $token = $response->json('token');
        $this->assertSame(64, strlen($token));
        $this->assertDatabaseHas('admin_tokens', ['admin_id' => $admin->id, 'token' => hash('sha256', $token)]);
        $this->assertDatabaseMissing('admin_tokens', ['token' => $token]);
        $this->assertTrue($admin->fresh()->last_login_at->equalTo(now()));

        $this->withToken($token)->getJson('/api/v1/admin/account')->assertOk()->assertJsonPath('data.id', $admin->id);

        $this->withToken($token)->postJson('/api/v1/admin/logout')->assertNoContent();
        $this->assertDatabaseCount('admin_tokens', 0);

        $this->app['auth']->forgetGuards();
        $this->withToken($token)->getJson('/api/v1/admin/account')->assertUnauthorized();
    }

    public function test_keep_me_signed_in_makes_the_token_last_30_days(): void
    {
        $this->freezeSecond();
        $admin = Admin::factory()->create(['password' => 'correct-horse']);

        $this->postJson('/api/v1/admin/login', ['email' => $admin->email, 'password' => 'correct-horse', 'remember' => true])
            ->assertJsonPath('expires_at', now()->addDays(30)->toIso8601String());
    }

    public function test_expired_and_unknown_tokens_are_rejected(): void
    {
        $token = AdminToken::issue(Admin::factory()->create(), remember: false)['token'];

        $this->getJson('/api/v1/admin/account')->assertUnauthorized();
        $this->withToken('not-a-real-token')->getJson('/api/v1/admin/account')->assertUnauthorized();

        $this->travel(25)->hours();
        $this->withToken($token)->getJson('/api/v1/admin/account')->assertUnauthorized();
    }

    public function test_signing_out_only_ends_that_browsers_session(): void
    {
        $admin = Admin::factory()->create();
        $laptop = AdminToken::issue($admin, remember: true)['token'];
        $phone = AdminToken::issue($admin, remember: true)['token'];

        $this->withToken($laptop)->postJson('/api/v1/admin/logout')->assertNoContent();

        $this->app['auth']->forgetGuards();
        $this->withToken($phone)->getJson('/api/v1/admin/account')->assertOk();
    }

    public function test_the_email_is_not_case_sensitive(): void
    {
        $admin = Admin::factory()->create(['email' => 'comms@cagad.org', 'password' => 'correct-horse']);

        $this->postJson('/api/v1/admin/login', ['email' => 'Comms@CAGAD.org', 'password' => 'correct-horse'])
            ->assertOk()
            ->assertJsonPath('admin.id', $admin->id);
    }

    public function test_a_wrong_password_is_rejected(): void
    {
        $admin = Admin::factory()->create();

        $this->postJson('/api/v1/admin/login', ['email' => $admin->email, 'password' => 'wrong'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');
        $this->assertDatabaseCount('admin_tokens', 0);
    }

    public function test_accounts_in_the_users_table_cannot_sign_in(): void
    {
        $user = User::factory()->create(['password' => 'correct-horse']);

        $this->postJson('/api/v1/admin/login', ['email' => $user->email, 'password' => 'correct-horse'])
            ->assertJsonValidationErrors('email');
        $this->assertDatabaseCount('admin_tokens', 0);
    }

    public function test_a_deactivated_admin_cannot_sign_in(): void
    {
        $admin = Admin::factory()->inactive()->create(['password' => 'correct-horse']);

        $this->postJson('/api/v1/admin/login', ['email' => $admin->email, 'password' => 'correct-horse'])
            ->assertJsonValidationErrors(['email' => 'This account has been deactivated. Ask a super admin to restore access.']);
        $this->assertDatabaseCount('admin_tokens', 0);
    }

    public function test_an_admin_deactivated_after_signing_in_is_signed_out(): void
    {
        $admin = Admin::factory()->create();
        $token = AdminToken::issue($admin, remember: true)['token'];

        $admin->update(['is_active' => false]);

        $this->withToken($token)->getJson('/api/v1/admin/posts')
            ->assertUnauthorized()
            ->assertJson(['message' => 'Your account has been deactivated, so you were signed out.']);
        $this->assertDatabaseCount('admin_tokens', 0);
    }

    public function test_repeated_failures_lock_the_account_out(): void
    {
        $this->freezeTime();
        $admin = Admin::factory()->create(['password' => 'correct-horse']);

        foreach (range(1, 5) as $attempt) {
            $this->postJson('/api/v1/admin/login', ['email' => $admin->email, 'password' => 'wrong']);
        }

        $this->postJson('/api/v1/admin/login', ['email' => $admin->email, 'password' => 'correct-horse'])
            ->assertJsonValidationErrors(['email' => __('auth.throttle', ['seconds' => 60, 'minutes' => 1])]);
        $this->assertDatabaseCount('admin_tokens', 0);
    }

    public function test_the_account_endpoint_returns_the_signed_in_admin(): void
    {
        $admin = Admin::factory()->create(['name' => 'Ada Obi']);

        $this->actingAs($admin, 'admin')
            ->getJson('/api/v1/admin/account')
            ->assertOk()
            ->assertJsonPath('data.name', 'Ada Obi')
            ->assertJsonPath('data.initials', 'AO')
            ->assertJsonPath('data.role', 'editor')
            ->assertJsonPath('data.posts_count', 0)
            ->assertJsonMissingPath('data.password');
    }

    public function test_an_admin_can_change_their_password(): void
    {
        $admin = Admin::factory()->create(['password' => 'old-password']);

        $this->actingAs($admin, 'admin')->putJson('/api/v1/admin/account', [
            'name' => 'New Name',
            'email' => $admin->email,
            'current_password' => 'old-password',
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ])->assertOk()->assertJsonPath('message', 'Your account was updated.');

        $admin->refresh();
        $this->assertSame('New Name', $admin->name);
        $this->assertTrue(Hash::check('new-password-123', $admin->password));
    }

    public function test_changing_the_password_needs_the_current_one(): void
    {
        $admin = Admin::factory()->create(['password' => 'old-password']);

        $this->actingAs($admin, 'admin')->putJson('/api/v1/admin/account', [
            'name' => $admin->name,
            'email' => $admin->email,
            'current_password' => 'not-it',
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ])->assertJsonValidationErrors('current_password');

        $this->assertTrue(Hash::check('old-password', $admin->fresh()->password));
    }

    public function test_an_admin_cannot_take_another_admins_email(): void
    {
        Admin::factory()->create(['email' => 'taken@cagad.org']);
        $admin = Admin::factory()->create();

        $this->actingAs($admin, 'admin')->putJson('/api/v1/admin/account', [
            'name' => $admin->name,
            'email' => 'Taken@CAGAD.org',
        ])->assertJsonValidationErrors('email');
    }

    public function test_the_account_form_cannot_change_the_role(): void
    {
        $admin = Admin::factory()->create();

        $this->actingAs($admin, 'admin')->putJson('/api/v1/admin/account', [
            'name' => $admin->name,
            'email' => $admin->email,
            'role' => 'super_admin',
            'is_active' => '1',
        ])->assertOk();

        $this->assertFalse($admin->fresh()->isSuperAdmin());
    }
}
