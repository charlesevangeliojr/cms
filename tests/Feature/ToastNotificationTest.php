<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ToastNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_transient_notifications_use_global_auto_dismissing_toasts(): void
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
        $this->assertSame(3, substr_count($response->getContent(), '<div data-toast'));
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
