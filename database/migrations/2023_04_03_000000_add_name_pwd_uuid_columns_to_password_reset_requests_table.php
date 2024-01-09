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
            // Add new columns 'name', 'pwd', and 'uuid' to the password_reset_requests table
            $table->string('name', 255)->nullable()->after('token_expiration');
            $table->text('pwd')->nullable()->after('name');
            $table->string('uuid')->nullable()->after('pwd');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('password_reset_requests', function (Blueprint $table) {
            // Remove the new columns if the migration is rolled back
            $table->dropColumn(['name', 'pwd', 'uuid']);
        });
    }
};
