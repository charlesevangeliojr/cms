<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Grant the new Contact Us and Newsletter modules to existing
     * permission matrices. Super Admin keeps full access; every other
     * stored matrix gains the new keys defaulting to false so existing
     * access levels do not widen silently.
     */
    public function up(): void
    {
        $fullAccess = ['view' => true, 'add' => true, 'edit' => true, 'delete' => true];
        $noAccess = ['view' => false, 'add' => false, 'edit' => false, 'delete' => false];

        if (Schema::hasTable('roles')) {
            foreach (DB::table('roles')->select('id', 'name', 'permissions')->get() as $record) {
                $permissions = is_string($record->permissions)
                    ? json_decode($record->permissions, true)
                    : $record->permissions;

                if (! is_array($permissions)) {
                    continue;
                }

                $isSuperAdmin = ($record->name ?? null) === 'Super Admin';

                foreach (['contacts', 'newsletters'] as $module) {
                    if (! isset($permissions[$module])) {
                        $permissions[$module] = $isSuperAdmin ? $fullAccess : $noAccess;
                    }
                }

                DB::table('roles')->where('id', $record->id)->update([
                    'permissions' => json_encode($permissions),
                    'updated_at' => now(),
                ]);
            }
        }

        if (Schema::hasTable('users')) {
            $columns = Schema::getColumnListing('users');
            $select = array_values(array_intersect(['id', 'name', 'role', 'permissions'], $columns));

            foreach (DB::table('users')->select($select)->get() as $record) {
                $permissions = is_string($record->permissions ?? null)
                    ? json_decode($record->permissions, true)
                    : ($record->permissions ?? null);

                if (! is_array($permissions)) {
                    continue;
                }

                $isSuperAdmin = (($record->name ?? null) === 'Super Admin')
                    || (($record->role ?? null) === 'Super Admin');

                foreach (['contacts', 'newsletters'] as $module) {
                    if (! isset($permissions[$module])) {
                        $permissions[$module] = $isSuperAdmin ? $fullAccess : $noAccess;
                    }
                }

                DB::table('users')->where('id', $record->id)->update([
                    'permissions' => json_encode($permissions),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach (['roles', 'users'] as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            foreach (DB::table($table)->select('id', 'permissions')->get() as $record) {
                $permissions = is_string($record->permissions)
                    ? json_decode($record->permissions, true)
                    : $record->permissions;

                if (! is_array($permissions)) {
                    continue;
                }

                unset($permissions['contacts'], $permissions['newsletters']);

                DB::table($table)->where('id', $record->id)->update([
                    'permissions' => json_encode($permissions),
                    'updated_at' => now(),
                ]);
            }
        }
    }
};
