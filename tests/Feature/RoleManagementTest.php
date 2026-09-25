<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_super_admin_is_active_initially(): void
    {
        $admin = $this->superAdmin();

        $response = $this->actingAs($admin)->get(route('users.create'));

        $response->assertOk();
        $this->assertSame(['Super Admin'], $response->viewData('roles')->values()->all());
    }

    public function test_super_admin_can_create_a_role_that_appears_in_the_dropdown(): void
    {
        $admin = $this->superAdmin();

        $response = $this->actingAs($admin)->post(route('roles.store'), [
            'name' => 'Release Manager',
            'is_active' => '1',
            'permissions' => [
                'dashboard' => ['view' => '1'],
            ],
        ]);

        $response->assertRedirect(route('users.create'))
            ->assertSessionHas('success', 'Role Release Manager created successfully.');

        $role = Role::where('name', 'Release Manager')->sole();
        $this->assertTrue($role->is_active);
        $this->assertTrue($role->permissions['dashboard']['view']);
        $this->assertFalse($role->permissions['banners']['view']);

        $this->actingAs($admin)
            ->get(route('users.create'))
            ->assertOk()
            ->assertSee('Release Manager');
    }

    public function test_non_super_admin_cannot_create_roles(): void
    {
        $admin = User::factory()->create([
            'role' => 'Content Manager',
            'is_active' => true,
        ]);

        $this->actingAs($admin)->get(route('roles.create'))
            ->assertForbidden()
            ->assertSee('You are not allowed to create roles.');
        $this->actingAs($admin)->post(route('roles.store'), [
            'name' => 'Unauthorized Role',
            'is_active' => '1',
        ])->assertForbidden();
    }

    public function test_inline_creation_returns_the_role_without_redirecting(): void
    {
        $this->actingAs($this->superAdmin())->postJson(route('roles.store'), [
            'name' => 'Inline Manager',
            'is_active' => true,
            'permissions' => ['dashboard' => ['view' => '1']],
        ])->assertCreated()
            ->assertJsonPath('role.name', 'Inline Manager')
            ->assertJsonPath('role.is_active', true)
            ->assertJsonPath('role.permissions.dashboard.view', true)
            ->assertJsonPath('role.permissions.users.delete', false);
        $this->assertDatabaseHas('roles', ['name' => 'Inline Manager']);
    }

    public function test_inline_creation_returns_validation_errors_and_enforces_access(): void
    {
        $this->actingAs($this->superAdmin())->postJson(route('roles.store'), [
            'name' => 'Super Admin', 'is_active' => true,
        ])->assertUnprocessable()->assertJsonValidationErrors('name');

        $user = User::factory()->create(['role' => 'Content Manager', 'is_active' => true]);
        $this->actingAs($user)->postJson(route('roles.store'), [
            'name' => 'Unauthorized Inline Role', 'is_active' => true,
        ])->assertForbidden();
        $this->assertDatabaseMissing('roles', ['name' => 'Unauthorized Inline Role']);
    }

    public function test_super_admin_can_delete_an_unused_role(): void
    {
        $role = Role::create(['name' => 'Unused Editor', 'permissions' => [], 'is_active' => true]);
        $this->actingAs($this->superAdmin())->deleteJson(route('roles.destroy', $role->name))->assertOk();
        $this->assertDatabaseMissing('roles', ['id' => $role->id]);
    }

    public function test_assigned_roles_and_super_admin_cannot_be_deleted(): void
    {
        $admin = $this->superAdmin();
        $role = Role::create(['name' => 'Assigned Editor', 'permissions' => [], 'is_active' => true]);
        $user = User::factory()->create(['role' => $role->name, 'is_active' => false]);
        $this->actingAs($admin)->deleteJson(route('roles.destroy', $role->name))->assertUnprocessable();
        $this->deleteJson(route('roles.destroy', 'Super Admin'))->assertUnprocessable();
        $this->assertDatabaseHas('roles', ['id' => $role->id]);
        $this->assertSame($role->name, $user->fresh()->role);
    }

    public function test_other_users_cannot_delete_roles(): void
    {
        $role = Role::create(['name' => 'Unused Editor', 'permissions' => [], 'is_active' => true]);
        $user = User::factory()->create(['role' => $role->name, 'is_active' => true, 'permissions' => User::fullAccessPermissions()]);
        $this->actingAs($user)->deleteJson(route('roles.destroy', $role->name))->assertForbidden();
        $this->assertDatabaseHas('roles', ['id' => $role->id]);
    }

    public function test_create_and_edit_forms_offer_role_dialogs(): void
    {
        $admin = $this->superAdmin();
        foreach ([route('users.create'), route('users.edit', $admin)] as $url) {
            $this->actingAs($admin)->get($url)->assertOk()
                ->assertSee('<dialog id="role-dialog"', false)
                ->assertSee('Delete selected role')
                ->assertSee('Create and select role');
        }
    }

    private function superAdmin(): User
    {
        return User::factory()->create([
            'role' => 'Super Admin',
            'is_active' => true,
        ]);
    }
}
