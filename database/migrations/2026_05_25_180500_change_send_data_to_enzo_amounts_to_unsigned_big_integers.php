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
        Schema::table('send_data_to_enzos', function (Blueprint $table) {
            $table->unsignedBigInteger('reservations_mount')->change();
            $table->unsignedBigInteger('pending_reservations_mount')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('send_data_to_enzos', function (Blueprint $table) {
            $table->decimal('reservations_mount', 8, 2)->change();
            $table->decimal('pending_reservations_mount', 8, 2)->change();
        });
    }
};
