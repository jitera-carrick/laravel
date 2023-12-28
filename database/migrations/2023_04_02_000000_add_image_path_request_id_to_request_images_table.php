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
            // Add new columns for image_path and request_id
            $table->string('image_path');
            $table->unsignedBigInteger('request_id');
            
            // Add a foreign key constraint to request_id referencing the id on the requests table
            $table->foreign('request_id')->references('id')->on('requests');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('request_images', function (Blueprint $table) {
            // Remove the foreign key constraint before dropping the column
            $table->dropForeign(['request_id']);
            
            // Remove the columns if the migration is rolled back
            $table->dropColumn(['image_path', 'request_id']);
        });
    }
};
