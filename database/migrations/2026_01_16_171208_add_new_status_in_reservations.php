<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            // Debes redefinir la lista completa incluyendo el nuevo valor
            $table->enum('status', [
                'pending',
                'awaiting_payment',
                'paid',
                'expired',
                'cancelled',
                'failed',
                'booking_in_reception',
            ])->default('awaiting_payment')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('reservations')
            ->where('status', 'booking_in_reception')
            ->update(['status' => 'pending']);

        Schema::table('reservations', function (Blueprint $table) {
            // Volvemos a la lista original eliminando 'refunded'
            $table->enum('status', [
                'pending',
                'awaiting_payment',
                'paid',
                'expired',
                'cancelled',
                'failed',
            ])->default('awaiting_payment')->change();
        });
    }
};
