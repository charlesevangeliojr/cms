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
            $table->json('permissions')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        $timestamp = now();

        DB::table('roles')->insert([
            [
                'name' => 'Super Admin',
                'permissions' => json_encode([
                    'dashboard' => ['view' => true, 'add' => true, 'edit' => true, 'delete' => true],
                    'banners' => ['view' => true, 'add' => true, 'edit' => true, 'delete' => true],
                    'users' => ['view' => true, 'add' => true, 'edit' => true, 'delete' => true],
                    'contacts' => ['view' => true, 'add' => true, 'edit' => true, 'delete' => true],
                    'newsletters' => ['view' => true, 'add' => true, 'edit' => true, 'delete' => true],
                ]),
                'is_active' => true,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'name' => 'Content Manager',
                'permissions' => json_encode([
                    'dashboard' => ['view' => true],
                    'banners' => ['view' => true, 'add' => true, 'edit' => true],
                    'users' => ['view' => true],
                    'contacts' => ['view' => true, 'edit' => true, 'delete' => true],
                    'newsletters' => ['view' => true, 'edit' => true, 'delete' => true],
                ]),
                'is_active' => true,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'name' => 'Editor',
                'permissions' => json_encode([
                    'dashboard' => ['view' => true],
                    'banners' => ['view' => true, 'edit' => true],
                    'contacts' => ['view' => true, 'edit' => true],
                    'newsletters' => ['view' => true, 'edit' => true],
                ]),
                'is_active' => true,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'name' => 'Viewer / Analyst',
                'permissions' => json_encode([
                    'dashboard' => ['view' => true],
                    'banners' => ['view' => true],
                    'contacts' => ['view' => true],
                    'newsletters' => ['view' => true],
                ]),
                'is_active' => true,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
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
