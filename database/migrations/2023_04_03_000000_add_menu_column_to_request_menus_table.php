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
        Schema::table('request_menus', function (Blueprint $table) {
            // Add new column for menu
            $table->string('menu')->after('menu_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('request_menus', function (Blueprint $table) {
            // Remove the column if the migration is rolled back
            $table->dropColumn(['menu']);
        });
    }
};
