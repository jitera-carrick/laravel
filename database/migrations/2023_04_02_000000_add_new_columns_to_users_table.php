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
            // Add new columns to the users table
            $table->string('phone')->nullable()->after('password_hash');
            $table->string('address')->nullable()->after('phone');
            $table->boolean('is_active')->default(true)->after('address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Remove the new columns if the migration is rolled back
            $table->dropColumn(['phone', 'address', 'is_active']);
        });
    }
};
