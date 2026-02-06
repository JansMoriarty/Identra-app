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
        Schema::create('guru_positions', function (Blueprint $table) {
            $table->id();

            // Sesuaikan tipe datanya dengan ID di tabel gurus
            $table->uuid('guru_id')->index();
            // Jika tabel gurus ada di database ini, sebaiknya tambahkan foreign key:
            // $table->foreign('guru_id')->references('id')->on('gurus')->onDelete('cascade');

            $table->foreignId('position_id')
                ->constrained('positions')
                ->cascadeOnDelete();

            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guru_positions');
    }
};
