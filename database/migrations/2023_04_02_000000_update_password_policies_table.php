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
        Schema::table('password_policies', function (Blueprint $table) {
            // Add new columns for password policies
            $table->integer('minimum_length')->default(8);
            $table->boolean('require_uppercase')->default(false);
            $table->boolean('require_lowercase')->default(false);
            $table->boolean('require_numbers')->default(false);
            $table->boolean('require_special_characters')->default(false);
            $table->boolean('exclude_user_info')->default(false);
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
        Schema::table('password_policies', function (Blueprint $table) {
            // Remove the foreign key constraint before dropping the column
            $table->dropForeign(['user_id']);
            
            // Remove the columns if the migration is rolled back
            $table->dropColumn([
                'minimum_length',
                'require_uppercase',
                'require_lowercase',
                'require_numbers',
                'require_special_characters',
                'exclude_user_info',
                'user_id'
            ]);
        });
    }
};
