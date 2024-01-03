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
        Schema::table('password_reset_requests', function (Blueprint $table) {
            // Add new columns 'reset_token' and 'expiration'
            $table->string('reset_token')->nullable()->after('user_id');
            $table->timestamp('expiration')->nullable()->after('reset_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('password_reset_requests', function (Blueprint $table) {
            // Remove the columns if the migration is rolled back
            $table->dropColumn(['reset_token', 'expiration']);
        });
    }
};
