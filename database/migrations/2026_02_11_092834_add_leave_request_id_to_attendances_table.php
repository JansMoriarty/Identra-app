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
        Schema::table('attendances', function (Blueprint $table) {
            // Kita pakai foreignId agar terhubung ke tabel izin (asumsi nama tabelnya: leave_requests)
            $table->foreignId('leave_request_id')
                ->nullable()
                ->after('guru_id') // Letakkan setelah guru_id
                ->constrained('leave_requests')
                ->onDelete('cascade'); // Jika pengajuan izin dihapus, data absennya juga hilang
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            //
        });
    }
};
