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
        Schema::table('users', function (Blueprint $table) {
            // Assuming new columns need to be added as per the task description
            // Since the task does not specify new columns, no code is added here.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Assuming columns need to be removed as per the task description
            // Since the task does not specify which columns to remove, no code is added here.
        });
    }
};
