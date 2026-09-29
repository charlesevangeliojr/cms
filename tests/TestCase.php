<?php

namespace Tests;

use App\Models\Role;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
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
