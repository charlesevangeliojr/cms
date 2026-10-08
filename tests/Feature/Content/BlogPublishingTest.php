<?php

namespace Tests\Feature\Content;

use App\Models\BlogCategory;
use App\Models\BlogAuthor;
use App\Models\BlogPost;
use App\Models\BlogTag;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BlogPublishingTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role_id' => Role::where('name', 'Super Admin')->value('id'), 'permissions' => User::fullAccessPermissions(), 'is_active' => true]);
    }

    private function data(array $extra = []): array
    {
        return array_merge(['title' => 'A new beginning', 'content' => '<h2>Hello readers</h2><p>Our latest story.</p>', 'author' => 'Editorial team', 'blog_category_id' => BlogCategory::where('slug', 'news')->value('id'), 'is_visible' => '0'], $extra);
    }

    private function makePost(array $extra = []): BlogPost
    {
        $data = $this->data(['slug' => 'a-new-beginning', ...$extra]);
        $author = BlogAuthor::firstOrCreate(['name' => $data['author']]);
        unset($data['author'], $data['tags']);

        return BlogPost::create([...$data, 'blog_author_id' => $author->id]);
    }

    public function test_admin_editor_and_list_are_available_and_hidden_posts_are_private(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->get(route('blogs.create'))->assertOk()->assertSee('Search engine listing')->assertSee('Thumbnail / sharing image')->assertSee('Image inside the blog post');
        $this->post(route('blogs.store'), $this->data())->assertRedirect(route('blogs.index'));
        $post = BlogPost::firstOrFail();
        $this->assertSame('a-new-beginning', $post->slug);
        $this->assertNull($post->published_at);
        $this->get(route('blogs.index'))->assertOk()->assertSee('A new beginning');
        $this->get(route('blogs.edit', $post))->assertOk();
        $this->get(route('blog.index'))->assertOk()->assertDontSee('A new beginning');
        $this->get($post->public_url)->assertNotFound();
        $this->get(route('sitemap'))->assertOk()->assertDontSee($post->public_url);
    }

    public function test_blog_categories_can_be_created_and_empty_categories_deleted(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->get(route('blog-categories.index'))->assertOk()
            ->assertSee('Existing categories')->assertSee('Add a category');

        $this->post(route('blog-categories.store'), ['name' => '  Research & Insights  '])
            ->assertRedirect(route('blog-categories.index'))->assertSessionHasNoErrors();
        $category = BlogCategory::where('slug', 'research-insights')->firstOrFail();
        $this->assertSame('Research & Insights', $category->name);

        $this->post(route('blog-categories.store'), ['name' => 'Research Insights'])
            ->assertSessionHasErrors('slug');

        $this->delete(route('blog-categories.destroy', $category))
            ->assertRedirect(route('blog-categories.index'))->assertSessionHas('success');
        $this->assertDatabaseMissing('blog_categories', ['id' => $category->id]);
    }

    public function test_category_deletion_is_blocked_when_posts_use_it_or_it_is_the_last_category(): void
    {
        $this->actingAs($this->admin());
        $post = $this->makePost();

        $this->delete(route('blog-categories.destroy', $post->category))
            ->assertRedirect(route('blog-categories.index'))->assertSessionHas('error');
        $this->assertDatabaseHas('blog_categories', ['id' => $post->blog_category_id]);

        $unused = BlogCategory::where('slug', 'updates')->firstOrFail();
        $this->delete(route('blog-categories.destroy', $unused))->assertSessionHas('success');
        $unused = BlogCategory::where('slug', 'guides')->firstOrFail();
        $this->delete(route('blog-categories.destroy', $unused))->assertSessionHas('success');
        $lastCategory = BlogCategory::where('slug', 'news')->firstOrFail();
        $this->delete(route('blog-categories.destroy', $lastCategory))->assertSessionHas('error');
        $this->assertDatabaseHas('blog_categories', ['id' => $lastCategory->id]);
    }

    public function test_category_management_requires_blog_add_and_delete_permissions(): void
    {
        $viewer = User::factory()->create([
            'role_id' => $this->contentManagerRole()->id,
            'permissions' => ['blogs' => ['view' => true]],
            'is_active' => true,
        ]);
        $category = BlogCategory::where('slug', 'updates')->firstOrFail();

        $this->actingAs($viewer)->get(route('blog-categories.index'))->assertOk();
        $this->post(route('blog-categories.store'), ['name' => 'Announcements'])->assertForbidden();
        $this->delete(route('blog-categories.destroy', $category))->assertForbidden();
    }

    public function test_blog_editor_shows_existing_tags_and_allows_new_comma_separated_tags(): void
    {
        BlogTag::create(['name' => 'Community']);
        $admin = $this->admin();

        $this->actingAs($admin)->get(route('blogs.create'))->assertOk()
            ->assertSee('Matching existing tags')
            ->assertSee('Community')
            ->assertDontSee('data-blog-tag="Community"', false)
            ->assertSee('Save post')
            ->assertDontSee('fixed inset-x-0 bottom-0', false)
            ->assertSee('Separate multiple tags with commas.')
            ->assertDontSee('Type a tag to see existing matches.')
            ->assertSee('absolute left-0 right-0 top-full', false);

        $this->actingAs($admin)->post(route('blogs.store'), $this->data(['tags' => 'community, New topic']))
            ->assertRedirect(route('blogs.index'));
        $this->assertSame(['Community', 'New topic'], BlogPost::firstOrFail()->tags);
        $this->assertSame(2, BlogTag::count());
    }

    public function test_published_article_has_canonical_social_metadata_schema_and_sitemap(): void
    {
        $this->actingAs($this->admin())->post(route('blogs.store'), $this->data(['is_visible' => '1', 'seo_title' => 'A better search title', 'meta_description' => 'A concise search description.', 'tags' => 'News, Ideas, News', 'excerpt' => 'An article summary.']))->assertSessionHasNoErrors();
        $post = BlogPost::firstOrFail();
        $this->assertNotNull($post->published_at);
        $this->assertSame(['News', 'Ideas'], $post->tags);
        $response = $this->get($post->public_url)->assertOk()
            ->assertSee('<title>A better search title | '.config('metadata.defaults.site_name').'</title>', false)
            ->assertSee('<meta name="description" content="A concise search description.">', false)
            ->assertSee('<link rel="canonical" href="'.$post->public_url.'">', false)
            ->assertSee('<meta property="og:type" content="article">', false)
            ->assertSee('<h2>Hello readers</h2>', false);
        preg_match('~<script type="application/ld\+json">(.*?)</script>~s', $response->getContent(), $matches);
        $schema = json_decode($matches[1], true, flags: JSON_THROW_ON_ERROR);
        $this->assertSame('BlogPosting', $schema['@type']);
        $this->assertSame($post->public_url, $schema['mainEntityOfPage']['@id']);
        $this->assertSame('Editorial team', $schema['author']['name']);
        $this->get(route('blog.index'))->assertOk()
            ->assertSee('A new beginning')
            ->assertSee('href="'.route('blog.index').'#blog-categories"', false);
        $this->get(route('blog.category', 'news'))->assertOk()
            ->assertSee('A new beginning')
            ->assertSee('href="'.route('blog.category', 'news').'#blog-categories"', false);
        $this->get('/blogs/guides/'.$post->slug)->assertNotFound();
        $sitemap = $this->get(route('sitemap'))->assertOk()->assertSee($post->public_url);
        $this->assertNotFalse(simplexml_load_string($sitemap->getContent()));
    }

    public function test_admin_can_download_a_blog_post_as_a_pdf(): void
    {
        $admin = $this->admin();
        $post = $this->makePost(['title' => 'PDF Export Check']);

        $response = $this->actingAs($admin)->get(route('blogs.pdf', $post));

        $response->assertOk()
            ->assertHeader('content-type', 'application/pdf')
            ->assertHeader('content-disposition', 'attachment; filename="pdf-export-check.pdf"');
        $this->assertStringStartsWith('%PDF-1.4', $response->getContent());
        $this->assertStringContainsString('PDF Export Check', $response->getContent());
    }

    public function test_blog_seo_data_is_stored_in_its_normalized_table(): void
    {
        $post = $this->makePost();
        $post->seo()->create([
            'seo_title' => 'A saved search title',
            'meta_description' => 'A saved search description.',
        ]);

        $this->assertDatabaseHas('blog_post_seo', [
            'blog_post_id' => $post->id,
            'seo_title' => 'A saved search title',
            'meta_description' => 'A saved search description.',
        ]);
        $this->assertSame('A saved search title', $post->fresh()->seo->seo_title);
    }

    public function test_authors_and_tags_are_stored_in_separate_tables(): void
    {
        $post = $this->makePost();
        $post->tags()->sync([BlogTag::firstOrCreate(['name' => 'Migration Test'])->id]);

        $this->assertDatabaseHas('blog_authors', ['name' => 'Editorial team']);
        $this->assertDatabaseHas('blog_tags', ['name' => 'Migration Test']);
        $this->assertDatabaseHas('blog_post_tag', ['blog_post_id' => $post->id]);
        $this->assertSame(['Migration Test'], $post->fresh()->tags);
    }

    public function test_visibility_updates_preserve_first_publication_and_filter_future_posts(): void
    {
        $this->actingAs($this->admin());
        $post = $this->makePost();
        $this->put(route('blogs.update', $post), $this->data(['is_visible' => '1']))->assertSessionHasNoErrors();
        $published = $post->refresh()->published_at;
        $this->put(route('blogs.update', $post), $this->data(['is_visible' => '0']))->assertSessionHasNoErrors();
        $this->get($post->public_url)->assertNotFound();
        $this->assertTrue($published->equalTo($post->refresh()->published_at));
        $post->update(['is_visible' => true, 'published_at' => now()->addDay()]);
        $this->get($post->public_url)->assertNotFound();
        $this->get(route('sitemap'))->assertDontSee($post->public_url);
    }

    public function test_permissions_block_each_write_action_for_a_view_only_account(): void
    {
        $user = User::factory()->create(['role_id' => $this->contentManagerRole()->id, 'permissions' => ['blogs' => ['view' => true]], 'is_active' => true]);
        $post = $this->makePost();
        $this->actingAs($user)->get(route('blogs.index'))->assertOk()->assertDontSee('Add blog post');
        $this->get(route('blogs.create'))->assertForbidden();
        $this->post(route('blogs.store'), $this->data())->assertForbidden();
        $this->get(route('blogs.edit', $post))->assertForbidden();
        $this->put(route('blogs.update', $post), $this->data())->assertForbidden();
        $this->delete(route('blogs.destroy', $post))->assertForbidden();
    }

    public function test_duplicate_handles_and_invalid_seo_lengths_and_empty_content_are_rejected(): void
    {
        $this->actingAs($this->admin());
        $this->makePost();
        $this->post(route('blogs.store'), $this->data(['slug' => 'a-new-beginning']))->assertSessionHasErrors('slug');
        $this->post(route('blogs.store'), $this->data(['title' => 'Other story', 'seo_title' => str_repeat('a', 71), 'meta_description' => str_repeat('a', 161)]))->assertSessionHasErrors(['seo_title', 'meta_description']);
        $this->post(route('blogs.store'), $this->data(['title' => 'Other story', 'content' => '<script>alert(1)</script><p><br></p>']))->assertSessionHasErrors('content');
        $this->post(route('blogs.store'), $this->data(['title' => 'Other story', 'slug' => ['bad']]))->assertSessionHasErrors('slug');
        $this->assertSame(1, BlogPost::count());
    }

    public function test_rich_content_preserves_formatting_and_strips_unsafe_markup(): void
    {
        $this->actingAs($this->admin())->post(route('blogs.store'), $this->data(['is_visible' => '1', 'content' => '<h2 onclick="alert(1)">Heading</h2><p><strong>Bold</strong><em>Italic</em><a href="javascript:alert(1)">Unsafe</a><a href="https://example.com" onmouseover="alert(1)">Safe</a><figure class="table"><table><tbody><tr><th><p>Column</p></th><td><p>Value</p></td></tr></tbody></table></figure><img src=x onerror=alert(1)><script>alert(1)</script><svg onload="alert(1)"></svg></p>']))->assertSessionHasNoErrors();
        $post = BlogPost::firstOrFail();
        $this->assertStringContainsString('<strong>Bold</strong>', $post->content);
        $this->assertStringContainsString('<table><tbody><tr><th><p>Column</p></th><td><p>Value</p></td></tr></tbody></table>', $post->content);
        foreach (['onclick', 'onmouseover', 'onerror', 'javascript:', '<script', '<svg', '<img'] as $unsafe) {
            $this->assertStringNotContainsString($unsafe, $post->content);
        }
        $this->get($post->public_url)->assertOk()
            ->assertSee('href="https://example.com"', false)
            ->assertSee('<table><tbody><tr><th><p>Column</p></th><td><p>Value</p></td></tr></tbody></table>', false);
    }

    public function test_image_upload_replacement_removal_and_delete_cleanup(): void
    {
        Storage::fake('blogs');
        $this->actingAs($this->admin())->post(route('blogs.store'), $this->data(['image' => UploadedFile::fake()->image('thumbnail.jpg'), 'article_image' => UploadedFile::fake()->image('article.jpg'), 'meta_description' => 'A beautiful landscape', 'is_visible' => '1']))->assertSessionHasNoErrors();
        $post = BlogPost::firstOrFail();
        $thumbnail = basename($post->image_path);
        $articleImage = basename($post->article_image_path);
        Storage::disk('blogs')->assertExists($thumbnail);
        Storage::disk('blogs')->assertExists($articleImage);
        $this->get($post->public_url)->assertSee('alt="A beautiful landscape"', false)
            ->assertSee('<meta property="og:image" content="'.$post->image_url.'">', false)
            ->assertSee('<img class="blog-featured-image" src="'.$post->article_image_url.'"', false);
        $this->put(route('blogs.update', $post), $this->data(['image' => UploadedFile::fake()->image('replacement.png')]))->assertSessionHasNoErrors();
        Storage::disk('blogs')->assertMissing($thumbnail);
        Storage::disk('blogs')->assertExists($articleImage);
        $secondThumbnail = basename($post->refresh()->image_path);
        Storage::disk('blogs')->assertExists($secondThumbnail);
        $this->put(route('blogs.update', $post), $this->data(['remove_image' => '1']))->assertSessionHasNoErrors();
        Storage::disk('blogs')->assertMissing($secondThumbnail);
        Storage::disk('blogs')->assertExists($articleImage);
        $this->assertNull($post->refresh()->image_path);
        $this->put(route('blogs.update', $post), $this->data(['image' => UploadedFile::fake()->image('final.jpg')]))->assertSessionHasNoErrors();
        $lastThumbnail = basename($post->refresh()->image_path);
        $this->delete(route('blogs.destroy', $post))->assertRedirect(route('blogs.index'));
        Storage::disk('blogs')->assertMissing($lastThumbnail);
        Storage::disk('blogs')->assertMissing($articleImage);
        $this->assertDatabaseMissing('blog_posts', ['id' => $post->id]);
    }

    public function test_oversized_and_non_image_uploads_are_rejected(): void
    {
        Storage::fake('blogs');
        $this->actingAs($this->admin());
        $this->post(route('blogs.store'), $this->data(['image' => UploadedFile::fake()->image('huge.jpg')->size(2049)]))->assertSessionHasErrors('image');
        $this->post(route('blogs.store'), $this->data(['article_image' => UploadedFile::fake()->image('huge-article.jpg')->size(2049)]))->assertSessionHasErrors('article_image');
        $this->post(route('blogs.store'), $this->data(['image' => UploadedFile::fake()->create('script.svg', 10, 'image/svg+xml')]))->assertSessionHasErrors('image');
        $this->assertSame([], Storage::disk('blogs')->allFiles());
        $this->assertSame(0, BlogPost::count());
    }

    public function test_upgrade_registers_blog_access_without_overwriting_custom_denials(): void
    {
        $fullAdmin = $this->admin();
        $restrictedAdmin = User::factory()->create(['role_id' => $fullAdmin->role_id, 'permissions' => ['users' => ['view' => true]], 'is_active' => true]);
        app(\Database\Seeders\PagesAndPrivilegesSeeder::class)->run();
        $this->assertTrue($fullAdmin->fresh()->canAccess('blogs', 'add'));
        $this->assertFalse($restrictedAdmin->fresh()->canAccess('blogs', 'view'));
        $this->assertTrue($restrictedAdmin->fresh()->canAccess('users', 'view'));
        $this->assertDatabaseHas('users', ['id' => $fullAdmin->id]);
        $this->assertSame(3, BlogCategory::count());
    }
}
