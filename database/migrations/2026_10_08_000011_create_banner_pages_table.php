<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('banner_pages')) return;

        Schema::create('banner_pages', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 100)->unique();
            $table->string('title')->nullable();
            $table->longText('content')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        $now = now();
        DB::table('banner_pages')->insert([
            ['name' => 'Home', 'slug' => 'home', 'title' => 'Home', 'content' => '', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'About page', 'slug' => 'about', 'title' => 'About Us', 'content' => 'Turbo Drive intercepted this navigation — no full page reload.', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Blog pages', 'slug' => 'blogs', 'title' => 'Blog', 'content' => '', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Contact Us', 'slug' => 'contact', 'title' => 'Contact Us', 'content' => 'Have a question? Send us a message.', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void {}
};
