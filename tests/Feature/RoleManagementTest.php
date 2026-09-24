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

    private function superAdmin(): User
    {
        return User::factory()->create([
            'role' => 'Super Admin',
            'is_active' => true,
        ]);
    }
}
