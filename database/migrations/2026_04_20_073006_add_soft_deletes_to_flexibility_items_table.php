<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('flexibility_items', function (Blueprint $table) {
            $table->softDeletes(); // Ini akan menambah kolom deleted_at
        });
    }

    public function down()
    {
        Schema::table('flexibility_items', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
