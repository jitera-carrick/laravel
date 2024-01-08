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
        Schema::table('hair_stylist_requests', function (Blueprint $table) {
            // Assuming the columns are of type string and integer for user_id
            $table->string('requested_date')->nullable();
            $table->string('service_type')->nullable();
            $table->string('status')->nullable();
            $table->text('additional_notes')->nullable();
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
        Schema::table('hair_stylist_requests', function (Blueprint $table) {
            // Remove the foreign key constraint before dropping the column
            $table->dropForeign(['user_id']);
            
            // Remove the columns if the migration is rolled back
            $table->dropColumn(['requested_date', 'service_type', 'status', 'additional_notes', 'user_id']);
        });
    }
};
