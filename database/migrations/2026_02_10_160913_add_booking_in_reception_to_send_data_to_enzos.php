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
            $table->integer('pending_reservations_count')->after('reservations_mount');
            $table->decimal('pending_reservations_mount')->after('pending_reservations_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('send_data_to_enzos', function (Blueprint $table) {
            $table->dropColumn(['pending_reservations_count', 'pending_reservations_mount']);
        });
    }
};
