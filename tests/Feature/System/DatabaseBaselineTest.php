<?php

namespace Tests\Feature\System;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DatabaseBaselineTest extends TestCase
{
    use RefreshDatabase;

    public function test_fresh_schema_contains_the_current_cms_tables_and_columns(): void
    {
        foreach ([
            'roles', 'users', 'pages', 'privileges', 'role_privileges', 'user_privileges',
            'contact_messages', 'newsletter_subscribers', 'banner_pages', 'home_banners',
            'home_banner_images', 'page_banners', 'blog_categories', 'blog_posts',
            'blog_post_seo', 'blog_authors', 'blog_tags', 'blog_post_tag', 'site_metadata',
        ] as $table) {
            $this->assertTrue(Schema::hasTable($table), "Expected {$table} in the fresh schema.");
        }

        $this->assertFalse(Schema::hasTable('public_pages'));
        $this->assertTrue(Schema::hasColumns('banner_pages', ['title', 'content', 'slug', 'is_active']));
        $this->assertFalse(Schema::hasColumn('page_banners', 'title'));
    }

    public function test_individual_schema_migrations_leave_existing_database_data_untouched(): void
    {
        $user = User::factory()->create([
            'role_id' => Role::where('name', 'Super Admin')->value('id'),
            'email' => 'preserve-this-user@example.com',
        ]);

        foreach (glob(database_path('migrations/2026_10_08_*.php')) as $migrationPath) {
            $migration = require $migrationPath;
            $migration->up();
        }

        $this->assertDatabaseHas('users', ['id' => $user->id, 'email' => 'preserve-this-user@example.com']);
        $this->assertDatabaseHas('banner_pages', ['slug' => 'about']);
    }
}
