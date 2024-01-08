
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Assuming the new columns to be added are 'details' and 'priority'
// Replace 'details' and 'priority' with the actual names of your new columns
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('requests', function (Blueprint $table) {
            // Add new columns to the requests table
            $table->text('details')->after('user_id'); // Add 'details' column after 'user_id'
            $table->string('priority')->after('status'); // Add 'priority' column after 'status'
            // The foreign key constraint for 'assigned_to' has been removed as per the new requirements
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('requests', function (Blueprint $table) {
            // The foreign key constraint removal for 'assigned_to' has been removed as per the new requirements

            // Remove the columns if the migration is rolled back
            $table->dropColumn(['details', 'priority']);
        });
    }
};
