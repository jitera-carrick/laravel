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
            // Add 'is_logged_in' column to 'users' table
            $table->boolean('is_logged_in')->after('remember_token')->default(false);
            // Add 'message_id' column to 'users' table
            $table->unsignedBigInteger('message_id')->nullable()->after('is_logged_in');
            // Add foreign key for 'message_id' column
            $table->foreign('message_id')->references('id')->on('messages')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Remove the foreign key before dropping the column
            $table->dropForeign(['message_id']);
            // Drop 'is_logged_in' and 'message_id' columns
            $table->dropColumn(['is_logged_in', 'message_id']);
        });
    }
};
