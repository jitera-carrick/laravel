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
            // Add 'details' column of type text after 'user_id' column
            $table->text('details')->after('user_id')->nullable();

            // Add 'status' column of type string after 'details' column
            $table->string('status')->after('details')->default('pending');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stylist_requests', function (Blueprint $table) {
            // Remove the 'details' and 'status' columns
            $table->dropColumn(['details', 'status']);
        });
    }
};
