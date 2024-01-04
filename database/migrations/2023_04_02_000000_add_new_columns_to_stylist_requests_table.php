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
        Schema::table('stylist_requests', function (Blueprint $table) {
            // Add new columns to the stylist_requests table
            // The specific columns to be added are not mentioned in the "# TABLE" section.
            // Replace 'new_column_name' with the actual column name you want to add.
            // Replace 'existing_column' with the name of the column after which the new column should be added.
            // Example: $table->string('new_column_name')->after('existing_column');
            
            // If there are more columns to add, repeat the above line with appropriate modifications.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stylist_requests', function (Blueprint $table) {
            // Remove the new columns if the migration is rolled back
            // Replace 'new_column_name' with the actual column name that was added.
            // Example: $table->dropColumn('new_column_name');
            
            // If there are more columns to drop, repeat the above line with appropriate modifications.
        });
    }
};
