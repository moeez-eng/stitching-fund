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
       Schema::create('lot_labours', function (Blueprint $table) {
    $table->id();
    $table->foreignId('lot_id')->constrained()->cascadeOnDelete();
    $table->dateTime('dated');
    $table->string('labour_type');
    $table->string('unit');
    $table->decimal('rate', 10, 2);
    $table->integer('pieces');
    $table->decimal('price', 10, 2);
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lot_labours');
    }
};
