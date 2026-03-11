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
        Schema::create('face_profiles', function (Blueprint $table) {
            $table->id();

            // Menggunakan foreignId jika tabel users menggunakan id() standar
            // atau gunakan $table->uuid('user_id') jika tabel users pakai UUID
            $table->foreignId('user_id')
                ->unique()
                ->constrained('users')
                ->onDelete('cascade');

            $table->string('image_path')->nullable();
            $table->longText('face_descriptor'); // Tempat menyimpan JSON embedding [0.12, -0.04, ...]

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('face_profiles');
    }
};
