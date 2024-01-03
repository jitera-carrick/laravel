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
        Schema::table('email_verification_tokens', function (Blueprint $table) {
            // Add new columns to the email_verification_tokens table
            $table->id();
            $table->timestamps();
            $table->string('token');
            $table->dateTime('expires_at');
            $table->boolean('used')->default(false);
            $table->unsignedBigInteger('user_id');

            // Define the foreign key relationship with the users table
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('email_verification_tokens', function (Blueprint $table) {
            // Drop the foreign key constraint before dropping the column
            $table->dropForeign(['user_id']);

            // Remove the columns added in the up() method
            $table->dropColumn('user_id');
            $table->dropColumn('used');
            $table->dropColumn('expires_at');
            $table->dropColumn('token');
            $table->dropTimestamps();
            $table->dropColumn('id');
        });
    }
};
