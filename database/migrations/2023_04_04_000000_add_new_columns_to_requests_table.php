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
        Schema::table('requests', function (Blueprint $table) {
            // Add new columns to the requests table
            $table->string('new_column_1')->after('updated_at');
            $table->string('new_column_2')->after('new_column_1');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('requests', function (Blueprint $table) {
            // Remove the columns if the migration is rolled back
            $table->dropColumn(['new_column_1', 'new_column_2']);
        });
    }
};
