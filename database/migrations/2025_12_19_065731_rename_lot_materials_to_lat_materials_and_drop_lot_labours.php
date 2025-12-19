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
        // Rename lot_materials to lat_materials
        Schema::rename('lot_materials', 'lat_materials');
        
        // Drop lot_labours table
        Schema::dropIfExists('lot_labours');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse: rename lat_materials back to lot_materials
        Schema::rename('lat_materials', 'lot_materials');
        
        // Reverse: recreate lot_labours table (basic structure)
      
    }
};
