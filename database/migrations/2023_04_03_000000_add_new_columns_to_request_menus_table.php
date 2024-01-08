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
            // Add new columns to the request_menus table
            $table->string('menu_name')->after('menu_id');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'))->after('menu_name');
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'))->after('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('request_menus', function (Blueprint $table) {
            // Remove the new columns if the migration is rolled back
            $table->dropColumn(['menu_name', 'created_at', 'updated_at']);
        });
    }
};
