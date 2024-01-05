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
        Schema::table('password_reset_requests', function (Blueprint $table) {
            // Assuming the 'status' column is a string and 'user_id' is an unsigned big integer
            $table->string('status')->after('updated_at');
            $table->unsignedBigInteger('user_id')->after('status');
            
            // Assuming there is a foreign key relationship to the users table
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('password_reset_requests', function (Blueprint $table) {
            // Drop the foreign key constraint before dropping the 'user_id' column
            $table->dropForeign(['user_id']);
            $table->dropColumn(['status', 'user_id']);
        });
    }
};
