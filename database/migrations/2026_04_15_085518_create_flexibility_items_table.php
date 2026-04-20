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
        Schema::create('flexibility_items', function (Blueprint $table) {
            $table->id();
            $table->string('item_name');
            $table->text('description');
            $table->enum('item_type', ['LATE_WAVER', 'WFH_PASS', 'LEAVE_PERMISSION']);
            $table->integer('value_power'); // Nilai kompensasi (misal: 30 untuk 30 menit)
            $table->integer('point_cost');
            $table->integer('stock_limit')->nullable();
            $table->boolean('is_available')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flexibility_items');
    }
};
