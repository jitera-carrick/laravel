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
        Schema::table('password_resets', function (Blueprint $table) {
            // Check if the columns already exist before adding them
            if (!Schema::hasColumn('password_resets', 'id')) {
                $table->id();
            }
            if (!Schema::hasColumn('password_resets', 'created_at')) {
                $table->timestamp('created_at')->nullable();
            }
            if (!Schema::hasColumn('password_resets', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }
            if (!Schema::hasColumn('password_resets', 'email')) {
                $table->string('email');
            }
            if (!Schema::hasColumn('password_resets', 'token')) {
                $table->string('token');
            }
            // The user_id column and foreign key constraint are added in both new and existing code
            if (!Schema::hasColumn('password_resets', 'user_id')) {
                $table->unsignedBigInteger('user_id');
                $table->foreign('user_id')->references('id')->on('users');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('password_resets', function (Blueprint $table) {
            // Remove the foreign key constraint before dropping the column
            if (Schema::hasColumn('password_resets', 'user_id')) {
                $table->dropForeign(['user_id']);
            }

            // Remove the columns if the migration is rolled back
            $table->dropColumn(['email', 'token', 'user_id', 'created_at', 'updated_at']);
            if (Schema::hasColumn('password_resets', 'id')) {
                $table->dropColumn('id'); // id should be dropped separately as it's a primary key
            }
        });
    }
};
