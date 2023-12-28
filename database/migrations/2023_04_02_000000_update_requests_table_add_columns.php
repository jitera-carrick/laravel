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
        Schema::table('requests', function (Blueprint $table) {
            // Add new columns to the requests table
            $table->string('area')->after('updated_at');
            $table->string('menu')->after('area');
            $table->json('hair_concerns')->nullable()->after('menu');
            $table->string('status')->after('hair_concerns');
            $table->unsignedBigInteger('user_id')->after('status');

            // Define the foreign key constraint for user_id
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('requests', function (Blueprint $table) {
            // Remove the foreign key constraint before dropping the column
            $table->dropForeign(['user_id']);

            // Remove the columns if the migration is rolled back
            $table->dropColumn(['area', 'menu', 'hair_concerns', 'status', 'user_id']);
        });
    }
};
