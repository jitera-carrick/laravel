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
        Schema::table('stylist_requests', function (Blueprint $table) {
            // Add 'details' column of type text
            $table->text('details')->after('user_id')->nullable();

            // Add 'request_id' column of type unsignedBigInteger
            $table->unsignedBigInteger('request_id')->after('details');

            // Since there is a relation with requests, we assume 'request_id' is a foreign key
            $table->foreign('request_id')->references('id')->on('requests')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stylist_requests', function (Blueprint $table) {
            // Remove the foreign key constraint before dropping the 'request_id' column
            $table->dropForeign(['request_id']);

            // Remove the new columns if the migration is rolled back
            $table->dropColumn(['details', 'request_id']);
        });
    }
};
