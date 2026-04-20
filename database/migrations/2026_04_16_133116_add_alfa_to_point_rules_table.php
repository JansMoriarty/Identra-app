<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // INI YANG BENAR

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Mengubah ENUM di database (khusus MySQL/PostgreSQL)
        DB::statement("ALTER TABLE point_rules MODIFY COLUMN trigger_event ENUM('CHECK_IN', 'CHECK_OUT', 'ALFA')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('point_rules', function (Blueprint $table) {
            //
        });
    }
};
