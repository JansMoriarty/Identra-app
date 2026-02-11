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

            $table->uuid('guru_id')->unique();
            $table->string('image_path')->nullable();
            $table->longText('face_descriptor');

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
