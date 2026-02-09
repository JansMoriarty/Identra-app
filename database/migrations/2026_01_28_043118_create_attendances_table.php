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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->uuid('guru_id')->index();
            $table->date('tanggal');
            $table->time('jam_masuk')->nullable();
            $table->time('jam_pulang')->nullable();

            // Status absensi
            $table->enum('status', ['izin', 'sakit', 'hadir', 'alpha', 'telat']);

            // Tambahkan 'manual' di sini
            $table->enum('metode', ['face', 'manual'])->default('manual');

            // Penting untuk alasan izin/sakit
            $table->text('keterangan')->nullable();

            $table->timestamps();
            $table->unique(['guru_id', 'tanggal']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
