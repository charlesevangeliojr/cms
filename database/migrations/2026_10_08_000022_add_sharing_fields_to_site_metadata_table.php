<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_metadata', function (Blueprint $table) {
            if (! Schema::hasColumn('site_metadata', 'favicon')) {
                $table->string('favicon')->nullable();
            }
            if (! Schema::hasColumn('site_metadata', 'keywords')) {
                $table->json('keywords')->nullable();
            }
            if (! Schema::hasColumn('site_metadata', 'og_title')) {
                $table->string('og_title', 150)->nullable();
            }
            if (! Schema::hasColumn('site_metadata', 'og_description')) {
                $table->string('og_description', 320)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('site_metadata', function (Blueprint $table) {
            $table->dropColumn(['favicon', 'keywords', 'og_title', 'og_description']);
        });
    }
};
