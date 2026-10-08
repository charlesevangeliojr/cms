<?php

namespace Tests\Feature\Dashboard;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardSystemStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_shows_system_status_without_resource_usage(): void
    {
        $admin = User::factory()->create([
            'role_id' => Role::where('name', 'Super Admin')->firstOrFail()->id,
            'is_active' => true,
            'permissions' => User::fullAccessPermissions(),
        ]);

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertOk()
            ->assertSee('System Status')
            ->assertDontSee('CPU Usage')
            ->assertDontSee('Storage');
    }
}
