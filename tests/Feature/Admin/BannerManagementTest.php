<?php

namespace Tests\Feature\Admin;

use App\Models\BannerPage;
use App\Models\HomeBanner;
use App\Models\HomeBannerImage;
use App\Models\PageBanner;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class BannerManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->app->usePublicPath(storage_path('framework/testing/banner-public'));
        foreach (['home-banners', 'page-banners'] as $directory) {
            $path = public_path('uploads/'.$directory);
            if (! is_dir($path)) mkdir($path, 0755, true);
        }
    }

    protected function tearDown(): void
    {
        foreach (['home-banners', 'page-banners'] as $directory) {
            foreach (glob(public_path('uploads/'.$directory).'/*') ?: [] as $file) {
                if (is_file($file)) @unlink($file);
            }
        }
        parent::tearDown();
    }

    public function test_home_banner_has_its_own_manager_with_one_title_description_and_many_images(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->get(route('home-banners.index'))->assertOk()
            ->assertSee('Headline')->assertSee('Description')->assertDontSee('for="banner_page_id"')
            ->assertSee('Choose images to add')->assertDontSee('Add images</button>')->assertSee('Crop and continue')->assertSee('Show Home Banner')
            ->assertSee("data-notifications='[]'", false);

        $banner = HomeBanner::firstOrFail();
        $this->put(route('home-banners.update'), [
            'title' => 'One home headline',
            'description' => 'One shared home description.',
            'is_active' => '1',
        ])->assertRedirect(route('home-banners.index'))->assertSessionHasNoErrors();

        $this->putJson(route('home-banners.visibility'), ['is_active' => 0])
            ->assertOk()->assertJson(['success' => true, 'is_active' => false, 'message' => 'Home Banner is now hidden.']);
        $this->assertFalse($banner->refresh()->is_active);
        $this->putJson(route('home-banners.visibility'), ['is_active' => 1])->assertOk();

        $this->post(route('home-banners.images.store'), [
            'images' => [UploadedFile::fake()->image('home-one.jpg'), UploadedFile::fake()->image('home-two.jpg')],
        ])->assertRedirect(route('home-banners.index'))->assertSessionHasNoErrors();
        $this->get(route('home-banners.index'))->assertOk()->assertSee('data-order-up')->assertSee('data-image-visibility')
            ->assertSee('data-confirm="Remove this Home banner image?"', false);

        $this->assertSame('One home headline', $banner->refresh()->title);
        $this->assertSame('One shared home description.', $banner->description);
        $this->assertDatabaseCount('home_banner_images', 2);
        $this->assertDatabaseCount('page_banners', 0);
        foreach (HomeBannerImage::all() as $image) $this->assertFileExists(public_path($image->image_path));

        $image = HomeBannerImage::firstOrFail();
        $this->put(route('home-banners.images.update', $image), ['is_active' => '0'])
            ->assertRedirect(route('home-banners.index'))->assertSessionHasNoErrors();
        $this->assertFalse($image->refresh()->is_active);

        $images = HomeBannerImage::orderBy('position')->get();
        $this->putJson(route('home-banners.images.update', $images[1]), ['is_active' => 1])
            ->assertOk()->assertJson(['success' => true, 'is_active' => true]);
        $this->putJson(route('home-banners.images.order'), ['image_ids' => [$images[1]->id, $images[0]->id]])
            ->assertOk()->assertJson(['success' => true]);
        $this->assertSame([$images[1]->id, $images[0]->id], HomeBannerImage::orderBy('position')->pluck('id')->all());

        $html = $this->get(route('home'))->assertOk()->getContent();
        $this->assertSame(1, substr_count($html, '<h1 class="site-hero-title">One home headline</h1>'));
        $this->assertSame(1, substr_count($html, 'One shared home description.'));
        foreach (HomeBannerImage::where('is_active', true)->get() as $image) $this->assertStringContainsString($image->image_url, $html);
        foreach (HomeBannerImage::where('is_active', false)->get() as $image) $this->assertStringNotContainsString($image->image_url, $html);

        $this->putJson(route('home-banners.images.update', $images[0]), ['is_active' => 1])->assertOk();
        $html = $this->get(route('home'))->assertOk()->getContent();
        $this->assertStringContainsString($images[0]->refresh()->image_url, $html);
        $this->assertStringContainsString($images[1]->refresh()->image_url, $html);
        $this->assertLessThan(strpos($html, $images[0]->image_url), strpos($html, $images[1]->image_url));
    }

    public function test_pages_banner_has_a_separate_manager_and_specific_page_placement(): void
    {
        $this->assertFalse(Schema::hasColumn('page_banners', 'title'));
        $admin = $this->admin();
        $this->actingAs($admin)->get(route('home-banners.index'))->assertOk();
        $this->get(route('page-banners.create'))->assertOk()->assertSee('Banner placement')->assertSee('About page')->assertSee('Blog pages')->assertSee('Contact Us')->assertSee('Crop banner image to 16:3')->assertSee('page-banner-cropper.js')->assertDontSee('id="banner-title"', false);

        $aboutId = BannerPage::where('slug', 'about')->value('id');
        $this->post(route('page-banners.store'), [
            'banner_page_id' => $aboutId,
            'is_active' => '1',
            'image' => UploadedFile::fake()->image('about.jpg'),
        ])->assertRedirect(route('page-banners.index'))->assertSessionHasNoErrors();

        $banner = PageBanner::firstOrFail();
        $this->assertSame('about', $banner->page->slug);
        $this->assertFileExists(public_path($banner->image_path));
        $this->assertDatabaseCount('home_banner_images', 0);

        $html = $this->get(route('about'))->assertOk()->getContent();
        $this->assertStringContainsString('About page', $html);
        $this->assertStringContainsString('site-hero-pages', $html);
        $this->assertStringContainsString('width="1600" height="300"', $html);
        $this->assertStringNotContainsString('Get in Touch', $html);
    }

    public function test_page_without_an_active_banner_uses_the_16_by_3_default_image(): void
    {
        $this->get(route('about'))->assertOk()
            ->assertSee('site-hero-pages', false)
            ->assertSee('images/page-banner-default.svg', false)
            ->assertSee('width="1600" height="300"', false)
            ->assertSee('<h1 class="site-hero-title">About Us</h1>', false);
    }

    public function test_home_and_page_images_are_limited_to_two_megabytes(): void
    {
        $this->actingAs($this->admin())->get(route('home-banners.index'));
        $this->post(route('home-banners.images.store'), [
            'images' => [UploadedFile::fake()->image('too-large.jpg')->size(2049)],
        ])->assertSessionHasErrors('images.0');

        $this->post(route('page-banners.store'), [
            'banner_page_id' => BannerPage::where('slug', 'blogs')->value('id'),
            'title' => 'Blog title', 'is_active' => '1',
            'image' => UploadedFile::fake()->image('too-large-page.jpg')->size(2049),
        ])->assertSessionHasErrors('image');
    }

    public function test_page_banner_filters_are_distinct_and_preserve_pagination(): void
    {
        $admin = $this->admin();
        foreach (['about', 'blogs'] as $slug) {
            for ($i = 1; $i <= 11; $i++) {
                PageBanner::create([
                    'banner_page_id' => BannerPage::where('slug', $slug)->value('id'),
                    'image_path' => 'uploads/page-banners/sample-'.$slug.'-'.$i.'.jpg',
                    'is_active' => true,
                ]);
            }
        }

        $this->actingAs($admin)->get(route('page-banners.index', ['target' => 'blogs']))
            ->assertOk()->assertSee('sample-blogs-11.jpg')->assertDontSee('sample-about-1.jpg')->assertSee('target=blogs', false);
        $this->get(route('page-banners.index', ['target' => 'blogs', 'page' => 2]))
            ->assertOk()->assertSee('sample-blogs-1.jpg')->assertDontSee('sample-blogs-11.jpg');
    }

    public function test_users_without_banner_permissions_cannot_write_to_either_banner_table(): void
    {
        $user = User::factory()->create([
            'role_id' => Role::where('name', 'Content Manager')->value('id'),
            'permissions' => ['banners' => ['view' => true]],
            'is_active' => true,
        ]);

        $this->actingAs($user)->get(route('home-banners.index'))->assertOk();
        $this->put(route('home-banners.update'), ['title' => 'No', 'description' => 'No', 'is_active' => '1'])->assertForbidden();
        $this->putJson(route('home-banners.visibility'), ['is_active' => 0])->assertForbidden();
        $this->get(route('page-banners.create'))->assertForbidden();
        $this->assertDatabaseCount('page_banners', 0);
    }

    private function admin(): User
    {
        return User::factory()->create([
            'role_id' => Role::where('name', 'Super Admin')->value('id'),
            'permissions' => User::fullAccessPermissions(),
            'is_active' => true,
        ]);
    }
}
