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
        Schema::table('user_invitations', function (Blueprint $table) {
            if (!Schema::hasColumn('user_invitations', 'status')) {
                $table->enum('status', ['pending', 'accepted', 'expired', 'revoked'])
                      ->default('pending')
                      ->after('expires_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_invitations', function (Blueprint $table) {
            if (Schema::hasColumn('user_invitations', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};
