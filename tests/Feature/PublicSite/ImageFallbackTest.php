<?php

namespace Tests\Feature\PublicSite;

use App\Models\BlogPost;
use App\Models\HomeBannerImage;
use App\Models\PageBanner;
use Tests\TestCase;

class ImageFallbackTest extends TestCase
{
    public function test_missing_banner_uploads_use_bundled_images(): void
    {
        $home = new HomeBannerImage(['image_path' => 'uploads/home-banners/missing-image.jpg']);
        $page = new PageBanner(['image_path' => 'uploads/page-banners/missing-image.jpg']);

        $this->assertSame(asset('images/login-background.png'), $home->image_url);
        $this->assertSame(asset('images/page-banner-default.svg'), $page->image_url);
        $this->assertFileExists(public_path('images/login-background.png'));
        $this->assertFileExists(public_path('images/page-banner-default.svg'));
    }

    public function test_empty_banner_paths_use_bundled_images_instead_of_the_site_root(): void
    {
        $this->assertSame(asset('images/login-background.png'), (new HomeBannerImage)->image_url);
        $this->assertSame(asset('images/page-banner-default.svg'), (new PageBanner)->image_url);
    }

    public function test_missing_blog_uploads_use_the_bundled_preview_image(): void
    {
        $post = new BlogPost([
            'image_path' => 'uploads/blogs/missing-thumbnail.jpg',
            'article_image_path' => 'uploads/blogs/missing-article.jpg',
        ]);

        $this->assertSame(asset('images/meta-thumbnail.png'), $post->image_url);
        $this->assertSame(asset('images/meta-thumbnail.png'), $post->article_image_url);
        $this->assertFileExists(public_path('images/meta-thumbnail.png'));
    }

    public function test_blog_posts_without_assigned_images_keep_their_optional_image_behavior(): void
    {
        $post = new BlogPost;

        $this->assertNull($post->image_url);
        $this->assertNull($post->article_image_url);
    }

    public function test_existing_images_are_not_replaced_by_fallbacks(): void
    {
        $originalPublicPath = public_path();
        $directory = storage_path('framework/testing/image-fallback-'.bin2hex(random_bytes(8)));
        mkdir($directory.'/uploads/blogs', 0755, true);

        try {
            $this->app->usePublicPath($directory);
            copy($originalPublicPath.'/images/meta-thumbnail.png', public_path('uploads/blogs/existing.png'));

            $home = new HomeBannerImage(['image_path' => 'uploads/blogs/existing.png']);
            $page = new PageBanner(['image_path' => 'uploads/blogs/existing.png']);
            $post = new BlogPost([
                'image_path' => 'uploads/blogs/existing.png',
                'article_image_path' => 'uploads/blogs/existing.png',
            ]);

            foreach ([$home->image_url, $page->image_url, $post->image_url, $post->article_image_url] as $url) {
                $this->assertSame(asset('uploads/blogs/existing.png'), $url);
            }
        } finally {
            unlink($directory.'/uploads/blogs/existing.png');
            rmdir($directory.'/uploads/blogs');
            rmdir($directory.'/uploads');
            rmdir($directory);
            $this->app->usePublicPath($originalPublicPath);
        }
    }
}
