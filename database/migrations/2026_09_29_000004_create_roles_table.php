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
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        $timestamp = now();

        // A fresh database starts with only the Super Admin role.
        // Additional roles are created by administrators as needed.
        DB::table('roles')->insert([
            ['name' => 'Super Admin', 'is_active' => true, 'created_at' => $timestamp, 'updated_at' => $timestamp],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
