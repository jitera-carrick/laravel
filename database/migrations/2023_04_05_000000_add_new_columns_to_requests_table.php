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
            // Since the columns 'area', 'menu', 'hair_concerns', 'status', 'user_id' are already present in the requests table,
            // we do not need to add them again. We will only define the new relationships here.

            // Define the relationships with request_images, request_areas, and request_menus
            // These are one-to-many relationships, so we don't need to define them in the requests table
            // They will be defined in the respective tables (request_images, request_areas, request_menus)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('requests', function (Blueprint $table) {
            // No columns to remove since we didn't add any new columns in the up() method.
            // We only defined relationships which are not represented directly in the database schema.
        });
    }
};
