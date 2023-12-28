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
        Schema::create('requests', function (Blueprint $table) {
            $table->id();
            $table->string('area')->nullable(); // Assuming 'area' is a string and nullable as not enough info provided
            $table->string('menu')->nullable(); // Assuming 'menu' is a string and nullable as not enough info provided
            $table->json('hair_concerns')->nullable();
            $table->string('status');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();

            // Define the foreign key constraint for user_id
            $table->foreign('user_id')->references('id')->on('users');

            // Assuming that 'area' and 'menu' are related to other tables, but without specific info, we can't create foreign keys
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('requests');
    }
};
