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
    Schema::create('classrooms', function (Blueprint $table) {
        $table->id();
        $table->string('name'); // Contoh: XII RPL 1, XI TKJ 2
        $table->string('qr_code')->unique(); // String unik untuk generate QR
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('classrooms');
    }
};
