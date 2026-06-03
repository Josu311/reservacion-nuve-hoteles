<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CuponCode extends Model
{
    protected $table = 'cupon_codes';

    protected $fillable = [
        'code',
        'discount_type',
        'discount_value',
        'usage_limit',
        'times_used',
        'status',
        'starts_at',
        'expires_at',
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'usage_limit' => 'integer',
        'times_used' => 'integer',
        'status' => 'boolean',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
    ];
}
