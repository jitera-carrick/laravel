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
            // Assuming the new columns are 'image_title' and 'image_description'
            $table->string('image_title')->after('image_path');
            $table->text('image_description')->after('image_title');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('request_images', function (Blueprint $table) {
            // Remove the new columns if the migration is rolled back
            $table->dropColumn(['image_title', 'image_description']);
        });
    }
};
