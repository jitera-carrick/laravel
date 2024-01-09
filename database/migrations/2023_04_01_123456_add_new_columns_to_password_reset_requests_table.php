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
            // Add new columns to the password_reset_requests table
            $table->string('reset_token')->after('user_id');
            $table->timestamp('token_expiration')->after('reset_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('password_reset_requests', function (Blueprint $table) {
            // Remove the new columns if the migration is rolled back
            $table->dropColumn(['reset_token', 'token_expiration']);
        });
    }
};
