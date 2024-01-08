<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Replace YYYY_MM_DD_HHMMSS with the current date and time
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('hair_stylist_requests', function (Blueprint $table) {
            // Add a new column for request_image_id
            $table->unsignedBigInteger('request_image_id')->after('user_id')->nullable();
            
            // Add a foreign key constraint to request_image_id referencing the id on the request_images table
            $table->foreign('request_image_id')->references('id')->on('request_images');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hair_stylist_requests', function (Blueprint $table) {
            // Remove the foreign key constraint before dropping the column
            $table->dropForeign(['request_image_id']);
            
            // Remove the column if the migration is rolled back
            $table->dropColumn(['request_image_id']);
        });
    }
};
