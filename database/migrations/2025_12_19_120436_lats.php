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
            $table->string('company_name')->nullable();
            $table->decimal('total_price', 15, 2)->default(0);
            $table->integer('pieces')->default(0);
            $table->decimal('profit_percentage', 5, 2)->default(0);
            $table->decimal('initial_investment', 15, 2)->default(0);
            $table->decimal('market_payments_received', 15, 2)->default(0);
            $table->string('payment_status')->default('pending'); // pending, partial, complete
            $table->decimal('total_with_profit', 15, 2)->default(0);
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->timestamps();
            
            // Composite unique index on lat_no and user_id
            $table->unique(['lat_no', 'user_id']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('lats');
    }
};
