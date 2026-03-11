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
        Schema::create('assessments', function (Blueprint $table) {
            $table->id();
            // Mengacu ke id di tabel users (Guru yang dinilai)
            $table->foreignId('teacher_id')->constrained('users')->onDelete('cascade');
            // Mengacu ke id di tabel users (Admin yang menilai)
            $table->foreignId('evaluator_id')->constrained('users')->onDelete('cascade');

            $table->foreignId('assessment_period_id')->constrained()->onDelete('cascade');

            $table->text('general_feedback')->nullable();
            $table->decimal('final_score', 5, 2)->nullable();
            $table->boolean('is_visible_to_teacher')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assessments');
    }
};
