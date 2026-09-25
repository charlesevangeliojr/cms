<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityDefenseTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_responses_have_browser_defenses_and_are_not_cacheable(): void
    {
        $response = $this->get(route('login'))->assertOk()
            ->assertHeader('X-Frame-Options', 'DENY')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow')
            ->assertHeader('Content-Security-Policy', "base-uri 'self'; object-src 'none'; frame-ancestors 'none'; form-action 'self'");
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
    }

    public function test_repeated_login_attempts_are_throttled(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->postJson(route('login.attempt'), ['email' => 'missing@example.com', 'password' => 'wrong'])->assertStatus(302);
        }
        $this->postJson(route('login.attempt'), ['email' => 'missing@example.com', 'password' => 'wrong'])->assertStatus(429);
    }

    public function test_public_form_rate_limit_is_shared_across_endpoints(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->postJson(route('contact.store'), [])->assertUnprocessable();
        }
        $this->postJson(route('newsletter.store'), [])->assertStatus(429);
    }

    public function test_delegated_manager_cannot_escalate_or_take_over_admin(): void
    {
        Role::create(['name' => 'Manager', 'is_active' => true, 'permissions' => []]);
        $manager = User::factory()->create(['role' => 'Manager', 'is_active' => true, 'permissions' => ['users' => ['view' => true, 'add' => true, 'edit' => true, 'delete' => true]]]);
        $admin = User::factory()->create(['role' => 'Super Admin', 'is_active' => true]);
        $this->actingAs($manager);
        $this->get(route('users.edit', $admin))->assertForbidden();
        $this->delete(route('users.destroy', $admin))->assertForbidden();
        $data = ['name' => 'Changed', 'email' => $admin->email, 'role' => 'Manager'];
        $this->put(route('users.update', $admin), $data)->assertForbidden();
        $this->assertNotSame('Changed', $admin->fresh()->name);
        $data = ['name' => 'New User', 'email' => 'new@example.com', 'password' => 'password123', 'password_confirmation' => 'password123', 'role' => 'Super Admin'];
        $this->post(route('users.store'), $data)->assertForbidden();
        $data['role'] = 'Manager';
        $data['permissions'] = ['banners' => ['delete' => '1']];
        $this->post(route('users.store'), $data)->assertForbidden();
        $this->assertDatabaseMissing('users', ['email' => 'new@example.com']);
        $data['permissions'] = ['users' => ['view' => '1']];
        $this->post(route('users.store'), $data)->assertSessionHasNoErrors()->assertRedirect(route('users.index'));
    }
}
