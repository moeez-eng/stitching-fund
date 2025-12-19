<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   // In the generated migration file
public function up()
{
    Schema::create('expenses', function (Blueprint $table) {
        $table->id();
        $table->foreignId('lot_no')->constrained('lots', 'lot_no')->onDelete('cascade');
        $table->string('labour_type');
        $table->decimal('unit', 10, 2);
        $table->decimal('rate', 10, 2);
        $table->decimal('pieces', 10, 2);
        $table->decimal('price', 10, 2);
        $table->date('dated');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
