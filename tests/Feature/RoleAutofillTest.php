<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAutofillTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_form_exposes_database_role_defaults_to_the_browser(): void
    {
        $admin = User::factory()->create([
            'role' => 'Super Admin',
            'is_active' => true,
            'permissions' => User::fullAccessPermissions(),
        ]);
        Role::create([
            'name' => 'Partial Access',
            'permissions' => [
                'dashboard' => ['view' => true, 'add' => false, 'edit' => false, 'delete' => false],
            ],
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('users.create'));

        $response->assertOk()
            ->assertSee('data-role-defaults', false)
            ->assertSee('Partial Access', false)
            ->assertSee('initializeRolePermissionForms', false);
        $this->assertSame(
            ['view' => true, 'add' => false, 'edit' => false, 'delete' => false],
            $response->viewData('roleDefaults')['Partial Access']['dashboard'],
        );
    }

    public function test_custom_permissions_are_preserved_after_a_validation_error(): void
    {
        $admin = User::factory()->create([
            'role' => 'Super Admin',
            'is_active' => true,
            'permissions' => User::fullAccessPermissions(),
        ]);

        $this->actingAs($admin)->post(route('users.store'), [
            'name' => '',
            'email' => 'preserved@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'Super Admin',
            'permissions' => [
                'dashboard' => ['view' => '1'],
            ],
        ])->assertSessionHasErrors('name');

        $response = $this->actingAs($admin)->get(route('users.create'));

        $response->assertOk()->assertSee('data-preserve-custom="1"', false);
        $this->assertMatchesRegularExpression(
            '/name="permissions\[dashboard\]\[view\]"[^>]*checked/',
            $response->getContent(),
        );
    }

    public function test_edit_form_uses_stored_permissions_until_the_role_changes(): void
    {
        $admin = User::factory()->create([
            'role' => 'Super Admin',
            'is_active' => true,
            'permissions' => User::fullAccessPermissions(),
        ]);
        $user = User::factory()->create([
            'role' => 'Super Admin',
            'is_active' => true,
            'permissions' => [
                'dashboard' => ['view' => true, 'add' => false, 'edit' => false, 'delete' => false],
                'banners' => ['view' => false, 'add' => false, 'edit' => false, 'delete' => false],
                'users' => ['view' => false, 'add' => false, 'edit' => false, 'delete' => false],
            ],
        ]);

        $response = $this->actingAs($admin)->get(route('users.edit', $user));

        $response->assertOk()->assertSee('data-preserve-custom="1"', false);
        $this->assertMatchesRegularExpression(
            '/name="permissions\[dashboard\]\[view\]"[^>]*checked/',
            $response->getContent(),
        );
        $this->assertDoesNotMatchRegularExpression(
            '/name="permissions\[banners\]\[view\]"[^>]*checked/',
            $response->getContent(),
        );
    }
}
