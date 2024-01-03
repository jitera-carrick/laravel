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
        Schema::table('menu_items', function (Blueprint $table) {
            // Add new columns to the menu_items table
            // The specific columns to be added are not mentioned, so this is a generic example.
            // Replace 'new_column_name' with the actual column name you want to add.
            $table->string('new_column_name')->after('existing_column'); // Replace 'existing_column' with the name of the column after which the new column should be added.
            
            // If there are more columns to add, repeat the above line with appropriate modifications.
            
            // Since there is a relation with users, we assume that user_id is a foreign key
            // If user_id is not already a foreign key, then add the foreign key constraint
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            // Remove the new columns if the migration is rolled back
            $table->dropColumn('new_column_name');
            
            // If there are more columns to drop, repeat the above line with appropriate modifications.
            
            // Drop the foreign key constraint if it exists
            $table->dropForeign(['user_id']);
        });
    }
};
