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
        // Assuming the task is to add new columns to the 'requests' table
        Schema::table('requests', function (Blueprint $table) {
            // Add new columns here
            // Example: $table->string('new_column')->after('existing_column');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('requests', function (Blueprint $table) {
            // Remove the new columns here
            // Example: $table->dropColumn('new_column');
        });
    }
};
