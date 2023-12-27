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
        // Add foreign key for 'users' table referencing 'messages'
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('message_id')->nullable()->after('is_logged_in')->constrained('messages')->onDelete('set null');
        });

        // Add foreign key for 'stylists' table referencing 'messages'
        Schema::table('stylists', function (Blueprint $table) {
            $table->foreignId('message_id')->nullable()->after('user_id')->constrained('messages')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove foreign key for 'users' table referencing 'messages'
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('message_id');
        });

        // Remove foreign key for 'stylists' table referencing 'messages'
        Schema::table('stylists', function (Blueprint $table) {
            $table->dropConstrainedForeignId('message_id');
        });
    }
};
