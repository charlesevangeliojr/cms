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
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->nullable()->after('email');
            $table->string('contact')->nullable()->after('role');
            $table->string('branch')->nullable()->after('contact');
            $table->boolean('is_active')->default(true)->after('branch');
            $table->json('permissions')->nullable()->after('is_active');
        });

        // Existing accounts were full admins before roles existed.
        DB::table('users')->whereNull('role')->update(['role' => 'Super Admin']);
        DB::table('users')->whereNull('is_active')->update(['is_active' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'contact', 'branch', 'is_active', 'permissions']);
        });
    }
};
