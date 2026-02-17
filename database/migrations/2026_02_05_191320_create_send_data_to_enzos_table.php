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
        Schema::create('send_data_to_enzos', function (Blueprint $table) {
            $table->id();
            $table->integer('reservations_count');
            $table->decimal('reservations_mount');
            $table->json('response')->nullable();
            $table->json('payload');
            $table->integer('status')->nullable();
            $table->integer('post_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('send_data_to_enzos');
    }
};
