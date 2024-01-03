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
            // Since the columns are already added in the previous migration file,
            // we do not need to add them again. This section should be used to add
            // any new columns that are not already present in the table.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('requests', function (Blueprint $table) {
            // Since the columns are already removed in the previous migration file,
            // we do not need to remove them again. This section should be used to remove
            // any new columns that are not already present in the table.
        });
    }
};
