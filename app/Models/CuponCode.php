<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CuponCode extends Model
{
    protected $table = 'cupon_codes';

    protected $fillable = [
        'code',
        'discount_value',
        'discount_percentage',
        'usage_limit',
        'times_used',
        'status',
        'starts_at',
        'expires_at',
    ];
}
