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
        Schema::table('request_areas', function (Blueprint $table) {
            // Add new column for area
            $table->string('area')->after('area_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('request_areas', function (Blueprint $table) {
            // Remove the column if the migration is rolled back
            $table->dropColumn(['area']);
        });
    }
};
