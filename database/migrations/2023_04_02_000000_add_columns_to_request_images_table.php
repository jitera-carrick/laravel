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
            // Assuming the columns are to be added and not already existing in the table
            $table->id(); // Adds an auto-incrementing id (primary key) column
            $table->timestamps(); // Adds created_at and updated_at columns
            $table->string('image_path'); // Adds an image_path column
            $table->softDeletes(); // Adds a deleted_at column for soft deletes
            $table->unsignedBigInteger('hair_stylist_request_id'); // Adds a hair_stylist_request_id column

            // Add a foreign key constraint to hair_stylist_request_id referencing the id on the hair_stylist_requests table
            $table->foreign('hair_stylist_request_id')->references('id')->on('hair_stylist_requests');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('request_images', function (Blueprint $table) {
            // Remove the foreign key constraint before dropping the column
            $table->dropForeign(['hair_stylist_request_id']);
            
            // Remove the columns if the migration is rolled back
            $table->dropColumn(['hair_stylist_request_id', 'deleted_at', 'image_path']);
            $table->dropTimestamps();
            $table->dropSoftDeletes();
            $table->dropColumn('id');
        });
    }
};
