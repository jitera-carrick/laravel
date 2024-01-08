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
            // Add new columns for user_id and stylist_id
            $table->unsignedBigInteger('user_id')->after('status');
            $table->unsignedBigInteger('stylist_id')->after('user_id');
            
            // Add foreign key constraints
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('stylist_id')->references('id')->on('stylists');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stylist_requests', function (Blueprint $table) {
            // Remove the foreign key constraints before dropping the columns
            $table->dropForeign(['user_id']);
            $table->dropForeign(['stylist_id']);
            
            // Remove the columns if the migration is rolled back
            $table->dropColumn(['user_id', 'stylist_id']);
        });
    }
};
