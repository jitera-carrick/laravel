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
            $table->string('token')->after('id');
            $table->timestamp('expires_at')->nullable()->after('token');
            $table->unsignedBigInteger('user_id')->after('expires_at')->index();
            $table->boolean('used')->default(false)->after('user_id');
            
            // Assuming 'users' table has 'id' as the primary key
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('email_verification_tokens', function (Blueprint $table) {
            // Remove the foreign key constraint and index before dropping the column
            $table->dropForeign(['user_id']);
            $table->dropIndex(['user_id']);
            
            // Remove the new columns if the migration is rolled back
            $table->dropColumn(['token', 'expires_at', 'user_id', 'used']);
        });
    }
};
