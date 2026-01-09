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
        Schema::table('wallet_ledgers', function (Blueprint $table) {
            $table->enum('type', ['deposit', 'invest', 'return', 'profit', 'withdrawal', 'pool_adjustment'])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wallet_ledgers', function (Blueprint $table) {
            $table->enum('type', ['deposit', 'invest', 'return', 'profit', 'withdrawal'])->change();
        });
    }
};
