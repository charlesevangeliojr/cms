<?php

namespace Tests\Feature\Admin;

use App\Models\Role;
use App\Models\SiteMetadata;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SiteMetadataManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create([
            'role_id' => Role::where('name', 'Super Admin')->value('id'),
            'permissions' => User::fullAccessPermissions(),
            'is_active' => true,
        ]);
    }

    public function test_admin_can_save_site_wide_metadata_and_it_is_escaped_on_public_pages(): void
    {
        $admin = $this->admin();
        Storage::fake('metadata');

        $this->actingAs($admin)->get(route('seo-metadata.index'))->assertOk()
            ->assertSee('Meta Tags')->assertSee('Social preview image')
            ->assertSee('Search preview')->assertSee('Social share preview')
            ->assertSee('data-character-count="description"', false)
            ->assertDontSee('Search Indexing');

        $this->actingAs($admin)->put(route('seo-metadata.update'), [
            'site_name' => 'Example & Co',
            'title' => 'Welcome to Example',
            'description' => 'A <script>alert(1)</script> safe description',
            'image' => UploadedFile::fake()->image('share.png'),
        ])->assertSessionHasErrors('description');

        $this->put(route('seo-metadata.update'), [
            'site_name' => 'Example & Co',
            'title' => 'Welcome to Example',
            'description' => 'A safe description for our site.',
            'image' => UploadedFile::fake()->image('share.png'),
        ])->assertRedirect(route('seo-metadata.index'))->assertSessionHasNoErrors();

        $this->assertDatabaseHas('site_metadata', ['site_name' => 'Example & Co']);
        $this->get(route('contact'))->assertOk()
            ->assertSee('<meta property="og:site_name" content="Example &amp; Co">', false)
            ->assertSee('<meta name="robots" content="index, follow">', false)
            ->assertSee('A safe description for our site.');
    }

    public function test_metadata_form_rejects_unsafe_image_paths_and_ignores_robots_overrides(): void
    {
        $this->actingAs($this->admin())->put(route('seo-metadata.update'), [
            'site_name' => 'Example',
            'title' => 'Welcome',
            'description' => 'Description',
            'image' => 'javascript:alert(1).png',
            'robots' => 'noindex, nofollow',
        ])->assertSessionHasErrors('image');

        $this->assertSame(0, SiteMetadata::count());

        $this->put(route('seo-metadata.update'), [
            'site_name' => 'Example',
            'title' => 'Welcome',
            'description' => 'Description',
            'robots' => 'noindex, nofollow',
        ])->assertRedirect(route('seo-metadata.index'))->assertSessionHasNoErrors();

        $this->assertDatabaseHas('site_metadata', ['robots' => 'index, follow']);
    }

    public function test_metadata_routes_require_permissions(): void
    {
        $viewer = User::factory()->create([
            'role_id' => $this->contentManagerRole()->id,
            'permissions' => ['metadata' => ['view' => true]],
            'is_active' => true,
        ]);

        $this->actingAs($viewer)->get(route('seo-metadata.index'))->assertOk();
        $this->put(route('seo-metadata.update'), [
            'site_name' => 'Example',
            'title' => 'Welcome',
            'description' => 'Description',
            'image' => 'images/meta-thumbnail.png',
            'robots' => 'index, follow',
        ])->assertForbidden();
    }
}
