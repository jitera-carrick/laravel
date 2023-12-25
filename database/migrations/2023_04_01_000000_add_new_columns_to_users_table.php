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
        Schema::table('users', function (Blueprint $table) {
            // Add new columns
            $table->string('password_hash')->after('password');
            $table->string('session_token')->nullable()->after('password_hash');
            $table->dateTime('session_expiration')->nullable()->after('session_token');
            $table->boolean('is_stylist')->default(false)->after('session_expiration');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Remove the columns in the reverse order that they were added
            $table->dropColumn('is_stylist');
            $table->dropColumn('session_expiration');
            $table->dropColumn('session_token');
            $table->dropColumn('password_hash');
        });
    }
};
