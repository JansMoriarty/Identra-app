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
        Schema::create('class_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // TAMBAHKAN INI: Supaya kita tahu ini absen untuk pelajaran apa
            $table->string('subject_name');

            $table->string('class_code');
            $table->date('tanggal');
            $table->time('jam_masuk');
            $table->string('metode')->default('qr_code');
            $table->timestamps();

            // Tambahkan unique constraint agar satu guru tidak absen 2x di pelajaran & hari yang sama
            $table->unique(['user_id', 'subject_name', 'class_code', 'tanggal']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_attendances');
    }
};
