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

            // 1. Tambahkan status 'telat_kompensasi' agar di laporan tetap terlihat 
            // bedanya antara yang benar-benar rajin vs yang dibantu token.
            $table->enum('status', ['izin', 'sakit', 'hadir', 'alpha', 'telat', 'telat_kompensasi']);

            $table->enum('metode', ['face', 'manual'])->default('manual');
            $table->text('keterangan')->nullable();

            // --- TAMBAHAN UNTUK DOMPET INTEGRITAS ---

            // 2. Kolom untuk Interceptor Token
            $table->boolean('is_token_applied')->default(false);
            $table->string('token_info')->nullable(); // Menyimpan nama token yang dipakai (Cth: "Bebas Telat 15 Menit")

            // 3. Kolom untuk Traceability Poin
            // Menyimpan poin yang didapat/dikurangi dari absensi ini
            $table->integer('points_earned')->default(0);

            // 4. Koordinat (Opsional tapi disarankan untuk validasi Face Auth di Laravel)
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            // ----------------------------------------

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
