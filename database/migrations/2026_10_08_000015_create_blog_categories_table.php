<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('blog_categories')) return;

        Schema::create('blog_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        $now = now();
        DB::table('blog_categories')->insert([
            ['name' => 'News', 'slug' => 'news', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Updates', 'slug' => 'updates', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Guides', 'slug' => 'guides', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void {}
};
