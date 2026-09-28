<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SessionTimeoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_idle_session_is_signed_out(): void
    {
        $user = $this->admin();

        $response = $this->actingAs($user)
            ->withSession(['last_seen' => now()->subMinutes(31)->getTimestamp()])
            ->get(route('dashboard'));

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_active_session_stays_signed_in_and_refreshes(): void
    {
        $user = $this->admin();

        $response = $this->actingAs($user)
            ->withSession(['last_seen' => now()->subMinutes(5)->getTimestamp()])
            ->get(route('dashboard'));

        $response->assertOk();
        $this->assertAuthenticated();
        $this->assertGreaterThan(
            now()->subMinute()->getTimestamp(),
            session('last_seen'),
        );
    }

    public function test_first_request_after_login_starts_tracking(): void
    {
        $user = $this->admin();

        $this->actingAs($user)->get(route('dashboard'))->assertOk();
        $this->assertNotNull(session('last_seen'));
    }

    private function admin(): User
    {
        return User::factory()->create([
            'role' => 'Super Admin',
            'is_active' => true,
            'permissions' => User::fullAccessPermissions(),
        ]);
    }
}
