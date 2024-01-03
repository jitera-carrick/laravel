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
            // The specific columns to be added are not mentioned in the "# TABLE" section.
            // Replace 'new_column_name' with the actual column name you want to add.
            // For example, adding a 'content' column of type text:
            $table->text('content')->after('updated_at'); // Add the 'content' column after the 'updated_at' column
            
            // If there are more columns to add, repeat the above line with appropriate modifications.
            
            // Since there is a relation with users, we assume 'user_id' is a foreign key
            // If 'user_id' is not already a foreign key, uncomment the following lines:
            // $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            // Remove the new columns if the migration is rolled back
            $table->dropColumn('content');
            
            // If there are more columns to drop, repeat the above line with appropriate modifications.
            
            // If 'user_id' was set as a foreign key in the up() method, uncomment the following line:
            // $table->dropForeign(['user_id']);
        });
    }
};
