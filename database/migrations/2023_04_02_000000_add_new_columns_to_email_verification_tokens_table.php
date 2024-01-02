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
        Schema::table('email_verification_tokens', function (Blueprint $table) {
            // Add new columns to the email_verification_tokens table
            // The specific columns to be added are not mentioned in the "# TABLE" section.
            // The following is a placeholder for the actual columns that need to be added.
            // Replace 'new_column_name' with the actual column name and specify the correct column type.
            $table->string('new_column_name')->after('existing_column'); // Replace 'existing_column' with the name of the column after which the new column should be added.
            
            // If there are more columns to add, repeat the above line with appropriate modifications.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('email_verification_tokens', function (Blueprint $table) {
            // Remove the new columns if the migration is rolled back
            $table->dropColumn('new_column_name');
            
            // If there are more columns to drop, repeat the above line with appropriate modifications.
        });
    }
};
