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
        Schema::create('lats', function (Blueprint $table) {
            $table->id();
            $table->string('lat_no')->unique();
            $table->string('design_name')->nullable();
            $table->string('customer_name')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('lats');
    }
};
