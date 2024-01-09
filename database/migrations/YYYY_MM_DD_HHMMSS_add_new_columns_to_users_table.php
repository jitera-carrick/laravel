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
            // Add new columns for email_verified_at and remember_token
            $table->timestamp('email_verified_at')->nullable()->after('email');
            $table->string('remember_token', 100)->nullable()->after('password');

            // Add foreign key constraint for password_resets.user_id
            // Assuming that the password_resets table has a 'user_id' column
            $table->foreign('id')->references('user_id')->on('password_resets');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Remove the foreign key constraint before dropping the columns
            $table->dropForeign(['id']);

            // Remove the columns if the migration is rolled back
            $table->dropColumn(['email_verified_at', 'remember_token']);
        });
    }
};
