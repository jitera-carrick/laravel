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
        Schema::table('users', function (Blueprint $table) {
            // Add new columns for stylist_request_id and hair_stylist_request_id
            $table->unsignedBigInteger('stylist_request_id')->nullable()->after('session_expiration');
            $table->unsignedBigInteger('hair_stylist_request_id')->nullable()->after('stylist_request_id');

            // Add foreign key constraints
            $table->foreign('stylist_request_id')->references('id')->on('stylist_requests');
            $table->foreign('hair_stylist_request_id')->references('id')->on('hair_stylist_requests');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Remove the foreign key constraints before dropping the columns
            $table->dropForeign(['stylist_request_id']);
            $table->dropForeign(['hair_stylist_request_id']);

            // Remove the columns if the migration is rolled back
            $table->dropColumn(['stylist_request_id', 'hair_stylist_request_id']);
        });
    }
};
