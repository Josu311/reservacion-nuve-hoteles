<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GlobalPromotion extends Model
{
    protected $fillable = [
        'name',
        'description',
        'status',
        'discount_type',
        'discount_value',
        'booking_starts_at',
        'booking_ends_at',
        'stay_starts_at',
        'stay_ends_at',
        'hotel_code',
        'room_type_code',
        'priority',
    ];

    protected $casts = [
        'status' => 'boolean',
        'discount_value' => 'decimal:2',
        'booking_starts_at' => 'datetime',
        'booking_ends_at' => 'datetime',
        'stay_starts_at' => 'date',
        'stay_ends_at' => 'date',
        'priority' => 'integer',
    ];
}
