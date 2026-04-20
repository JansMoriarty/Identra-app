<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('point_rules', function (Blueprint $table) {
            $table->id();
            $table->string('rule_name'); // Cth: Datang Pagi Banget
            $table->string('target_role'); // Siswa, Guru, Karyawan
            $table->enum('trigger_event', ['CHECK_IN', 'CHECK_OUT']); // Kapan aturan dijalankan
            $table->enum('condition_operator', ['<', '>', '<=', '>=', 'BETWEEN']);
            $table->time('condition_time')->nullable(); // Jika berbasis jam (06:30)
            $table->integer('condition_minute')->nullable(); // Jika berbasis durasi telat (15 menit)
            $table->integer('point_modifier'); // +5 atau -3
            $table->integer('priority')->default(1); // Urutan eksekusi
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('point_rules');
    }
};
