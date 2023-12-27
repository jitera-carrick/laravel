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
        // Add foreign key for 'stylists' table
        Schema::table('stylists', function (Blueprint $table) {
            $table->foreignId('user_id')->after('id')->constrained('users')->onDelete('cascade');
        });

        // Add foreign key for 'messages' table
        Schema::table('messages', function (Blueprint $table) {
            $table->foreignId('user_id')->after('id')->constrained('users')->onDelete('cascade');
        });

        // Add foreign key for 'treatment_plans' table
        Schema::table('treatment_plans', function (Blueprint $table) {
            $table->foreignId('user_id')->after('id')->constrained('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove foreign key for 'stylists' table
        Schema::table('stylists', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
        });

        // Remove foreign key for 'messages' table
        Schema::table('messages', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
        });

        // Remove foreign key for 'treatment_plans' table
        Schema::table('treatment_plans', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
        });
    }
};
