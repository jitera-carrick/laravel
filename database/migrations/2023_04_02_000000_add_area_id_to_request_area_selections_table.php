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
        Schema::table('request_area_selections', function (Blueprint $table) {
            // Add new column for area_id
            $table->unsignedBigInteger('area_id');
            
            // Add a foreign key constraint to area_id referencing the id on the areas table
            $table->foreign('area_id')->references('id')->on('areas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('request_area_selections', function (Blueprint $table) {
            // Remove the foreign key constraint before dropping the column
            $table->dropForeign(['area_id']);
            
            // Remove the column if the migration is rolled back
            $table->dropColumn(['area_id']);
        });
    }
};
