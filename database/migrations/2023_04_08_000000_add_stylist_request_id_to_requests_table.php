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
            // Add new column for stylist_request_id
            $table->unsignedBigInteger('stylist_request_id')->after('request_image_id');

            // Define the foreign key constraint for stylist_request_id
            $table->foreign('stylist_request_id')->references('id')->on('stylist_requests');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('requests', function (Blueprint $table) {
            // Remove the foreign key constraint before dropping the column
            $table->dropForeign(['stylist_request_id']);

            // Remove the column if the migration is rolled back
            $table->dropColumn(['stylist_request_id']);
        });
    }
};
