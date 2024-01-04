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
            // Assuming new columns need to be added, for example 'new_column_1' and 'new_column_2'
            $table->string('new_column_1')->nullable()->after('updated_at');
            $table->integer('new_column_2')->default(0)->after('new_column_1');
            
            // If there are any new foreign keys, for example 'new_foreign_id' referencing 'id' on 'new_foreign_table'
            // $table->unsignedBigInteger('new_foreign_id')->after('new_column_2');
            // $table->foreign('new_foreign_id')->references('id')->on('new_foreign_table');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('requests', function (Blueprint $table) {
            // If there are any foreign keys added, drop them first
            // $table->dropForeign(['new_foreign_id']);
            
            // Drop the new columns if the migration is rolled back
            $table->dropColumn(['new_column_1', 'new_column_2']);
        });
    }
};
