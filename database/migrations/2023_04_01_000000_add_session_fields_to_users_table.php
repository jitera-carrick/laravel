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
        Schema::table('users', function (Blueprint $table) {
            $table->string('password_hash')->after('password');
            $table->string('session_token')->nullable()->after('password_hash');
            $table->timestamp('session_expires')->nullable()->after('session_token');
            $table->boolean('keep_session')->default(false)->after('session_expires');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['password_hash', 'session_token', 'session_expires', 'keep_session']);
        });
    }
};
