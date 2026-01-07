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
        Schema::create('user_invitations', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->string('token')->unique();
            $table->string('role');
            $table->foreignId('invited_by')
                ->constrained('users')
                ->onDelete('cascade');
            $table->string('company_name')->nullable();
            $table->string('unique_code')->unique();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('expires_at');
            $table->enum('status', ['pending', 'accepted', 'expired'])->default('pending');
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamps();
            
            // Indexes
            $table->index('email');
            $table->index('status');
            $table->index('invited_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_invitations');
    }
};
