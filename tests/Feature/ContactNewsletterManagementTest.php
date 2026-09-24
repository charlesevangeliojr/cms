<?php

namespace Tests\Feature;

use App\Models\ContactMessage;
use App\Models\NewsletterSubscriber;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactNewsletterManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_sidebar_shows_user_management_contact_us_and_newsletter(): void
    {
        $admin = $this->superAdmin();

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertOk()
            ->assertSee('User Management')
            ->assertDontSee('Users & access')
            ->assertSee('Contact Us')
            ->assertSee('Newsletter')
            ->assertSeeInOrder(['Dashboard', 'Contact Us', 'Newsletter', 'Banners', 'User Management'])
            ->assertSee(route('contacts.index'), false)
            ->assertSee(route('newsletters.index'), false);
    }

    public function test_admin_can_list_filter_and_manage_contact_messages(): void
    {
        $admin = $this->superAdmin();
        ContactMessage::create([
            'name' => 'Maria Santos',
            'email' => 'maria@example.com',
            'subject' => 'Pricing inquiry',
            'message' => 'Please send pricing details.',
            'is_read' => false,
        ]);
        ContactMessage::create([
            'name' => 'Jose Cruz',
            'email' => 'jose@example.com',
            'subject' => 'Support request',
            'message' => 'Need help with setup.',
            'is_read' => true,
        ]);

        $this->actingAs($admin)->get(route('contacts.index'))
            ->assertOk()
            ->assertSee('Pricing inquiry')
            ->assertSee('Support request');

        $this->actingAs($admin)->get(route('contacts.index', ['status' => 'unread']))
            ->assertOk()
            ->assertSee('Pricing inquiry')
            ->assertDontSee('Support request');

        $message = ContactMessage::where('email', 'maria@example.com')->sole();

        $this->actingAs($admin)->patch(route('contacts.update', $message), ['is_read' => '1'])
            ->assertRedirect(route('contacts.index'))
            ->assertSessionHas('success', 'Message status updated successfully.');

        $this->assertTrue($message->refresh()->is_read);

        $this->actingAs($admin)->delete(route('contacts.destroy', $message))
            ->assertRedirect(route('contacts.index'))
            ->assertSessionHas('success', 'Message deleted successfully.');

        $this->assertDatabaseMissing('contact_messages', ['id' => $message->id]);
    }

    public function test_admin_can_list_filter_and_manage_newsletter_subscribers(): void
    {
        $admin = $this->superAdmin();
        NewsletterSubscriber::create([
            'email' => 'active@example.com',
            'name' => 'Active User',
            'is_active' => true,
        ]);
        NewsletterSubscriber::create([
            'email' => 'inactive@example.com',
            'name' => 'Inactive User',
            'is_active' => false,
        ]);

        $this->actingAs($admin)->get(route('newsletters.index'))
            ->assertOk()
            ->assertSee('active@example.com')
            ->assertSee('inactive@example.com');

        $this->actingAs($admin)->get(route('newsletters.index', ['status' => 'active']))
            ->assertOk()
            ->assertSee('active@example.com')
            ->assertDontSee('inactive@example.com');

        $subscriber = NewsletterSubscriber::where('email', 'active@example.com')->sole();

        $this->actingAs($admin)->patch(route('newsletters.update', $subscriber), ['is_active' => '0'])
            ->assertRedirect(route('newsletters.index'))
            ->assertSessionHas('success', 'Subscriber status updated successfully.');

        $this->assertFalse($subscriber->refresh()->is_active);

        $this->actingAs($admin)->delete(route('newsletters.destroy', $subscriber))
            ->assertRedirect(route('newsletters.index'))
            ->assertSessionHas('success', 'Subscriber deleted successfully.');

        $this->assertDatabaseMissing('newsletter_subscribers', ['id' => $subscriber->id]);
    }

    public function test_contact_and_newsletter_routes_require_permissions(): void
    {
        $user = User::factory()->create([
            'role' => 'Content Manager',
            'is_active' => true,
            'permissions' => [
                'dashboard' => ['view' => true, 'add' => false, 'edit' => false, 'delete' => false],
                'banners' => ['view' => false, 'add' => false, 'edit' => false, 'delete' => false],
                'users' => ['view' => false, 'add' => false, 'edit' => false, 'delete' => false],
                'contacts' => ['view' => false, 'add' => false, 'edit' => false, 'delete' => false],
                'newsletters' => ['view' => false, 'add' => false, 'edit' => false, 'delete' => false],
            ],
        ]);

        $message = ContactMessage::create([
            'name' => 'Denied User',
            'email' => 'denied@example.com',
            'subject' => 'Denied subject',
            'message' => 'Denied body.',
        ]);
        $subscriber = NewsletterSubscriber::create(['email' => 'denied-subscriber@example.com']);

        $this->actingAs($user)->get(route('contacts.index'))
            ->assertForbidden()
            ->assertSee('You are not allowed to view contact messages.');
        $this->actingAs($user)->delete(route('contacts.destroy', $message))
            ->assertForbidden()
            ->assertSee('You are not allowed to delete contact messages.');

        $this->actingAs($user)->get(route('newsletters.index'))
            ->assertForbidden()
            ->assertSee('You are not allowed to view newsletter subscribers.');
        $this->actingAs($user)->delete(route('newsletters.destroy', $subscriber))
            ->assertForbidden()
            ->assertSee('You are not allowed to delete newsletter subscribers.');
    }

    private function superAdmin(): User
    {
        return User::factory()->create([
            'role' => 'Super Admin',
            'is_active' => true,
            'permissions' => User::fullAccessPermissions(),
        ]);
    }
}
