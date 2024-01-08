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
        Schema::table('stylists', function (Blueprint $table) {
            // Assuming stylist_requests table has a stylist_id column to form the relationship
            // No new columns are added to the stylists table in this migration
        });
        
        // Assuming stylist_requests table already exists, we add the foreign key here
        Schema::table('stylist_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('stylist_id')->after('id'); // Add stylist_id after the id column
            
            // Add a foreign key constraint to stylist_id referencing the id on the stylists table
            $table->foreign('stylist_id')->references('id')->on('stylists');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stylist_requests', function (Blueprint $table) {
            // Remove the foreign key constraint before dropping the column
            $table->dropForeign(['stylist_id']);
            
            // Remove the column if the migration is rolled back
            $table->dropColumn(['stylist_id']);
        });
        
        // No changes are made to the stylists table in the down method
    }
};
