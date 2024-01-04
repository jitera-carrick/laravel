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
            // Add new columns to the password_reset_requests table
            // The specific columns to be added are not mentioned in the "# TABLE" section.
            // The following is a placeholder for the actual column names and types.
            $table->string('new_column_name1')->after('updated_at'); // Replace 'new_column_name1' with the actual column name.
            $table->integer('new_column_name2')->after('new_column_name1'); // Replace 'new_column_name2' with the actual column name.
            // Add more columns as needed.
            
            // Assuming 'user_id' is an existing column, create a foreign key relationship
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('password_reset_requests', function (Blueprint $table) {
            // Remove the foreign key before dropping the column
            $table->dropForeign(['user_id']);
            
            // Remove the new columns if the migration is rolled back
            $table->dropColumn('new_column_name1');
            $table->dropColumn('new_column_name2');
            // Drop more columns as needed.
        });
    }
};
