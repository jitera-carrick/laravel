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
        Schema::table('request_menu_selections', function (Blueprint $table) {
            // Add new columns for request_id and menu_id
            $table->unsignedBigInteger('request_id');
            $table->unsignedBigInteger('menu_id');
            
            // Add foreign key constraints
            $table->foreign('request_id')->references('id')->on('requests');
            $table->foreign('menu_id')->references('id')->on('menus');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('request_menu_selections', function (Blueprint $table) {
            // Remove the foreign key constraints before dropping the columns
            $table->dropForeign(['request_id']);
            $table->dropForeign(['menu_id']);
            
            // Remove the columns if the migration is rolled back
            $table->dropColumn(['request_id', 'menu_id']);
        });
    }
};
