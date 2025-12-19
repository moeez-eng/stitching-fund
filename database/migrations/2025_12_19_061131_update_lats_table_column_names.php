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
            $table->renameColumn('coustmer_name', 'customer_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lats', function (Blueprint $table) {
            $table->renameColumn('customer_name', 'coustmer_name');
        });
    }
};
