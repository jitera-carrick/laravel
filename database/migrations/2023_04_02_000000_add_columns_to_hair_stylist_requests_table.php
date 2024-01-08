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
            // Add new columns for details and status
            $table->text('details')->nullable();
            $table->string('status')->default('pending');
            
            // Add a foreign key constraint to user_id referencing the id on the users table
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');
            
            // Add a foreign key constraint to request_image_id referencing the id on the request_images table
            $table->unsignedBigInteger('request_image_id');
            $table->foreign('request_image_id')->references('id')->on('request_images');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hair_stylist_requests', function (Blueprint $table) {
            // Remove the foreign key constraints before dropping the columns
            $table->dropForeign(['user_id']);
            $table->dropForeign(['request_image_id']);
            
            // Remove the columns if the migration is rolled back
            $table->dropColumn(['details', 'status', 'user_id', 'request_image_id']);
        });
    }
};
