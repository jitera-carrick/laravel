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
        Schema::table('password_reset_tokens', function (Blueprint $table) {
            // Add new columns
            $table->timestamp('expires_at')->nullable()->after('created_at');
            $table->boolean('used')->default(false)->after('expires_at');
            $table->foreignId('user_id')->constrained()->onDelete('cascade')->after('used');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('password_reset_tokens', function (Blueprint $table) {
            // Remove the columns in the reverse order that they were added
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
            $table->dropColumn('used');
            $table->dropColumn('expires_at');
        });
    }
};
