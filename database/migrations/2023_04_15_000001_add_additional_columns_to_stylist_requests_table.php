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
        Schema::table('stylist_requests', function (Blueprint $table) {
            // Add new columns based on the "# TABLE" information
            if (!Schema::hasColumn('stylist_requests', 'area')) {
                $table->string('area')->nullable()->after('details');
            }
            if (!Schema::hasColumn('stylist_requests', 'gender')) {
                $table->string('gender')->nullable()->after('area');
            }
            if (!Schema::hasColumn('stylist_requests', 'birth_date')) {
                $table->date('birth_date')->nullable()->after('gender');
            }
            if (!Schema::hasColumn('stylist_requests', 'display_name')) {
                $table->string('display_name')->nullable()->after('birth_date');
            }
            if (!Schema::hasColumn('stylist_requests', 'menu')) {
                $table->text('menu')->nullable()->after('display_name');
            }
            if (!Schema::hasColumn('stylist_requests', 'hair_concerns')) {
                $table->text('hair_concerns')->nullable()->after('menu');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stylist_requests', function (Blueprint $table) {
            // Remove the columns in the reverse order that they were added
            $table->dropColumn('hair_concerns');
            $table->dropColumn('menu');
            $table->dropColumn('display_name');
            $table->dropColumn('birth_date');
            $table->dropColumn('gender');
            $table->dropColumn('area');
        });
    }
};
