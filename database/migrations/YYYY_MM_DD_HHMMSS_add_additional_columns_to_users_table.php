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
            // Add new columns for email_verified, session_token, and session_expiration
            $table->boolean('email_verified')->default(false)->after('email');
            $table->string('session_token')->nullable()->after('remember_token');
            $table->dateTime('session_expiration')->nullable()->after('session_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Remove the columns if the migration is rolled back
            $table->dropColumn(['email_verified', 'session_token', 'session_expiration']);
        });
    }
};
