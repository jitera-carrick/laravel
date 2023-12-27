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
        Schema::create('password_policies', function (Blueprint $table) {
            $table->id();
            $table->integer('minimum_length');
            $table->boolean('require_digits');
            $table->boolean('require_letters');
            $table->boolean('require_special_characters');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('password_policies');
    }
};
