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
        Schema::table('hair_stylist_requests', function (Blueprint $table) {
            // Add new column for user_id
            $table->unsignedBigInteger('user_id')->after('id');

            // Add a foreign key constraint to user_id referencing the id on the users table
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hair_stylist_requests', function (Blueprint $table) {
            // Remove the foreign key constraint before dropping the column
            $table->dropForeign(['user_id']);
            
            // Remove the column if the migration is rolled back
            $table->dropColumn(['user_id']);
        });
    }
};
