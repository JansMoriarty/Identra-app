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
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nama lokasi (contoh: Kampus Utama)
            $table->decimal('latitude', 10, 8); // Koordinat lintang
            $table->decimal('longitude', 11, 8); // Koordinat bujur
            $table->integer('radius'); // Jarak radius dalam satuan meter
            $table->boolean('is_active')->default(true); // Status lokasi aktif atau tidak
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
