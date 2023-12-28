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
            // Add new columns for password hash, salt, and last password reset
            $table->string('password_hash')->after('password');
            $table->string('password_salt')->after('password_hash');
            $table->timestamp('last_password_reset')->nullable()->after('password_salt');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Remove the columns if the migration is rolled back
            $table->dropColumn(['password_hash', 'password_salt', 'last_password_reset']);
        });
    }
};
