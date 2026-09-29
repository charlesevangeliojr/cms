<?php

namespace Tests\Feature;

use App\Http\Controllers\AuthController;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LoginSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_failed_login_starts_a_10_second_lockout(): void
    {
        $user = User::factory()->create(['is_active' => true]);

        $response = $this->post(route('login.attempt'), [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('email');
        $response->assertSessionHas('lockout_seconds', 10);
        $this->assertGuest();
    }

    public function test_lockout_doubles_with_each_consecutive_failure(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $payload = ['email' => $user->email, 'password' => 'wrong-password'];

        $this->post(route('login.attempt'), $payload)->assertSessionHas('lockout_seconds', 10);

        $this->travel(11)->seconds();
        $this->post(route('login.attempt'), $payload)->assertSessionHas('lockout_seconds', 20);

        $this->travel(21)->seconds();
        $this->post(route('login.attempt'), $payload)->assertSessionHas('lockout_seconds', 40);
    }

    public function test_attempts_during_lockout_are_blocked(): void
    {
        $user = User::factory()->create(['is_active' => true]);

        $this->post(route('login.attempt'), [
            'email' => $user->email,
            'password' => 'wrong-password',
        ])->assertSessionHas('lockout_seconds', 10);

        $this->post(route('login.attempt'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_successful_login_clears_the_lockout(): void
    {
        $user = User::factory()->create([
            'role_id' => Role::where('name', 'Super Admin')->firstOrFail()->id,
            'is_active' => true,
            'permissions' => User::fullAccessPermissions(),
        ]);

        $this->post(route('login.attempt'), [
            'email' => $user->email,
            'password' => 'wrong-password',
        ])->assertSessionHas('lockout_seconds', 10);

        $this->travel(11)->seconds();
        $this->post(route('login.attempt'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertSessionMissing('lockout_seconds');
        $this->assertAuthenticated();

        $this->post(route('login.attempt'), [
            'email' => $user->email,
            'password' => 'wrong-password',
        ])->assertSessionHas('lockout_seconds', 10);
    }

    public function test_session_limit_keeps_only_the_newest_sessions(): void
    {
        config(['session.driver' => 'database']);
        $user = User::factory()->create(['is_active' => true]);

        foreach (range(1, 5) as $i) {
            DB::table('sessions')->insert([
                'id' => "session-{$i}",
                'user_id' => $user->id,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'test',
                'payload' => 'payload',
                'last_activity' => time() - (5 - $i) * 60,
            ]);
        }

        $method = new \ReflectionMethod(AuthController::class, 'enforceSessionLimit');
        $method->invoke(new AuthController, $user->id);

        $this->assertSame(
            ['session-5', 'session-4'],
            DB::table('sessions')->where('user_id', $user->id)->orderByDesc('last_activity')->pluck('id')->all(),
        );
    }
}
