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
        Schema::create('email_verification_tokens', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('token');
            // Combine the nullable expires_at from the new code with the existing dateTime type
            $table->dateTime('expires_at')->nullable();
            // Add the verified column from the new code
            $table->boolean('verified')->default(false);
            $table->unsignedBigInteger('user_id');

            // Add a foreign key constraint to user_id referencing the id on the users table
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('email_verification_tokens', function (Blueprint $table) {
            // Remove the foreign key constraint before dropping the table
            $table->dropForeign(['user_id']);
        });

        Schema::dropIfExists('email_verification_tokens');
    }
};
