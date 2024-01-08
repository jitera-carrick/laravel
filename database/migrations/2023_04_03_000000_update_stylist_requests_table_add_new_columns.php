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
            // Add new columns or modify existing ones here
            // For example, if you need to add a 'created_at' timestamp column:
            // $table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stylist_requests', function (Blueprint $table) {
            // Remove the newly added columns or revert changes here
            // For example, if you added a 'created_at' column in the up() method:
            // $table->dropColumn('created_at');
        });
    }
};
