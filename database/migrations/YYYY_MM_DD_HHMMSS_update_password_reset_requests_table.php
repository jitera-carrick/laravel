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
        Schema::table('password_reset_requests', function (Blueprint $table) {
            // Add new columns 'token', 'expires_at', 'reset_token', and 'token_expiration' to the password_reset_requests table
            $table->string('token')->after('updated_at');
            $table->dateTime('expires_at')->after('token');
            $table->string('reset_token')->after('expires_at');
            $table->dateTime('token_expiration')->after('reset_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('password_reset_requests', function (Blueprint $table) {
            // Remove the new columns if the migration is rolled back
            $table->dropColumn(['token', 'expires_at', 'reset_token', 'token_expiration']);
        });
    }
};
