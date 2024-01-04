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
            // Assuming the 'id', 'created_at', 'updated_at', 'token', 'expires_at', 'used', and 'user_id' columns already exist
            // and we are adding new columns or modifying existing ones as per the new requirements.

            // Add or modify columns as per the new requirements here
            // For example, if we need to add a new column 'email' we would do:
            // $table->string('email')->after('id');

            // If we need to modify an existing column, for example changing 'used' column to a different type:
            // $table->boolean('used')->change();

            // If we need to add a foreign key constraint to 'user_id':
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('email_verification_tokens', function (Blueprint $table) {
            // If we added a new column 'email', we would drop it here:
            // $table->dropColumn('email');

            // If we modified an existing column, we would revert it back to its original state:
            // $table->integer('used')->change();

            // Remove the foreign key constraint for 'user_id' before dropping the column
            $table->dropForeign(['user_id']);

            // If we need to drop the 'user_id' column:
            // $table->dropColumn('user_id');
        });
    }
};
