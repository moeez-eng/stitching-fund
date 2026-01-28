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
        Schema::create('investment_pools', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lat_id')->nullable();
            $table->string('design_name')->nullable();
            $table->decimal('amount_required', 15, 2)->default(0);
            $table->integer('number_of_partners')->default(1);
            $table->decimal('total_collected', 15, 2)->default(0);
            $table->decimal('percentage_collected', 5, 2)->default(0);
            $table->decimal('remaining_amount', 15, 2)->default(0);
            $table->json('partners')->nullable();
            $table->foreignId('user_id')->nullable();
            $table->string('status')->default('open');
            $table->decimal('collected_amount', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('investment_pools');
    }
};
