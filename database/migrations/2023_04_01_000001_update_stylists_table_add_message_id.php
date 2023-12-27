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
            // Add message_id column and foreign key
            $table->unsignedBigInteger('message_id')->nullable()->after('user_id');
            $table->foreign('message_id')->references('id')->on('messages')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stylists', function (Blueprint $table) {
            // Drop the foreign key and column for message_id
            $table->dropForeign(['message_id']);
            $table->dropColumn('message_id');
        });
    }
};
