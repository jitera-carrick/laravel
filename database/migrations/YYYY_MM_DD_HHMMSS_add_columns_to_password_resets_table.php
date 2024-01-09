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
        Schema::table('password_resets', function (Blueprint $table) {
            // Assuming 'id', 'created_at', 'updated_at' are already present in the table
            // Add new columns for email, token, and user_id
            $table->string('email')->after('updated_at');
            $table->string('token')->after('email');
            $table->unsignedBigInteger('user_id')->after('token');

            // Add foreign key constraint
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('password_resets', function (Blueprint $table) {
            // Remove the foreign key constraint before dropping the column
            $table->dropForeign(['user_id']);

            // Remove the columns if the migration is rolled back
            $table->dropColumn(['email', 'token', 'user_id']);
        });
    }
};
