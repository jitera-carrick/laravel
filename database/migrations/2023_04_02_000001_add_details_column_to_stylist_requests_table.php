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
            $table->text('details')->nullable()->after('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stylist_requests', function (Blueprint $table) {
            // Remove the 'details' column if the migration is rolled back
            $table->dropColumn('details');
        });
    }
};
