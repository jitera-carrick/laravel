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
            // Add new columns to the users table
            $table->string('session_token')->nullable()->after('remember_token');
            $table->boolean('is_logged_in')->default(false)->after('session_token');
            $table->timestamp('session_expiration')->nullable()->after('is_logged_in');
            $table->string('user_type')->after('session_expiration');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Remove the new columns if the migration is rolled back
            $table->dropColumn(['session_token', 'is_logged_in', 'session_expiration', 'user_type']);
        });
    }
};
