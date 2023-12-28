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
        Schema::table('sessions', function (Blueprint $table) {
            // Add new columns to the sessions table
            // The specific columns and their types are not provided in the "# TABLE" information.
            // The following is a placeholder example and should be replaced with actual column details.
            $table->string('session_token')->after('id');
            $table->timestamp('expires_at')->nullable()->after('session_token');
            $table->boolean('is_active')->default(true)->after('expires_at');
            $table->unsignedBigInteger('user_id')->after('is_active');
            
            // Add the foreign key constraint to the user_id column
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sessions', function (Blueprint $table) {
            // Remove the foreign key constraint before dropping the column
            $table->dropForeign(['user_id']);
            
            // Remove the new columns if the migration is rolled back
            $table->dropColumn('session_token');
            $table->dropColumn('expires_at');
            $table->dropColumn('is_active');
            $table->dropColumn('user_id');
        });
    }
};
