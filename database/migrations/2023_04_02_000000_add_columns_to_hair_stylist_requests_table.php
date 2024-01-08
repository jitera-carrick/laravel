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
            // Add new columns for details and status from existing code
            if (!Schema::hasColumn('hair_stylist_requests', 'details')) {
                $table->text('details')->nullable();
            }
            if (!Schema::hasColumn('hair_stylist_requests', 'status')) {
                $table->string('status')->default('pending');
            }
            
            // Add a foreign key constraint to user_id referencing the id on the users table from existing code
            if (!Schema::hasColumn('hair_stylist_requests', 'user_id')) {
                $table->unsignedBigInteger('user_id');
                $table->foreign('user_id')->references('id')->on('users');
            }
            
            // Add a foreign key constraint to request_image_id referencing the id on the request_images table from existing code
            if (!Schema::hasColumn('hair_stylist_requests', 'request_image_id')) {
                $table->unsignedBigInteger('request_image_id');
                $table->foreign('request_image_id')->references('id')->on('request_images');
            }

            // Add new column 'service_id' from new code
            if (!Schema::hasColumn('hair_stylist_requests', 'service_id')) {
                $table->unsignedBigInteger('service_id')->after('user_id');
                $table->foreign('service_id')->references('id')->on('services');
            }

            // Add a new index for the 'status' column from new code
            if (!Schema::hasIndex('hair_stylist_requests', 'hair_stylist_requests_status_index')) {
                $table->index('status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hair_stylist_requests', function (Blueprint $table) {
            // Remove the foreign key constraints before dropping the columns from existing code
            if (Schema::hasColumn('hair_stylist_requests', 'user_id')) {
                $table->dropForeign(['user_id']);
            }
            if (Schema::hasColumn('hair_stylist_requests', 'request_image_id')) {
                $table->dropForeign(['request_image_id']);
            }
            
            // Remove the columns if the migration is rolled back from existing code
            if (Schema::hasColumn('hair_stylist_requests', 'details')) {
                $table->dropColumn('details');
            }
            if (Schema::hasColumn('hair_stylist_requests', 'status')) {
                $table->dropColumn('status');
            }
            if (Schema::hasColumn('hair_stylist_requests', 'user_id')) {
                $table->dropColumn('user_id');
            }
            if (Schema::hasColumn('hair_stylist_requests', 'request_image_id')) {
                $table->dropColumn('request_image_id');
            }

            // Remove the foreign key constraint before dropping the column from new code
            if (Schema::hasColumn('hair_stylist_requests', 'service_id')) {
                $table->dropForeign(['service_id']);
                $table->dropColumn('service_id');
            }

            // Remove the index for the 'status' column from new code
            if (Schema::hasIndex('hair_stylist_requests', 'hair_stylist_requests_status_index')) {
                $table->dropIndex(['status']);
            }
        });
    }
};
