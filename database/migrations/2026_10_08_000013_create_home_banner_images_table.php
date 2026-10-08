<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('home_banner_images')) return;

        Schema::create('home_banner_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('home_banner_id')->constrained('home_banners')->cascadeOnDelete();
            $table->string('image_path');
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void {}
};
