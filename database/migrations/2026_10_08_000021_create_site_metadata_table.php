<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('site_metadata')) return;

        Schema::create('site_metadata', function (Blueprint $table) {
            $table->id();
            $table->string('site_name', 100);
            $table->string('title', 150);
            $table->string('description', 320);
            $table->string('image', 255);
            $table->string('robots', 32)->default('index, follow');
            $table->timestamps();
        });
    }

    public function down(): void {}
};
