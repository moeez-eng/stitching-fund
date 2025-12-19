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
        Schema::table('lot_labours', function (Blueprint $table) {
            $table->dropForeign(['lot_id']);
            $table->foreign('lot_id')->references('id')->on('lats')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lot_labours', function (Blueprint $table) {
            $table->dropForeign(['lot_id']);
            $table->foreign('lot_id')->references('id')->on('lots')->cascadeOnDelete();
        });
    }
};
