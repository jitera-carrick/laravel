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
            // Assuming the "# TABLE" information provided is to add new columns to the email_verification_tokens table
            // The specific columns and their types are not provided in the "# TABLE" information.
            // The following is a placeholder example and should be replaced with actual column details.
            $table->string('token')->after('id');
            $table->timestamp('expires_at')->nullable()->after('token');
            $table->boolean('used')->default(false)->after('expires_at');
            $table->unsignedBigInteger('user_id')->after('used');
            
            // Add the foreign key constraint to the user_id column
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('email_verification_tokens', function (Blueprint $table) {
            // Remove the foreign key constraint before dropping the column
            $table->dropForeign(['user_id']);
            
            // Remove the new columns if the migration is rolled back
            $table->dropColumn('token');
            $table->dropColumn('expires_at');
            $table->dropColumn('used');
            $table->dropColumn('user_id');
        });
    }
};
