<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderBook extends Model
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'payment',
        'isDone',
        'ammount'
    ];
}
