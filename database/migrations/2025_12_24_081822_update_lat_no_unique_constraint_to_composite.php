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
        Schema::table('lats', function (Blueprint $table) {
            // Drop the existing unique index on lat_no
            $table->dropUnique('lats_lat_no_unique');
            
            // Add a composite unique index on lat_no and user_id
            $table->unique(['lat_no', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lats', function (Blueprint $table) {
            // Drop the composite unique index
            $table->dropUnique(['lat_no', 'user_id']);
            
            // Recreate the original unique index on lat_no
            $table->unique('lat_no');
        });
    }
};
