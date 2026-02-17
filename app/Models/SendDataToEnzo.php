<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SendDataToEnzo extends Model
{
    protected $table = 'send_data_to_enzos';

    protected $fillable = [
        'reservations_count',
        'reservations_mount',
        'pending_reservations_count',
        'pending_reservations_mount',
        'response',
        'payload',
        'status',
        'post_id'
    ];
}
