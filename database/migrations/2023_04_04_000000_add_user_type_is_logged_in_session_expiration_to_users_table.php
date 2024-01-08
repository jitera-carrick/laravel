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
            // Add new columns for user type, is logged in, and session expiration
            $table->string('user_type')->after('updated_at');
            $table->boolean('is_logged_in')->default(false)->after('user_type');
            $table->timestamp('session_expiration')->nullable()->after('is_logged_in');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Remove the columns if the migration is rolled back
            $table->dropColumn(['user_type', 'is_logged_in', 'session_expiration']);
        });
    }
};
