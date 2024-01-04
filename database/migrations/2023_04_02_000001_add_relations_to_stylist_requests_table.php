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
            // Assuming 'user_id' is an unsigned big integer as it's a foreign key.
            $table->unsignedBigInteger('user_id')->after('status');

            // Assuming 'users' table has 'id' as the primary key.
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stylist_requests', function (Blueprint $table) {
            // Drop the foreign key constraint before dropping the column
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};
