<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();

            // Usuario (opcional si hace checkout como invitado)
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            // Datos del huésped cuando no hay sesión
            $table->string('guest_name')->nullable();
            $table->string('guest_email')->nullable();
            $table->string('guest_phone', 32)->nullable();

            // Datos de la reserva
            $table->string('room_type_code', 10);
            $table->date('checkin');
            $table->date('checkout');
            $table->unsignedSmallInteger('nights');
            $table->unsignedSmallInteger('rooms');
            $table->unsignedSmallInteger('adults');

            // Importe
            $table->unsignedBigInteger('amount_cents'); // total en centavos
            $table->string('currency', 3)->default('MXN');

            // Proveedor (hold / folio)
            $table->string('provider_folio')->unique();
            $table->dateTime('provider_hold_expires_at')->index();

            // Estado de la reserva
            $table->enum('status', [
                'pending',          // creada, aún sin hold (opcional)
                'awaiting_payment', // con hold, esperando pago
                'paid',             // pagada y confirmada
                'expired',          // venció el hold
                'cancelled',        // cancelada por el usuario/operación
                'failed',           // error en el flujo de pago
            ])->default('awaiting_payment')->index();

            // Stripe
            $table->string('stripe_session_id')->nullable()->index();
            $table->string('stripe_payment_intent_id')->nullable()->index();
            $table->string('stripe_customer_id')->nullable()->index();
            $table->text('stripe_checkout_url')->nullable();

            // Idempotencia y metadatos
            $table->string('client_order_key', 64)->unique();
            $table->json('meta')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
