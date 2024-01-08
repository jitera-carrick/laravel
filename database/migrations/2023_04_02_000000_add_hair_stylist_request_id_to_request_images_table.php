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
        Schema::table('request_images', function (Blueprint $table) {
            // Add new column for hair_stylist_request_id
            $table->unsignedBigInteger('hair_stylist_request_id');

            // Add a foreign key constraint to hair_stylist_request_id referencing the id on the hair_stylist_requests table
            $table->foreign('hair_stylist_request_id', 'request_images_hair_stylist_request_id_foreign')->references('id')->on('hair_stylist_requests');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('request_images', function (Blueprint $table) {
            // Remove the foreign key constraint before dropping the column
            $table->dropForeign('request_images_hair_stylist_request_id_foreign');
            
            // Remove the column if the migration is rolled back
            $table->dropColumn('hair_stylist_request_id');
        });
    }
};
