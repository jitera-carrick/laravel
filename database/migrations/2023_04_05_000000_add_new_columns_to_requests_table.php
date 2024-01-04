<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Assuming the new columns to be added are 'priority' and 'assigned_to'
// Replace 'priority' and 'assigned_to' with the actual names of your new columns
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('requests', function (Blueprint $table) {
            // Add new columns to the requests table
            $table->string('priority')->after('status'); // Assuming 'priority' is a string
            $table->unsignedBigInteger('assigned_to')->nullable()->after('priority'); // Assuming 'assigned_to' is a foreign key to users table

            // Define the foreign key constraint for assigned_to
            $table->foreign('assigned_to')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('requests', function (Blueprint $table) {
            // Remove the foreign key constraint before dropping the column
            $table->dropForeign(['assigned_to']);

            // Remove the columns if the migration is rolled back
            $table->dropColumn(['priority', 'assigned_to']);
        });
    }
};
