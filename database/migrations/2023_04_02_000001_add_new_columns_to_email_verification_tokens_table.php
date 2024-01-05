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
            // Add new columns as per the "# TABLE" information
            $table->id(); // Assuming 'id' is an auto-incrementing integer
            $table->timestamps(); // Assuming 'created_at' and 'updated_at' are timestamps
            $table->string('token'); // Assuming 'token' is a string
            $table->dateTime('expires_at'); // Assuming 'expires_at' is a datetime
            $table->boolean('verified'); // Assuming 'verified' is a boolean
            $table->unsignedBigInteger('user_id'); // Assuming 'user_id' is an unsigned big integer

            // Add foreign key constraint to 'user_id' referencing 'id' on 'users' table
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('email_verification_tokens', function (Blueprint $table) {
            // Drop foreign key constraint for 'user_id'
            $table->dropForeign(['user_id']);

            // Drop the columns added in the up() method
            $table->dropColumn('id');
            $table->dropColumn('created_at');
            $table->dropColumn('updated_at');
            $table->dropColumn('token');
            $table->dropColumn('expires_at');
            $table->dropColumn('verified');
            $table->dropColumn('user_id');
        });
    }
};
