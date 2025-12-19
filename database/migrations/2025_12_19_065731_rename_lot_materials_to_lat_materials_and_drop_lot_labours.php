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
        Schema::create('lot_labours', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lot_id')->constrained()->cascadeOnDelete();
            $table->string('labour');
            $table->decimal('rate', 10, 2);
            $table->decimal('quantity', 10, 2);
            $table->decimal('price', 10, 2);
            $table->timestamps();
        });
    }
};
