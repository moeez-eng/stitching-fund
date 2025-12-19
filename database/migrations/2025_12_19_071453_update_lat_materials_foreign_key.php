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
        // First drop the existing foreign key constraint
        Schema::table('lat_materials', function (Blueprint $table) {
            $table->dropForeign(['lot_id']);
        });

        // Rename the column from lot_id to lat_id
        Schema::table('lat_materials', function (Blueprint $table) {
            $table->renameColumn('lot_id', 'lat_id');
        });

        // Add the new foreign key constraint
        Schema::table('lat_materials', function (Blueprint $table) {
            $table->foreign('lat_id')->references('id')->on('lats')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the foreign key constraint
        Schema::table('lat_materials', function (Blueprint $table) {
            $table->dropForeign(['lat_id']);
        });

        // Rename the column back to lot_id
        Schema::table('lat_materials', function (Blueprint $table) {
            $table->renameColumn('lat_id', 'lot_id');
        });

        // Add the original foreign key constraint
        Schema::table('lat_materials', function (Blueprint $table) {
            $table->foreign('lot_id')->references('id')->on('lats')->cascadeOnDelete();
        });
    }
};
