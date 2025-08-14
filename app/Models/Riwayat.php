<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Riwayat extends Model
{
    protected $table = 'riwayat';

    protected $fillable = [
        'order_id',
        'paid_amount',
        'paid_at',
    ];
}
