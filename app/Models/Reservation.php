<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;

    public const CONFIRMATION_PENDING = 0;
    public const CONFIRMATION_CONFIRMED = 1;
    public const CONFIRMATION_CANCELLED = 2;

    protected $fillable = [
        'user_id',
        'hotel_code',
        'guest_name',
        'guest_email',
        'guest_phone',
        'origin_page',

        'room_type_code',
        'checkin',
        'checkout',
        'nights',
        'rooms',
        'adults',

        'amount_cents',
        'currency',

        'provider_folio',
        'provider_hold_expires_at',

        'status',
        'description',
        'is_confirmed',

        'stripe_session_id',
        'stripe_payment_intent_id',
        'stripe_customer_id',
        'stripe_checkout_url',

        'client_order_key',
        'meta',
    ];

    protected $casts = [
        'checkin'                   => 'date',
        'checkout'                  => 'date',
        'provider_hold_expires_at'  => 'datetime',
        'meta'                      => 'array',
        'amount_cents'              => 'integer',
        'nights'                    => 'integer',
        'rooms'                     => 'integer',
        'adults'                    => 'integer',
    ];

    // Relación con usuario (si existe)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Helper: ¿venció el hold del proveedor?
    public function isHoldExpired(): bool
    {
        return $this->provider_hold_expires_at
            ? now()->greaterThan($this->provider_hold_expires_at)
            : false;
    }

    // Alcance útil para activas
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['awaiting_payment', 'paid']);
    }
}
