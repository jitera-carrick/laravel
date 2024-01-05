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
        Schema::table('email_logs', function (Blueprint $table) {
            // Add new columns to the email_logs table
            $table->string('email_type')->after('updated_at');
            $table->timestamp('sent_at')->nullable()->after('email_type');
            $table->unsignedBigInteger('user_id')->after('sent_at');
            
            // Assuming 'users' table has 'id' as the primary key
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('email_logs', function (Blueprint $table) {
            // Remove the foreign key constraint before dropping the column
            $table->dropForeign(['user_id']);
            
            // Remove the new columns if the migration is rolled back
            $table->dropColumn(['email_type', 'sent_at', 'user_id']);
        });
    }
};
