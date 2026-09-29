<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('privileges', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('role_privileges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->foreignId('privilege_id')->constrained('privileges')->cascadeOnDelete();
            $table->foreignId('page_id')->constrained('pages')->cascadeOnDelete();
            $table->unique(['role_id', 'privilege_id', 'page_id']);
        });

        Schema::create('user_privileges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('privilege_id')->constrained('privileges')->cascadeOnDelete();
            $table->foreignId('page_id')->constrained('pages')->cascadeOnDelete();
            $table->boolean('is_allowed')->default(false);
            $table->unique(['user_id', 'privilege_id', 'page_id']);
        });

        // Page and privilege rows come from the cms registry so fresh
        // databases always match the configured pages.
        $now = now();
        foreach (config('cms.pages', []) as $slug => $page) {
            DB::table('pages')->insert([
                'slug' => $slug,
                'name' => $page['name'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
        foreach (array_keys(config('cms.privileges', [])) as $privilege) {
            DB::table('privileges')->insert([
                'name' => $privilege,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $pages = DB::table('pages')->orderBy('id')->pluck('id', 'slug');
        $privileges = DB::table('privileges')->orderBy('id')->pluck('id', 'name');
        $roles = DB::table('roles')->orderBy('id')->pluck('id', 'name');

        $rows = [];
        foreach ($roles as $roleName => $roleId) {
            // Super Admin gets every privilege on every page.
            if ($roleName !== 'Super Admin') {
                continue;
            }

            // Insert in the same order as the role/privilege/page unique key:
            // privilege first, then page. This keeps freshly generated IDs
            // ascending when the table is viewed through that index.
            foreach ($privileges->keys()->all() as $action) {
                foreach ($pages->keys()->all() as $page) {
                    $rows[] = [
                        'role_id' => $roleId,
                        'privilege_id' => $privileges[$action],
                        'page_id' => $pages[$page],
                    ];
                }
            }
        }

        foreach (array_chunk($rows, 100) as $chunk) {
            DB::table('role_privileges')->insert($chunk);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_privileges');
        Schema::dropIfExists('role_privileges');
        Schema::dropIfExists('privileges');
        Schema::dropIfExists('pages');
    }
};
