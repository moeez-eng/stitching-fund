<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
  public function up()
{
    Schema::table('users', function (Blueprint $table) {
        // add varchar phone after email
        $table->string('phone', 11)->unique()->after('email');
    });
}



    /**
     * Reverse the migrations.
     */
  public function down()
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropUnique(['phone']);  // Drop the unique index first
        $table->dropColumn('phone');    // Then drop the column
    });
}
};
