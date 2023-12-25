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
        Schema::table('images', function (Blueprint $table) {
            // Add stylist_request_id column and foreign key if they don't exist
            if (!Schema::hasColumn('images', 'stylist_request_id')) {
                $table->unsignedBigInteger('stylist_request_id')->after('file_path');
                $table->foreign('stylist_request_id')->references('id')->on('stylist_requests');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('images', function (Blueprint $table) {
            // Drop foreign key and column
            $table->dropForeign(['stylist_request_id']);
            $table->dropColumn('stylist_request_id');
        });
    }
};
