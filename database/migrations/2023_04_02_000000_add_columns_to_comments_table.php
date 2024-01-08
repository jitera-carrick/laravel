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
            // Assuming the columns 'title' and 'status' need to be added
            $table->string('title')->nullable();
            $table->string('status')->default('pending');

            // Assuming 'user_id' is a foreign key to the 'users' table
            $table->unsignedBigInteger('user_id')->after('status');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            // Drop the foreign key constraint before dropping the 'user_id' column
            $table->dropForeign(['user_id']);
            
            // Drop the 'title' and 'status' columns
            $table->dropColumn(['title', 'status', 'user_id']);
        });
    }
};
