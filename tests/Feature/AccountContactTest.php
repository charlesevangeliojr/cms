<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountContactTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_number_can_be_created_edited_and_cleared(): void
    {
        $admin = User::factory()->create(['role' => 'Super Admin', 'is_active' => true, 'permissions' => User::fullAccessPermissions()]);
        $this->actingAs($admin);
        $data = ['name' => 'Contact User', 'email' => 'contact-user@example.com', 'password' => 'password123', 'password_confirmation' => 'password123', 'role' => 'Super Admin', 'contact' => '+63 912 345 6789', 'is_active' => '1'];

        $this->get(route('users.create'))->assertOk()->assertSee('Select Existing Role')->assertSee('Create New Role')->assertSee('Contact Number');
        $this->post(route('users.store'), $data)->assertSessionHasNoErrors()->assertRedirect(route('users.index'));
        $user = User::where('email', $data['email'])->sole();
        $this->assertSame($data['contact'], $user->contact);
        $this->get(route('users.edit', $user))->assertOk()->assertSee($data['contact']);

        $data['contact'] = '09123456789';
        $this->put(route('users.update', $user), $data)->assertSessionHasNoErrors();
        $this->assertSame($data['contact'], $user->fresh()->contact);
        $data['contact'] = '';
        $this->put(route('users.update', $user), $data)->assertSessionHasNoErrors();
        $this->assertNull($user->fresh()->contact);
    }

    public function test_invalid_contact_is_retained_and_does_not_overwrite_saved_number(): void
    {
        $admin = User::factory()->create(['role' => 'Super Admin', 'is_active' => true, 'permissions' => User::fullAccessPermissions(), 'contact' => '09123456789']);
        $this->actingAs($admin)->from(route('users.edit', $admin))->put(route('users.update', $admin), [
            'name' => $admin->name, 'email' => $admin->email, 'role' => $admin->role, 'contact' => str_repeat('1', 21),
        ])->assertSessionHasErrors('contact');
        $this->assertSame('09123456789', $admin->fresh()->contact);
        $this->get(route('users.edit', $admin))->assertOk()->assertSee(str_repeat('1', 21))->assertSee('id="contact-error"', false);
    }
}
