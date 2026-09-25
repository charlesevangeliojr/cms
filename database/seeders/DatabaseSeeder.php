<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Protected default admin — must not be removable (see UserController::destroy is_protected check).
        User::updateOrCreate(
            ['email' => 'cms@cms.com'],
            [
                'name' => 'CMS Admin',
                'password' => Hash::make('*'),
                'role' => 'Super Admin',
                'is_active' => true,
                'is_protected' => true,
                'permissions' => User::fullAccessPermissions(),
                'email_verified_at' => now(),
            ]
        );
    }
}
