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

            // New code integration
            $table->dateTime('request_time')->after('new_column_name2');
            $table->string('reset_token')->after('request_time');
            $table->string('status')->after('reset_token');
            // Assuming 'user_id' is an existing column, create a foreign key relationship
            // Check if user_id column already exists to avoid duplicate column error
            if (!Schema::hasColumn('password_reset_requests', 'user_id')) {
                $table->unsignedBigInteger('user_id')->after('status');
            }
            // Adding the foreign key constraint
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
            // Drop the columns added in the up() method
            $table->dropColumn('request_time');
            $table->dropColumn('reset_token');
            $table->dropColumn('status');
            // Check if user_id column was added in this migration to avoid dropping an existing column
            if (Schema::hasColumn('password_reset_requests', 'user_id')) {
                $table->dropColumn('user_id');
            }
        });
    }
};
