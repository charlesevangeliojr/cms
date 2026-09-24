<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('roles')
            ->where('name', '!=', 'Super Admin')
            ->update([
                'is_active' => false,
                'updated_at' => now(),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('roles')
            ->whereIn('name', ['Super Admin', 'Content Manager', 'Editor', 'Viewer / Analyst'])
            ->update([
                'is_active' => true,
                'updated_at' => now(),
            ]);
    }
};
