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
            // Menggunakan user_id yang merujuk ke tabel users
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('class_code');
            $table->date('tanggal');
            $table->time('jam_masuk');
            $table->string('metode')->default('qr_code');
            $table->timestamps();
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
