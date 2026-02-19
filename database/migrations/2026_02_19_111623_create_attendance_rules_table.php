<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('attendance_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // Contoh: 'batas_masuk', 'jam_pulang'
            $table->time('rule_value');      // Menggunakan tipe data TIME agar lebih valid
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_rules');
    }
};
