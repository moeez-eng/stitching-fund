<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('lats', function (Blueprint $table) {
            $table->decimal('market_payments_received', 15, 2)->default(0)->after('initial_investment');
            $table->string('payment_status')->default('pending')->after('market_payments_received'); // pending, partial, complete
            $table->decimal('total_with_profit', 15, 2)->default(0)->after('payment_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lats', function (Blueprint $table) {
            $table->dropColumn([
                'market_payments_received',
                'payment_status',
                'total_with_profit'
            ]);
        });
    }
};
