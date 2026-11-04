<?php

namespace Tests\Feature;

use App\Enums\AdminRole;
use App\Models\Admin;
use App\Models\AdminToken;
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

        $this->actingAs($editor, 'admin')->getJson('/api/v1/admin/admins')->assertForbidden();
        $this->actingAs($editor, 'admin')->getJson("/api/v1/admin/admins/{$this->superAdmin->id}")->assertForbidden();
        $this->actingAs($editor, 'admin')
            ->postJson('/api/v1/admin/admins', $this->validFields(['email' => 'sneaky@cagad.org']))
            ->assertForbidden();
        $this->actingAs($editor, 'admin')->deleteJson("/api/v1/admin/admins/{$this->superAdmin->id}")->assertForbidden();

        $this->assertDatabaseMissing('admins', ['email' => 'sneaky@cagad.org']);
        $this->assertModelExists($this->superAdmin);
    }

    public function test_super_admins_can_list_and_view_admins(): void
    {
        $editor = Admin::factory()->create(['name' => 'Ed Itor']);
        Post::factory()->for($editor)->create();

        $this->actingAs($this->superAdmin, 'admin')
            ->getJson('/api/v1/admin/admins')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment(['name' => 'Ed Itor', 'email' => $editor->email, 'posts_count' => 1]);

        $this->actingAs($this->superAdmin, 'admin')
            ->getJson("/api/v1/admin/admins/{$editor->id}")
            ->assertOk()
            ->assertJsonPath('data.name', 'Ed Itor')
            ->assertJsonPath('data.role_label', 'Editor');
    }

    public function test_a_super_admin_can_add_an_editor(): void
    {
        $this->actingAs($this->superAdmin, 'admin')
            ->postJson('/api/v1/admin/admins', $this->validFields(['email' => 'Comms@CAGAD.org']))
            ->assertCreated()
            ->assertJsonPath('message', 'Comms Officer was added as Editor.');

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
            ->postJson('/api/v1/admin/admins', $this->validFields([
                'email' => strtoupper($this->superAdmin->email),
                'password_confirmation' => 'something-else',
            ]))
            ->assertJsonValidationErrors(['email', 'password']);

        $this->actingAs($this->superAdmin, 'admin')
            ->postJson('/api/v1/admin/admins', $this->validFields(['password' => '', 'password_confirmation' => '', 'role' => 'owner']))
            ->assertJsonValidationErrors(['password', 'role']);

        $this->assertDatabaseCount('admins', 1);
    }

    public function test_a_super_admin_can_promote_and_deactivate_another_admin(): void
    {
        $editor = Admin::factory()->create(['password' => 'original-pass']);
        AdminToken::issue($editor, remember: true);

        $this->actingAs($this->superAdmin, 'admin')
            ->putJson("/api/v1/admin/admins/{$editor->id}", [
                'name' => 'Renamed',
                'email' => $editor->email,
                'role' => 'super_admin',
                'is_active' => false,
            ])
            ->assertOk()
            ->assertJsonPath('data.is_active', false);

        $editor->refresh();
        $this->assertSame('Renamed', $editor->name);
        $this->assertTrue($editor->isSuperAdmin());
        $this->assertFalse($editor->is_active);
        $this->assertTrue(Hash::check('original-pass', $editor->password));
        $this->assertSame(0, $editor->tokens()->count());
    }

    public function test_resetting_a_password_signs_the_admin_out_everywhere(): void
    {
        $editor = Admin::factory()->create();
        AdminToken::issue($editor, remember: true);

        $this->actingAs($this->superAdmin, 'admin')
            ->putJson("/api/v1/admin/admins/{$editor->id}", [
                'name' => $editor->name,
                'email' => $editor->email,
                'role' => 'editor',
                'is_active' => true,
                'password' => 'brand-new-pass',
                'password_confirmation' => 'brand-new-pass',
            ])
            ->assertOk();

        $this->assertTrue(Hash::check('brand-new-pass', $editor->fresh()->password));
        $this->assertSame(0, $editor->tokens()->count());
    }

    public function test_super_admins_cannot_demote_deactivate_or_delete_themselves(): void
    {
        $this->actingAs($this->superAdmin, 'admin')
            ->putJson("/api/v1/admin/admins/{$this->superAdmin->id}", [
                'name' => $this->superAdmin->name,
                'email' => $this->superAdmin->email,
                'role' => 'editor',
                'is_active' => false,
            ])
            ->assertForbidden();

        $this->actingAs($this->superAdmin, 'admin')
            ->deleteJson("/api/v1/admin/admins/{$this->superAdmin->id}")
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
            ->deleteJson("/api/v1/admin/admins/{$editor->id}")
            ->assertOk();

        $this->assertModelMissing($editor);
        $this->assertModelExists($post);
        $this->assertNull($post->fresh()->admin_id);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validFields(array $overrides = []): array
    {
        return [
            'name' => 'Comms Officer',
            'email' => 'comms@cagad.org',
            'role' => 'editor',
            'is_active' => true,
            'password' => 'a-strong-pass',
            'password_confirmation' => 'a-strong-pass',
            ...$overrides,
        ];
    }
}
