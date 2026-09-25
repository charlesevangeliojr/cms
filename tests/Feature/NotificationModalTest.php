<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

class NotificationModalTest extends TestCase
{
    use RefreshDatabase;

    public function test_transient_notifications_use_shared_modals(): void
    {
        $admin = User::factory()->create([
            'role' => 'Super Admin',
            'is_active' => true,
            'permissions' => User::fullAccessPermissions(),
        ]);

        $response = $this->actingAs($admin)->withSession([
            'success' => 'Banner created successfully.',
            'error' => 'Something went wrong.',
            'info' => 'Heads up.',
        ])->get(route('users.index'));

        $response->assertOk()
            ->assertSee('Banner created successfully.')
            ->assertSee('Something went wrong.')
            ->assertSee('Heads up.');
        $response->assertSee('id="notification-modal"', false)->assertSee('data-notifications', false)->assertDontSee('data-toast', false);
    }

    public function test_delete_forms_use_modal_confirmation_instead_of_browser_popups(): void
    {
        $admin = User::factory()->create(['role' => 'Super Admin', 'is_active' => true, 'permissions' => User::fullAccessPermissions()]);
        User::factory()->create(['role' => 'Super Admin']);
        $this->actingAs($admin)->get(route('users.index'))->assertOk()
            ->assertSee('data-confirm=', false)
            ->assertSee('id="notification-cancel"', false)
            ->assertDontSee('return confirm(', false);
    }

    public function test_login_validation_errors_are_available_in_the_modal(): void
    {
        $this->withSession(['errors' => (new ViewErrorBag)->put('default', new MessageBag(['email' => 'Check your email.']))])
            ->get(route('login'))->assertOk()->assertSee('id="notification-modal"', false)->assertSee('Check your email.');
    }

    public function test_full_page_permission_error_does_not_use_a_toast(): void
    {
        $user = User::factory()->create([
            'role' => 'Content Manager',
            'is_active' => true,
            'permissions' => [
                'dashboard' => ['view' => false, 'add' => false, 'edit' => false, 'delete' => false],
                'banners' => ['view' => false, 'add' => false, 'edit' => false, 'delete' => false],
                'users' => ['view' => false, 'add' => false, 'edit' => false, 'delete' => false],
            ],
        ]);

        $this->actingAs($user)->get(route('dashboard'))
            ->assertForbidden()
            ->assertSee('You are not allowed to view the dashboard.')
            ->assertDontSee('data-toast', false);
    }
}
