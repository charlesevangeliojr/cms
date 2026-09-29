<?php

namespace Database\Seeders;

use App\Models\Role;
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
        // Pages/privileges must exist before roles/users sync their
        // permission matrices against the pages/privileges tables.
        $this->call(PagesAndPrivilegesSeeder::class);

        // Protected default admin — must not be removable (see UserController::destroy is_protected check).
        $superAdminRole = Role::firstOrCreate(
            ['name' => 'Super Admin'],
            [
                'permissions' => User::fullAccessPermissions(),
                'is_active' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'cms@cms.com'],
            [
                'name' => 'CMS Admin',
                'password' => Hash::make('*'),
                'role_id' => $superAdminRole->id,
                'is_active' => true,
                'is_protected' => true,
                'permissions' => User::fullAccessPermissions(),
                'email_verified_at' => now(),
            ]
        );
    }
}
