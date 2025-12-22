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
        Schema::table('lats', function (Blueprint $table) {
            $table->integer('pieces')->default(0)->after('customer_name');
            $table->decimal('profit_percentage', 5, 2)->default(0)->after('pieces');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lats', function (Blueprint $table) {
            $table->dropColumn(['pieces', 'profit_percentage']);
        });
    }
};
