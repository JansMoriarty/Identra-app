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
        Schema::create('point_ledgers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('transaction_type', ['EARN', 'SPEND', 'PENALTY', 'ADJUSTMENT']);
            $table->integer('amount'); // Jumlah yang masuk/keluar
            $table->integer('current_balance'); // Saldo setelah transaksi ini
            $table->string('description'); // Keterangan transaksi
            $table->string('reference_id')->nullable(); // ID Absensi atau ID Pembelian terkait
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('point_ledgers');
    }
};
