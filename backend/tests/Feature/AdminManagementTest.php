<?php

namespace Tests\Feature;

use App\Enums\AdminRole;
use App\Models\Admin;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminManagementTest extends TestCase
{
    use RefreshDatabase;

    private Admin $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdmin = Admin::factory()->superAdmin()->create();
    }

    public function test_editors_cannot_manage_admins(): void
    {
        $editor = Admin::factory()->create();

        $this->actingAs($editor, 'admin')->get('/admin/admins')->assertForbidden();
        $this->actingAs($editor, 'admin')->get('/admin/admins/create')->assertForbidden();
        $this->actingAs($editor, 'admin')
            ->post('/admin/admins', $this->validFields(['email' => 'sneaky@cagad.org']))
            ->assertForbidden();
        $this->actingAs($editor, 'admin')->delete("/admin/admins/{$this->superAdmin->id}")->assertForbidden();

        $this->assertDatabaseMissing('admins', ['email' => 'sneaky@cagad.org']);
        $this->assertModelExists($this->superAdmin);
    }

    public function test_super_admins_can_list_admins(): void
    {
        $editor = Admin::factory()->create(['name' => 'Ed Itor']);

        $this->actingAs($this->superAdmin, 'admin')
            ->get('/admin/admins')
            ->assertOk()
            ->assertSee('Ed Itor')
            ->assertSee($editor->email)
            ->assertSee($this->superAdmin->email);

        $this->actingAs($this->superAdmin, 'admin')->get('/admin/admins/create')->assertOk();
        $this->actingAs($this->superAdmin, 'admin')->get("/admin/admins/{$editor->id}/edit")->assertOk()->assertSee('Ed Itor');
    }

    public function test_a_super_admin_can_add_an_editor(): void
    {
        $this->actingAs($this->superAdmin, 'admin')
            ->post('/admin/admins', $this->validFields(['email' => 'Comms@CAGAD.org']))
            ->assertRedirect('/admin/admins')
            ->assertSessionHasNoErrors();

        $admin = Admin::firstWhere('email', 'comms@cagad.org');
        $this->assertNotNull($admin);
        $this->assertSame('Comms Officer', $admin->name);
        $this->assertSame(AdminRole::Editor, $admin->role);
        $this->assertTrue($admin->is_active);
        $this->assertTrue(Hash::check('a-strong-pass', $admin->password));
    }

    public function test_new_admins_need_a_unique_email_and_a_confirmed_password(): void
    {
        $this->actingAs($this->superAdmin, 'admin')
            ->post('/admin/admins', $this->validFields([
                'email' => strtoupper($this->superAdmin->email),
                'password_confirmation' => 'something-else',
            ]))
            ->assertSessionHasErrors(['email', 'password']);

        $this->actingAs($this->superAdmin, 'admin')
            ->post('/admin/admins', $this->validFields(['password' => '', 'password_confirmation' => '', 'role' => 'owner']))
            ->assertSessionHasErrors(['password', 'role']);

        $this->assertDatabaseCount('admins', 1);
    }

    public function test_a_super_admin_can_promote_and_deactivate_another_admin(): void
    {
        $editor = Admin::factory()->create(['password' => 'original-pass']);

        $this->actingAs($this->superAdmin, 'admin')
            ->put("/admin/admins/{$editor->id}", [
                'name' => 'Renamed',
                'email' => $editor->email,
                'role' => 'super_admin',
                'is_active' => '0',
            ])
            ->assertRedirect('/admin/admins')
            ->assertSessionHasNoErrors();

        $editor->refresh();
        $this->assertSame('Renamed', $editor->name);
        $this->assertTrue($editor->isSuperAdmin());
        $this->assertFalse($editor->is_active);
        $this->assertTrue(Hash::check('original-pass', $editor->password));
    }

    public function test_a_super_admin_can_reset_another_admins_password(): void
    {
        $editor = Admin::factory()->create();

        $this->actingAs($this->superAdmin, 'admin')
            ->put("/admin/admins/{$editor->id}", [
                'name' => $editor->name,
                'email' => $editor->email,
                'role' => 'editor',
                'is_active' => '1',
                'password' => 'brand-new-pass',
                'password_confirmation' => 'brand-new-pass',
            ])
            ->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check('brand-new-pass', $editor->fresh()->password));
    }

    public function test_super_admins_cannot_demote_deactivate_or_delete_themselves(): void
    {
        $this->actingAs($this->superAdmin, 'admin')
            ->get("/admin/admins/{$this->superAdmin->id}/edit")
            ->assertRedirect('/admin/account');

        $this->actingAs($this->superAdmin, 'admin')
            ->put("/admin/admins/{$this->superAdmin->id}", [
                'name' => $this->superAdmin->name,
                'email' => $this->superAdmin->email,
                'role' => 'editor',
                'is_active' => '0',
            ])
            ->assertForbidden();

        $this->actingAs($this->superAdmin, 'admin')
            ->delete("/admin/admins/{$this->superAdmin->id}")
            ->assertForbidden();

        $this->superAdmin->refresh();
        $this->assertTrue($this->superAdmin->isSuperAdmin());
        $this->assertTrue($this->superAdmin->is_active);
    }

    public function test_removing_an_admin_keeps_their_posts(): void
    {
        $editor = Admin::factory()->create();
        $post = Post::factory()->for($editor)->create();

        $this->actingAs($this->superAdmin, 'admin')
            ->delete("/admin/admins/{$editor->id}")
            ->assertRedirect('/admin/admins');

        $this->assertModelMissing($editor);
        $this->assertModelExists($post);
        $this->assertNull($post->fresh()->admin_id);
    }

    /**
     * @param  array<string, string>  $overrides
     * @return array<string, string>
     */
    private function validFields(array $overrides = []): array
    {
        return [
            'name' => 'Comms Officer',
            'email' => 'comms@cagad.org',
            'role' => 'editor',
            'is_active' => '1',
            'password' => 'a-strong-pass',
            'password_confirmation' => 'a-strong-pass',
            ...$overrides,
        ];
    }
}
