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
            // Adding new columns for password hash and salt as per the guideline
            $table->string('password_hash')->after('password')->nullable();
            $table->string('password_salt')->after('password_hash')->nullable();
            // Additional columns can be added here as per the registration requirements
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Removing the columns added in the up() method
            $table->dropColumn(['password_hash', 'password_salt']);
            // If other columns were added, they should be removed here as well
        });
    }
};
