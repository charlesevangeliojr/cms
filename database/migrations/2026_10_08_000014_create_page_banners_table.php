<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('page_banners')) return;

        Schema::create('page_banners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('banner_page_id')->constrained('banner_pages')->cascadeOnDelete();
            $table->string('image_path');
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void {}
};
