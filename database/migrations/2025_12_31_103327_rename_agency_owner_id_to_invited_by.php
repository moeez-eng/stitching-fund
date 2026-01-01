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
    Schema::table('users', function (Blueprint $table) {
        // First, make the column nullable if it's not already
        $table->unsignedBigInteger('agency_owner_id')->nullable()->change();
        
        // Drop the existing foreign key constraint
        $table->dropForeign(['agency_owner_id']);
        
        // Rename the column
        $table->renameColumn('agency_owner_id', 'invited_by');
        
        // Re-add the foreign key constraint with the new column name
        $table->foreign('invited_by')
            ->references('id')
            ->on('users')
            ->onDelete('set null');
    });
}

    /**
     * Reverse the migrations.
     */
 public function down(): void
{
    Schema::table('users', function (Blueprint $table) {
        // Drop the foreign key constraint
        $table->dropForeign(['invited_by']);
        
        // Rename the column back
        $table->renameColumn('invited_by', 'agency_owner_id');
        
        // Re-add the foreign key constraint with the original column name
        $table->foreign('agency_owner_id')
            ->references('id')
            ->on('users')
            ->onDelete('set null');
    });
}
};
