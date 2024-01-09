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
        Schema::table('email_verifications', function (Blueprint $table) {
            // Assuming the 'id', 'created_at', 'updated_at', 'token', and 'user_id' columns need to be added

            // Add 'id' column
            $table->id();

            // Add 'created_at' and 'updated_at' columns
            $table->timestamps();

            // Add 'token' column
            $table->string('token', 255);

            // Add 'user_id' column
            $table->unsignedBigInteger('user_id');

            // Add a foreign key constraint to 'user_id' referencing the 'id' on the 'users' table
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('email_verifications', function (Blueprint $table) {
            // Remove the foreign key constraint before dropping the 'user_id' column
            $table->dropForeign(['user_id']);

            // Remove the columns if the migration is rolled back
            $table->dropColumn(['user_id', 'token']);
            $table->dropTimestamps();
            $table->dropColumn('id');
        });
    }
};
