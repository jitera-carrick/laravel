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
            $table->string('area');
            $table->string('menu');
            $table->json('hair_concerns')->nullable();
            $table->string('status');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();

            // Define the foreign key constraint for user_id
            $table->foreign('user_id')->references('id')->on('users');

            // Define the relationships with request_images, request_areas, and request_menus
            // These are one-to-many relationships, so we don't need to define them in the requests table
            // They will be defined in the respective tables (request_images, request_areas, request_menus)
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
