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
        Schema::table('hair_stylist_requests', function (Blueprint $table) {
            // Assuming the 'users' table has 'id' as the primary key
            // and the 'request_images' table has 'id' as the primary key
            // and 'hair_stylist_request_id' as a foreign key

            // Add a foreign key constraint to hair_stylist_request_id referencing the id on the hair_stylist_requests table
            $table->unsignedBigInteger('hair_stylist_request_id');
            $table->foreign('hair_stylist_request_id')->references('id')->on('hair_stylist_requests');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hair_stylist_requests', function (Blueprint $table) {
            // Remove the foreign key constraint before dropping the column
            $table->dropForeign(['hair_stylist_request_id']);
            
            // Remove the column if the migration is rolled back
            $table->dropColumn(['hair_stylist_request_id']);
        });
    }
};
