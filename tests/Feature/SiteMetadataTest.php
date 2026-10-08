<?php

namespace Tests\Feature;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\SiteMetadata;
use App\Models\User;
use Database\Seeders\PagesAndPrivilegesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SiteMetadataTest extends TestCase
{
    use RefreshDatabase;

    private function editor(bool $edit = true): User
    {
        $this->seed(PagesAndPrivilegesSeeder::class);

        return User::factory()->create(['permissions' => ['metadata' => ['view' => true, 'edit' => $edit]]]);
    }

    private function settings(array $extra = []): array
    {
        return array_merge([
            'site_name' => 'Example Site', 'title' => 'Welcome',
            'description' => 'Our website description.', 'robots' => 'index, follow',
            'keywords' => [' news ', 'updates', 'news', ''],
            'og_title' => 'Share our site', 'og_description' => 'Our sharing description.',
        ], $extra);
    }

    public function test_settings_upload_and_render_in_public_metadata(): void
    {
        Storage::fake('metadata');
        $this->actingAs($this->editor())->put(route('seo-metadata.update'), $this->settings([
            'image' => UploadedFile::fake()->image('share.png', 1200, 630),
            'favicon' => UploadedFile::fake()->image('favicon.png', 32, 32),
        ]))->assertRedirect(route('seo-metadata.index'));

        $metadata = SiteMetadata::current();
        $this->assertSame(['news', 'updates'], $metadata->keywords);
        Storage::disk('metadata')->assertExists(basename($metadata->image));
        Storage::disk('metadata')->assertExists(basename($metadata->favicon));
        $this->get('/')->assertOk()
            ->assertSee('<title>Welcome | Example Site</title>', false)
            ->assertSee('name="description" content="Our website description."', false)
            ->assertSee('name="keywords" content="news, updates"', false)
            ->assertSee('property="og:title" content="Share our site"', false)
            ->assertSee('property="og:description" content="Our sharing description."', false)
            ->assertSee('name="twitter:title" content="Share our site"', false)
            ->assertSee(asset($metadata->image), false)
            ->assertSee('rel="icon" href="'.asset($metadata->favicon).'"', false);
        $this->get(route('seo-metadata.index'))->assertOk()->assertSee('multipart/form-data', false);
    }

    public function test_replacements_delete_old_uploads_and_empty_uploads_preserve_them(): void
    {
        Storage::fake('metadata');
        Storage::disk('metadata')->put('old.png', 'old');
        SiteMetadata::current()->update(['image' => 'uploads/metadata/old.png']);
        $this->actingAs($this->editor())->put(route('seo-metadata.update'), $this->settings())->assertSessionHasNoErrors();
        Storage::disk('metadata')->assertExists('old.png');
        $this->put(route('seo-metadata.update'), $this->settings([
            'image' => UploadedFile::fake()->image('replacement.png'),
        ]))->assertSessionHasNoErrors();
        Storage::disk('metadata')->assertMissing('old.png');
    }

    public function test_invalid_and_oversized_uploads_are_rejected_without_changing_settings(): void
    {
        Storage::fake('metadata');
        $original = SiteMetadata::current()->title;
        $this->actingAs($this->editor())->put(route('seo-metadata.update'), $this->settings([
            'image' => UploadedFile::fake()->image('huge.png')->size(2049),
            'favicon' => UploadedFile::fake()->image('huge-icon.png')->size(1025),
        ]))->assertSessionHasErrors(['image', 'favicon']);
        $this->put(route('seo-metadata.update'), $this->settings([
            'image' => UploadedFile::fake()->create('script.php', 1, 'application/x-php'),
            'favicon' => UploadedFile::fake()->create('icon.svg', 1, 'image/svg+xml'),
            'og_title' => '<script>bad</script>', 'keywords' => ['<bad>'],
        ]))->assertSessionHasErrors(['image', 'favicon', 'og_title', 'keywords.0']);
        $this->assertSame($original, SiteMetadata::current()->title);
        $this->assertSame([], Storage::disk('metadata')->allFiles());
    }

    public function test_failed_database_save_removes_new_uploads_and_keeps_originals(): void
    {
        Storage::fake('metadata');
        Storage::disk('metadata')->put('original.png', 'original');
        SiteMetadata::current()->update(['image' => 'uploads/metadata/original.png']);
        $this->actingAs($this->editor());
        $event = 'eloquent.saving: '.SiteMetadata::class;
        Event::listen($event, function () {
            throw new \RuntimeException('Simulated save failure');
        });
        $this->withoutExceptionHandling();
        try {
            $this->put(route('seo-metadata.update'), $this->settings([
                'image' => UploadedFile::fake()->image('new.png'),
                'favicon' => UploadedFile::fake()->image('favicon.png'),
            ]));
            $this->fail('The database save should fail.');
        } catch (\RuntimeException $exception) {
            $this->assertSame('Simulated save failure', $exception->getMessage());
        } finally {
            Event::forget($event);
        }
        $this->assertSame(['original.png'], Storage::disk('metadata')->allFiles());
        $this->assertSame('uploads/metadata/original.png', SiteMetadata::current()->image);
    }

    public function test_metadata_requires_permission_and_viewers_cannot_save(): void
    {
        $this->get(route('seo-metadata.index'))->assertRedirect(route('login'));
        $this->actingAs($this->editor(false))->get(route('seo-metadata.index'))
            ->assertOk()->assertSee('view-only access')->assertDontSee('Save Settings');
        $this->put(route('seo-metadata.update'), $this->settings())->assertForbidden();
        $this->actingAs(User::factory()->create(['permissions' => ['metadata' => ['view' => false, 'edit' => false]]]))
            ->get(route('seo-metadata.index'))->assertForbidden();
    }

    public function test_empty_social_fields_fall_back_and_article_metadata_takes_precedence(): void
    {
        SiteMetadata::current()->update(['title' => 'Site title', 'description' => 'Site description', 'og_title' => null, 'og_description' => null]);
        $this->get('/')->assertOk()->assertSee('property="og:title" content="Site title |', false)
            ->assertSee('property="og:description" content="Site description"', false);
        SiteMetadata::current()->update(['og_title' => 'Global social title', 'og_description' => 'Global social description']);
        $category = BlogCategory::firstOrCreate(['slug' => 'metadata-test'], ['name' => 'Metadata Test']);
        $post = BlogPost::create([
            'blog_category_id' => $category->id, 'title' => 'Article title', 'slug' => 'article',
            'content' => '<p>Article body</p>', 'excerpt' => 'Article description',
            'is_visible' => true, 'published_at' => now()->subMinute(), 'image_path' => 'article.png',
        ]);
        $this->get($post->public_url)->assertOk()->assertSee('property="og:title" content="Article title |', false)
            ->assertSee('property="og:description" content="Article description"', false)
            ->assertSee(asset('uploads/blogs/article.png'), false)
            ->assertDontSee('Global social title');
    }
}
