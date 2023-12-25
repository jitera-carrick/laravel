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
        Schema::table('login_attempts', function (Blueprint $table) {
            // Add new columns
            $table->timestamp('attempted_at')->after('updated_at');
            $table->boolean('success')->after('attempted_at');
            $table->unsignedBigInteger('user_id')->after('success');
            
            // Define the foreign key relationship
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('login_attempts', function (Blueprint $table) {
            // Drop the foreign key constraint before dropping the column
            $table->dropForeign(['user_id']);
            
            // Remove the columns in the reverse order that they were added
            $table->dropColumn('user_id');
            $table->dropColumn('success');
            $table->dropColumn('attempted_at');
        });
    }
};
