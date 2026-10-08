<?php

namespace Tests\Feature\PublicSite;

use App\Models\HomeBanner;
use App\Models\BannerPage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LandingPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_features_active_banners_before_the_introduction_and_forms(): void
    {
        $banner = HomeBanner::create(['title' => 'Latest company news', 'description' => 'Read our latest announcement.', 'is_active' => true]);
        $banner->images()->create(['image_path' => 'uploads/home-banners/company.jpg', 'is_active' => true]);
        $banner->images()->create(['image_path' => 'uploads/home-banners/hidden.jpg', 'is_active' => false]);

        $this->get(route('home'))->assertOk()
            ->assertSeeInOrder(['Latest company news', 'home-heading'])
            ->assertSee('/uploads/home-banners/company.jpg', false)
            ->assertDontSee('Hidden campaign')
            ->assertDontSee('POST /contact')
            ->assertDontSee('contact-heading')
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

    public function test_about_page_has_a_home_to_about_us_breadcrumb(): void
    {
        $this->get(route('about'))->assertOk()
            ->assertSee('aria-label="Breadcrumb"', false)
            ->assertSee(route('home'), false)
            ->assertSeeInOrder(['Home', 'About Us']);
    }

    public function test_contact_is_a_public_page_with_its_own_banner_area_and_contact_form(): void
    {
        $this->get(route('contact'))->assertOk()
            ->assertSee('Contact Us')
            ->assertSee('site-hero-pages', false)
            ->assertSee('images/page-banner-default.svg', false)
            ->assertSee('id="contact-form"', false)
            ->assertSee('width="1600" height="300"', false);
    }

    public function test_about_page_content_is_read_from_the_database(): void
    {
        $this->assertFalse(Schema::hasTable('public_pages'));
        BannerPage::where('slug', 'about')->update([
            'title' => 'Our Organization',
            'content' => 'This About page copy was loaded from the database.',
        ]);

        $this->get(route('about'))->assertOk()
            ->assertSee('Our Organization')
            ->assertSee('This About page copy was loaded from the database.')
            ->assertDontSee('Turbo Drive intercepted this navigation');
    }

    public function test_home_offers_slide_controls_only_for_multiple_active_banners(): void
    {
        $banner = HomeBanner::create(['title' => 'Shared announcement', 'description' => 'Company announcement.', 'is_active' => true]);
        $banner->images()->createMany([
            ['image_path' => 'uploads/home-banners/announcement-one.jpg', 'is_active' => true, 'position' => 0],
            ['image_path' => 'uploads/home-banners/announcement-two.jpg', 'is_active' => true, 'position' => 1],
        ]);

        $response = $this->get(route('home'))->assertOk()
            ->assertSee('Previous banner')
            ->assertSee('Next banner')
            ->assertSee('Show banner 1: Shared announcement')
            ->assertSee('Show banner 2: Shared announcement');
        $this->assertSame(1, substr_count($response->getContent(), '<h1 class="site-hero-title">Shared announcement</h1>'));
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
