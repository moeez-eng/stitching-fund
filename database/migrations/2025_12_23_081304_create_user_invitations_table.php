<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('user_invitations', function (Blueprint $table) {
            // Add status column if it doesn't exist
            if (!Schema::hasColumn('user_invitations', 'status')) {
                $table->enum('status', ['pending', 'accepted', 'expired'])->default('pending')->after('expires_at');
            }
            
            // Add user_id to track which user accepted the invitation
            if (!Schema::hasColumn('user_invitations', 'user_id')) {
                $table->foreignId('user_id')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete()
                    ->after('invited_by');
            }

            // Make accepted_at nullable if it's not already
            if (Schema::hasColumn('user_invitations', 'accepted_at')) {
                Schema::table('user_invitations', function (Blueprint $table) {
                    $table->timestamp('accepted_at')->nullable()->change();
                });
            }
        });
    }

    public function down()
    {
        Schema::table('user_invitations', function (Blueprint $table) {
            if (Schema::hasColumn('user_invitations', 'status')) {
                $table->dropColumn('status');
            }
            
            if (Schema::hasColumn('user_invitations', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            }
        });
    }
};