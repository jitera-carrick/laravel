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
        Schema::table('stylist_requests', function (Blueprint $table) {
            // Add new columns for stylist_requests table
            $table->date('preferred_date')->nullable();
            $table->time('preferred_time')->nullable();
            $table->text('stylist_preferences')->nullable();
            $table->string('status')->default('pending');
            $table->unsignedBigInteger('user_id');

            // Add a foreign key constraint to user_id referencing the id on the users table
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stylist_requests', function (Blueprint $table) {
            // Remove the foreign key constraint before dropping the column
            $table->dropForeign(['user_id']);
            
            // Remove the columns if the migration is rolled back
            $table->dropColumn(['preferred_date', 'preferred_time', 'stylist_preferences', 'status', 'user_id']);
        });
    }
};
