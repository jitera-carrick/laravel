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
        Schema::table('comments', function (Blueprint $table) {
            // Add new columns to the comments table
            $table->timestamp('updated_at')->nullable();
            $table->string('title');
            $table->string('status');
            $table->unsignedBigInteger('user_id');
            $table->increments('id');
            $table->timestamp('created_at')->nullable();

            // Add a foreign key constraint to user_id referencing the id on the users table
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            // Remove the foreign key constraint before dropping the column
            $table->dropForeign(['user_id']);
            
            // Remove the columns if the migration is rolled back
            $table->dropColumn(['updated_at', 'title', 'status', 'user_id', 'id', 'created_at']);
        });
    }
};
