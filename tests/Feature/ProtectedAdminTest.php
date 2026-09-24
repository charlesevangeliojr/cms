<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProtectedAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_protected_admin_cannot_be_deleted_but_can_still_be_edited(): void
    {
        $deletingAdmin = User::factory()->create([
            'role' => 'Super Admin',
            'is_active' => true,
        ]);
        $protectedAdmin = User::factory()->create([
            'name' => 'CMS Admin',
            'email' => 'cms@cms.com',
            'role' => 'Super Admin',
            'is_active' => true,
            'is_protected' => true,
            'permissions' => User::fullAccessPermissions(),
        ]);

        $response = $this->actingAs($deletingAdmin)
            ->delete(route('users.destroy', $protectedAdmin));

        $response->assertRedirect(route('users.index'))
            ->assertSessionHas('error', 'This account is protected and cannot be deleted.');

        $this->assertDatabaseHas('users', [
            'id' => $protectedAdmin->id,
            'is_protected' => true,
        ]);

        $this->actingAs($deletingAdmin)
            ->get(route('users.edit', $protectedAdmin))
            ->assertOk()
            ->assertSee('CMS Admin');
    }

    public function test_protected_admin_permissions_can_be_edited(): void
    {
        $admin = User::factory()->create([
            'name' => 'CMS Admin',
            'email' => 'cms@cms.com',
            'role' => 'Super Admin',
            'is_active' => true,
            'is_protected' => true,
            'permissions' => User::fullAccessPermissions(),
        ]);

        $response = $this->actingAs($admin)->put(route('users.update', $admin), [
            'name' => 'CMS Admin',
            'email' => 'cms@cms.com',
            'role' => 'Super Admin',
            'is_active' => '1',
            'permissions' => [
                'dashboard' => ['view' => true, 'add' => true, 'edit' => true, 'delete' => true],
                'banners' => ['view' => true, 'add' => true, 'edit' => true, 'delete' => true],
                'users' => ['view' => true, 'add' => true, 'edit' => true],
            ],
        ]);

        $response->assertRedirect(route('users.index'))
            ->assertSessionHas('success', 'User updated successfully.');

        $admin->refresh();

        $this->assertTrue($admin->canAccess('banners', 'delete'));
        $this->assertTrue($admin->canAccess('users', 'edit'));
        $this->assertFalse($admin->canAccess('users', 'delete'));
    }

    public function test_role_dropdown_uses_active_database_roles(): void
    {
        $admin = User::factory()->create([
            'role' => 'Super Admin',
            'is_active' => true,
        ]);
        Role::create([
            'name' => 'Release Manager',
            'permissions' => [
                'dashboard' => ['view' => true],
            ],
            'is_active' => true,
        ]);
        Role::create([
            'name' => 'Archived Role',
            'permissions' => [],
            'is_active' => false,
        ]);

        $response = $this->actingAs($admin)->get(route('users.create'));

        $response->assertOk()
            ->assertSee('Release Manager')
            ->assertDontSee('Archived Role');
        $this->assertContains('Release Manager', $response->viewData('roles')->all());
        $this->assertNotContains('Archived Role', $response->viewData('roles')->all());
    }

    public function test_super_admin_role_defaults_check_every_module_permission(): void
    {
        $admin = User::factory()->create([
            'role' => 'Super Admin',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('users.create'));

        $response->assertOk();
        $this->assertSame(
            User::fullAccessPermissions(),
            $response->viewData('roleDefaults')['Super Admin'],
        );
    }
}
