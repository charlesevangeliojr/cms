<?php

namespace Tests;

use App\Models\Role;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Seed the cms page/privilege registry rows (now in a seeder, not the
     * migration) so permission matrices work on refreshed databases.
     */
    protected $seed = true;

    protected $seeder = \Database\Seeders\PagesAndPrivilegesSeeder::class;

    /**
     * Non-admin role used by tests. Fresh databases only seed Super Admin,
     * so tests that need another role create it here.
     */
    protected function contentManagerRole(): Role
    {
        return Role::firstOrCreate(
            ['name' => 'Content Manager'],
            ['is_active' => false],
        );
    }
}
