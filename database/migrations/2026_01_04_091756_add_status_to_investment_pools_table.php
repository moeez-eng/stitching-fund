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
        Schema::table('investment_pools', function (Blueprint $table) {
            if (!Schema::hasColumn('investment_pools', 'status')) {
                $table->string('status')->default('open')->after('user_id');
            }
            if (!Schema::hasColumn('investment_pools', 'collected_amount')) {
                $table->decimal('collected_amount', 15, 2)->default(0)->after('status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('investment_pools', function (Blueprint $table) {
            if (Schema::hasColumn('investment_pools', 'status')) {
                $table->dropColumn('status');
            }
            if (Schema::hasColumn('investment_pools', 'collected_amount')) {
                $table->dropColumn('collected_amount');
            }
        });
    }
};
