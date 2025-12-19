<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('lat_materials', function (Blueprint $table) {
            // First, drop the existing foreign key constraint
            $table->dropForeign(['lat_id']);
            
            // Then, add the correct foreign key constraint
            $table->foreign('lat_id')
                  ->references('id')
                  ->on('lats')
                  ->cascadeOnDelete();
        });
    }

    public function down()
    {
        Schema::table('lot_materials', function (Blueprint $table) {
            $table->dropForeign(['lot_id']);
            $table->foreign('lot_id')
                  ->references('id')
                  ->on('lots')
                  ->cascadeOnDelete();
        });
    }
};