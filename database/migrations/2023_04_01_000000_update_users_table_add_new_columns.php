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
            // Add new columns
            $table->string('password_hash')->after('password');
            $table->string('session_token')->nullable()->after('password_hash');
            $table->timestamp('token_expiration')->nullable()->after('session_token');
            $table->string('user_type')->after('token_expiration');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Remove the columns if this migration is rolled back
            $table->dropColumn(['password_hash', 'session_token', 'token_expiration', 'user_type']);
        });
    }
};
