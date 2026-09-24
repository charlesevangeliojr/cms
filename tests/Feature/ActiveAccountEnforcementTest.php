<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActiveAccountEnforcementTest extends TestCase
{
    use RefreshDatabase;

    public function test_deactivating_a_signed_in_user_expires_their_next_request(): void
    {
        $admin = User::factory()->create([
            'role' => 'Super Admin',
            'is_active' => true,
            'permissions' => User::fullAccessPermissions(),
        ]);
        $target = User::factory()->create([
            'role' => 'Super Admin',
            'is_active' => true,
            'permissions' => [
                'dashboard' => ['view' => true, 'add' => false, 'edit' => false, 'delete' => false],
                'banners' => ['view' => false, 'add' => false, 'edit' => false, 'delete' => false],
                'users' => ['view' => false, 'add' => false, 'edit' => false, 'delete' => false],
            ],
        ]);

        $this->actingAs($target)->get(route('dashboard'))->assertOk();

        $this->actingAs($admin)->put(route('users.update', $target), [
            'name' => $target->name,
            'email' => $target->email,
            'role' => 'Super Admin',
        ])->assertRedirect(route('users.index'));

        $this->actingAs($target)->get(route('dashboard'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}
