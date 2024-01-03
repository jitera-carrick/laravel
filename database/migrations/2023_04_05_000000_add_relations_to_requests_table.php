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
            // Add new columns for relationships
            $table->unsignedBigInteger('request_area_id')->after('user_id');
            $table->unsignedBigInteger('request_menu_id')->after('request_area_id');
            $table->unsignedBigInteger('request_image_id')->after('request_menu_id');

            // Define the foreign key constraints
            $table->foreign('request_area_id')->references('id')->on('request_areas');
            $table->foreign('request_menu_id')->references('id')->on('request_menus');
            $table->foreign('request_image_id')->references('id')->on('request_images');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('requests', function (Blueprint $table) {
            // Remove the foreign key constraints before dropping the columns
            $table->dropForeign(['request_area_id']);
            $table->dropForeign(['request_menu_id']);
            $table->dropForeign(['request_image_id']);

            // Remove the columns if the migration is rolled back
            $table->dropColumn(['request_area_id', 'request_menu_id', 'request_image_id']);
        });
    }
};
