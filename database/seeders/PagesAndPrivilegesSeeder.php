<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PagesAndPrivilegesSeeder extends Seeder
{
    /**
     * Sync pages/privileges from the cms registry and grant Super Admin
     * full access. Safe to re-run: existing rows are kept, names are
     * refreshed, and only missing grants are inserted.
     */
    public function run(): void
    {
        $now = now();

        foreach (config('cms.pages', []) as $slug => $page) {
            $existing = DB::table('pages')->where('slug', $slug)->first();

            if (! $existing) {
                DB::table('pages')->insert([
                    'slug' => $slug,
                    'name' => $page['name'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            } elseif ($existing->name !== $page['name']) {
                DB::table('pages')->where('slug', $slug)->update([
                    'name' => $page['name'],
                    'updated_at' => $now,
                ]);
            }
        }

        foreach (array_keys(config('cms.privileges', [])) as $privilege) {
            if (! DB::table('privileges')->where('name', $privilege)->exists()) {
                DB::table('privileges')->insert([
                    'name' => $privilege,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        $pages = DB::table('pages')->orderBy('id')->pluck('id', 'slug');
        $privileges = DB::table('privileges')->orderBy('id')->pluck('id', 'name');
        $roleId = DB::table('roles')->where('name', 'Super Admin')->value('id');

        if (! $roleId) {
            return;
        }

        // Insert in the same order as the role/privilege/page unique key:
        // privilege first, then page. This keeps freshly generated IDs
        // ascending when the table is viewed through that index.
        foreach ($privileges->keys()->all() as $action) {
            foreach ($pages->keys()->all() as $page) {
                DB::table('role_privileges')->updateOrInsert(
                    [
                        'role_id' => $roleId,
                        'privilege_id' => $privileges[$action],
                        'page_id' => $pages[$page],
                    ],
                    []
                );
            }
        }
    }
}
