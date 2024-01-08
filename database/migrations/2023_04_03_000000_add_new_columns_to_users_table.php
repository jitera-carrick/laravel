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
            // Check if the columns do not exist before adding them
            if (!Schema::hasColumn('users', 'session_token')) {
                $table->string('session_token')->nullable()->after('remember_token'); // Use nullable as in the existing code
            }
            if (!Schema::hasColumn('users', 'is_logged_in')) {
                $table->boolean('is_logged_in')->default(false)->after('session_token');
            }
            if (!Schema::hasColumn('users', 'session_expiration')) {
                $table->timestamp('session_expiration')->nullable()->after('is_logged_in');
            }
            if (!Schema::hasColumn('users', 'user_type')) {
                $table->string('user_type')->after('session_expiration');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Remove the columns if the migration is rolled back
            $table->dropColumn(['session_token', 'is_logged_in', 'session_expiration', 'user_type']);
        });
    }
};
