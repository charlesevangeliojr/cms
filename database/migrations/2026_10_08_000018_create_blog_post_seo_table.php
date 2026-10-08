<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('blog_post_seo')) return;

        Schema::create('blog_post_seo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blog_post_id')->unique()->constrained('blog_posts')->cascadeOnDelete();
            $table->string('seo_title', 70)->nullable();
            $table->string('meta_description', 160)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {}
};
