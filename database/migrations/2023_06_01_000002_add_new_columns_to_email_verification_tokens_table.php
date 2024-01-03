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
            // Assuming the columns are not defined in the "# TABLE" section, we will add them here.
            $table->id();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->string('token');
            $table->timestamp('expires_at')->nullable();
            $table->boolean('used')->default(false);
            $table->foreignId('user_id')->constrained('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('email_verification_tokens', function (Blueprint $table) {
            // To reverse the migration, we drop the columns and the foreign key constraint.
            $table->dropConstrainedForeignId('user_id');
            $table->dropColumn(['id', 'created_at', 'updated_at', 'token', 'expires_at', 'used']);
        });
    }
};
