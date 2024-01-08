<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Replace YYYY_MM_DD_HHMMSS with the current date and time in the format Year_Month_Day_HourMinuteSecond
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Add new column for request_id
            $table->unsignedBigInteger('request_id')->nullable()->after('hair_stylist_request_id');

            // Add foreign key constraint
            $table->foreign('request_id')->references('id')->on('requests');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Remove the foreign key constraint before dropping the column
            $table->dropForeign(['request_id']);

            // Remove the column if the migration is rolled back
            $table->dropColumn(['request_id']);
        });
    }
};
