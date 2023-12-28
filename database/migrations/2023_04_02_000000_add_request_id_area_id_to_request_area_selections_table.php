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
            // Add new columns for request_id and area_id
            $table->unsignedBigInteger('request_id');
            $table->unsignedBigInteger('area_id');
            
            // Add foreign key constraints
            $table->foreign('request_id')->references('id')->on('requests');
            $table->foreign('area_id')->references('id')->on('areas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('request_area_selections', function (Blueprint $table) {
            // Remove the foreign key constraints before dropping the columns
            $table->dropForeign(['request_id']);
            $table->dropForeign(['area_id']);
            
            // Remove the columns if the migration is rolled back
            $table->dropColumn(['request_id', 'area_id']);
        });
    }
};
