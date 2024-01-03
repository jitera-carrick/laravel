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
            // Add new columns for display_name, gender, date_of_birth, and request_id
            $table->string('display_name')->nullable()->after('updated_at');
            $table->string('gender', 10)->nullable()->after('display_name');
            $table->date('date_of_birth')->nullable()->after('gender');
            $table->unsignedBigInteger('request_id')->nullable()->after('date_of_birth');

            // Add a foreign key constraint to request_id referencing the id on the requests table
            $table->foreign('request_id')->references('id')->on('requests');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Remove the foreign key constraint before dropping the column
            $table->dropForeign(['request_id']);
            
            // Remove the columns if the migration is rolled back
            $table->dropColumn(['display_name', 'gender', 'date_of_birth', 'request_id']);
        });
    }
};
