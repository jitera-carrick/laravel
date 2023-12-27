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
            // Adding new columns
            $table->string('password_hash')->after('password');
            $table->string('session_token')->nullable()->after('password_hash');
            $table->dateTime('session_expiration')->nullable()->after('session_token');
            $table->string('role')->default('user')->after('session_expiration');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Dropping the columns in the reverse order they were added
            $table->dropColumn('role');
            $table->dropColumn('session_expiration');
            $table->dropColumn('session_token');
            $table->dropColumn('password_hash');
        });
    }
};
