<?php

namespace Tests\Feature;

use App\Models\Banner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_features_active_banners_before_the_introduction_and_forms(): void
    {
        Banner::create([
            'title' => 'Latest company news',
            'description' => 'Read our latest announcement.',
            'image_path' => 'company.jpg',
            'is_active' => true,
        ]);
        Banner::create([
            'title' => 'Hidden campaign',
            'description' => 'This must not be published.',
            'image_path' => 'hidden.jpg',
            'is_active' => false,
        ]);

        $this->get(route('home'))->assertOk()
            ->assertSeeInOrder(['Latest company news', 'home-heading', 'contact-heading', 'newsletter-heading'])
            ->assertSee('/uploads/banners/company.jpg', false)
            ->assertDontSee('Hidden campaign')
            ->assertDontSee('POST /contact')
            ->assertSee(route('contact.store'), false)
            ->assertSee(route('newsletter.store'), false);
    }

    public function test_home_has_a_welcome_hero_when_no_banners_are_published(): void
    {
        $this->get(route('home'))->assertOk()
            ->assertSee('Discover more.')
            ->assertSee('Stay connected.')
            ->assertSee('images/login-background.png', false)
            ->assertDontSee('data-hero-controls', false);
    }

    public function test_home_offers_slide_controls_only_for_multiple_active_banners(): void
    {
        foreach (['First announcement', 'Second announcement'] as $title) {
            Banner::create([
                'title' => $title,
                'description' => 'Company announcement.',
                'image_path' => 'announcement.jpg',
                'is_active' => true,
            ]);
        }

        $this->get(route('home'))->assertOk()
            ->assertSee('Previous banner')
            ->assertSee('Next banner')
            ->assertSee('Show banner 1: Second announcement')
            ->assertSee('Show banner 2: First announcement');
    }

    public function test_public_forms_save_submissions_and_show_visitor_facing_confirmation(): void
    {
        $this->from(route('home'))->post(route('contact.store'), [
            'name' => 'Maria Santos',
            'email' => 'maria@example.com',
            'subject' => 'Services inquiry',
            'message' => 'Please tell me more about your services.',
        ])->assertRedirect(route('home'))
            ->assertSessionHas('success', 'Thank you! Your message has been sent.');

        $this->assertDatabaseHas('contact_messages', ['email' => 'maria@example.com', 'subject' => 'Services inquiry']);

        $this->from(route('home'))->post(route('newsletter.store'), [
            'email' => 'maria@example.com',
        ])->assertRedirect(route('home'))
            ->assertSessionHas('success', 'Thank you for subscribing to our newsletter.');

        $this->assertDatabaseHas('newsletter_subscribers', ['email' => 'maria@example.com', 'is_active' => true]);
    }
}
