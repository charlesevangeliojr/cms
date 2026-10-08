<?php

namespace Tests\Feature\Account;

use App\Models\BannerPage;
use App\Models\PageBanner;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermissionEnforcementTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_is_hidden_and_forbidden_without_dashboard_view(): void
    {
        $user = User::factory()->create([
            'role_id' => $this->contentManagerRole()->id,
            'is_active' => true,
            'permissions' => [
                'dashboard' => ['view' => false, 'add' => false, 'edit' => false, 'delete' => false],
                'banners' => ['view' => true, 'add' => false, 'edit' => false, 'delete' => false],
                'users' => ['view' => true, 'add' => false, 'edit' => false, 'delete' => false],
            ],
        ]);

        $this->actingAs($user)
            ->get(route('users.index'))
            ->assertOk()
            ->assertDontSee(route('dashboard'), false)
            ->assertSee(route('home-banners.index'), false);

        $this->actingAs($user)->get(route('dashboard'))
            ->assertForbidden()
            ->assertSee('You are not allowed to view the dashboard.');
    }

    public function test_banner_edit_denial_names_the_forbidden_action(): void
    {
        $user = User::factory()->create([
            'role_id' => $this->contentManagerRole()->id,
            'is_active' => true,
            'permissions' => [
                'dashboard' => ['view' => false, 'add' => false, 'edit' => false, 'delete' => false],
                'banners' => ['view' => true, 'add' => false, 'edit' => false, 'delete' => false],
                'users' => ['view' => false, 'add' => false, 'edit' => false, 'delete' => false],
            ],
        ]);
        $banner = PageBanner::create([
            'banner_page_id' => BannerPage::where('slug', 'about')->value('id'),
            'title' => 'Restricted Banner',
            'image_path' => 'uploads/page-banners/restricted.jpg',
            'is_active' => true,
        ]);

        $this->actingAs($user)->get(route('page-banners.edit', $banner))
            ->assertForbidden()
            ->assertSee('You are not allowed to edit banners.');
    }

    public function test_unchecking_every_permission_is_stored_instead_of_using_role_defaults(): void
    {
        $admin = User::factory()->create([
            'role_id' => Role::where('name', 'Super Admin')->firstOrFail()->id,
            'is_active' => true,
            'permissions' => User::fullAccessPermissions(),
        ]);
        $target = User::factory()->create([
            'role_id' => Role::where('name', 'Super Admin')->firstOrFail()->id,
            'is_active' => true,
            'permissions' => User::fullAccessPermissions(),
        ]);

        $response = $this->actingAs($admin)->put(route('users.update', $target), [
            'name' => $target->name,
            'email' => $target->email,
            'role_id' => Role::where('name', 'Super Admin')->firstOrFail()->id,
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('users.edit', $target))
            ->assertSessionHas('success', 'User updated successfully.');

        $target->refresh();
        $denied = [
            'view' => false,
            'add' => false,
            'edit' => false,
            'delete' => false,
        ];

        $this->assertSame(
            array_fill_keys(array_keys(config('cms.pages')), $denied),
            $target->permissions,
        );
        $this->assertFalse($target->canAccess('dashboard', 'view'));
    }

    public function test_login_redirects_to_first_permitted_module(): void
    {
        $user = User::factory()->create([
            'role_id' => $this->contentManagerRole()->id,
            'is_active' => true,
            'permissions' => [
                'dashboard' => ['view' => false, 'add' => false, 'edit' => false, 'delete' => false],
                'banners' => ['view' => true, 'add' => false, 'edit' => false, 'delete' => false],
                'users' => ['view' => false, 'add' => false, 'edit' => false, 'delete' => false],
            ],
        ]);

        $response = $this->post(route('login.attempt'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('home-banners.index'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_rejects_a_user_without_any_module_access(): void
    {
        $user = User::factory()->create([
            'role_id' => $this->contentManagerRole()->id,
            'is_active' => true,
            'permissions' => [
                'dashboard' => ['view' => false, 'add' => false, 'edit' => false, 'delete' => false],
                'banners' => ['view' => false, 'add' => false, 'edit' => false, 'delete' => false],
                'users' => ['view' => false, 'add' => false, 'edit' => false, 'delete' => false],
            ],
        ]);

        $response = $this->post(route('login.attempt'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}
