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
            $table->string('title')->after('updated_at');
            $table->string('status')->after('title');
            $table->unsignedBigInteger('user_id')->after('status');
            
            // Assuming 'users' table has 'id' as the primary key
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
            
            // Remove the new columns if the migration is rolled back
            $table->dropColumn(['title', 'status', 'user_id']);
        });
    }
};
