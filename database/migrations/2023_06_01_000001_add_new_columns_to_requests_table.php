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
        Schema::table('requests', function (Blueprint $table) {
            // Add new columns to the requests table
            // The specific columns to be added are already defined in the "# TABLE" section
            // Since the columns are already added in the previous migration, no new columns are added here.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('requests', function (Blueprint $table) {
            // Since no new columns were added, no columns are removed here.
        });
    }
};
