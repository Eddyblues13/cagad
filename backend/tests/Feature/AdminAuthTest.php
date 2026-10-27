<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_login_page_renders(): void
    {
        $this->get('/admin/login')->assertOk()->assertSee('Blog Admin');
    }

    public function test_the_backend_root_points_at_the_admin(): void
    {
        $this->get('/')->assertRedirect('/admin');
    }

    public function test_guests_are_sent_to_the_login_page(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
    }

    public function test_an_admin_can_sign_in_and_out(): void
    {
        $this->freezeSecond();
        $admin = Admin::factory()->create(['password' => 'correct-horse']);

        $this->post('/admin/login', ['email' => $admin->email, 'password' => 'correct-horse'])
            ->assertRedirect('/admin');
        $this->assertAuthenticatedAs($admin, 'admin');
        $this->assertTrue($admin->fresh()->last_login_at->equalTo(now()));

        $this->post('/admin/logout')
            ->assertRedirect('/admin/login')
            ->assertSessionHas('status', 'You have been signed out.');
        $this->assertGuest('admin');
    }

    public function test_the_email_is_not_case_sensitive(): void
    {
        $admin = Admin::factory()->create(['email' => 'comms@cagad.org', 'password' => 'correct-horse']);

        $this->post('/admin/login', ['email' => 'Comms@CAGAD.org', 'password' => 'correct-horse'])
            ->assertRedirect('/admin');
        $this->assertAuthenticatedAs($admin, 'admin');
    }

    public function test_admins_return_to_the_page_they_asked_for_after_signing_in(): void
    {
        $admin = Admin::factory()->create(['password' => 'correct-horse']);

        $this->get('/admin/categories')->assertRedirect('/admin/login');

        $this->post('/admin/login', ['email' => $admin->email, 'password' => 'correct-horse'])
            ->assertRedirect('/admin/categories');
    }

    public function test_a_wrong_password_is_rejected(): void
    {
        $admin = Admin::factory()->create();

        $this->post('/admin/login', ['email' => $admin->email, 'password' => 'wrong'])
            ->assertSessionHasErrors('email');
        $this->assertGuest('admin');
    }

    public function test_accounts_in_the_users_table_cannot_sign_in(): void
    {
        $user = User::factory()->create(['password' => 'correct-horse']);

        $this->post('/admin/login', ['email' => $user->email, 'password' => 'correct-horse'])
            ->assertSessionHasErrors('email');
        $this->assertGuest('admin');
        $this->assertGuest('web');
    }

    public function test_users_signed_in_through_the_web_guard_cannot_reach_the_admin(): void
    {
        $this->actingAs(User::factory()->create(), 'web')
            ->get('/admin')
            ->assertRedirect('/admin/login');
    }

    public function test_a_deactivated_admin_cannot_sign_in(): void
    {
        $admin = Admin::factory()->inactive()->create(['password' => 'correct-horse']);

        $this->post('/admin/login', ['email' => $admin->email, 'password' => 'correct-horse'])
            ->assertSessionHasErrors(['email' => 'This account has been deactivated. Ask a super admin to restore access.']);
        $this->assertGuest('admin');
    }

    public function test_an_admin_deactivated_mid_session_is_signed_out(): void
    {
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin')->get('/admin')->assertOk();

        $admin->update(['is_active' => false]);

        $this->get('/admin/posts')
            ->assertRedirect('/admin/login')
            ->assertSessionHasErrors('email');
        $this->assertGuest('admin');
    }

    public function test_a_deactivated_admin_gets_a_json_error_from_the_editor_upload(): void
    {
        $this->actingAs(Admin::factory()->inactive()->create(), 'admin')
            ->postJson('/admin/uploads/images', ['file' => UploadedFile::fake()->image('x.jpg')])
            ->assertForbidden();
    }

    public function test_repeated_failures_lock_the_account_out(): void
    {
        $this->freezeTime();
        $admin = Admin::factory()->create(['password' => 'correct-horse']);

        foreach (range(1, 5) as $attempt) {
            $this->post('/admin/login', ['email' => $admin->email, 'password' => 'wrong']);
        }

        $this->post('/admin/login', ['email' => $admin->email, 'password' => 'correct-horse'])
            ->assertSessionHasErrors(['email' => __('auth.throttle', ['seconds' => 60, 'minutes' => 1])]);
        $this->assertGuest('admin');
    }

    public function test_signed_in_admins_skip_the_login_page(): void
    {
        $this->actingAs(Admin::factory()->create(), 'admin')
            ->get('/admin/login')
            ->assertRedirect('/admin');
    }

    public function test_the_account_page_renders(): void
    {
        $admin = Admin::factory()->create(['name' => 'Ada Obi']);

        $this->actingAs($admin, 'admin')
            ->get('/admin/account')
            ->assertOk()
            ->assertSee('Ada Obi')
            ->assertSee($admin->email);
    }

    public function test_an_admin_can_change_their_password(): void
    {
        $admin = Admin::factory()->create(['password' => 'old-password']);

        $this->actingAs($admin, 'admin')->put('/admin/account', [
            'name' => 'New Name',
            'email' => $admin->email,
            'current_password' => 'old-password',
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ])->assertSessionHasNoErrors();

        $admin->refresh();
        $this->assertSame('New Name', $admin->name);
        $this->assertTrue(Hash::check('new-password-123', $admin->password));
    }

    public function test_changing_the_password_needs_the_current_one(): void
    {
        $admin = Admin::factory()->create(['password' => 'old-password']);

        $this->actingAs($admin, 'admin')->put('/admin/account', [
            'name' => $admin->name,
            'email' => $admin->email,
            'current_password' => 'not-it',
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ])->assertSessionHasErrors('current_password');

        $this->assertTrue(Hash::check('old-password', $admin->fresh()->password));
    }

    public function test_an_admin_cannot_take_another_admins_email(): void
    {
        Admin::factory()->create(['email' => 'taken@cagad.org']);
        $admin = Admin::factory()->create();

        $this->actingAs($admin, 'admin')->put('/admin/account', [
            'name' => $admin->name,
            'email' => 'Taken@CAGAD.org',
        ])->assertSessionHasErrors('email');
    }

    public function test_the_account_form_cannot_change_the_role(): void
    {
        $admin = Admin::factory()->create();

        $this->actingAs($admin, 'admin')->put('/admin/account', [
            'name' => $admin->name,
            'email' => $admin->email,
            'role' => 'super_admin',
            'is_active' => '1',
        ])->assertSessionHasNoErrors();

        $this->assertFalse($admin->fresh()->isSuperAdmin());
    }
}
