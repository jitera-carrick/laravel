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
            // Add new column for image_file
            $table->string('image_file')->after('request_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('request_images', function (Blueprint $table) {
            // Remove the column if the migration is rolled back
            $table->dropColumn(['image_file']);
        });
    }
};
