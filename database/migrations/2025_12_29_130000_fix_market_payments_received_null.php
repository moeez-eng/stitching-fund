<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update existing NULL values to 0 before enforcing NOT NULL constraint
        DB::table('lats')->whereNull('market_payments_received')->update(['market_payments_received' => 0]);
        
        // Make the column NOT NULL if it isn't already
        Schema::table('lats', function (Blueprint $table) {
            $table->decimal('market_payments_received', 15, 2)->default(0)->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lats', function (Blueprint $table) {
            $table->decimal('market_payments_received', 15, 2)->nullable()->change();
        });
    }
};
