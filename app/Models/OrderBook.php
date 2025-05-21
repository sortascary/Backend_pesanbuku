<?php

namespace App\Models;

use App\Models\BookClass;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrderBook extends Model
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;

    protected $fillable = [
        'order_id',
        'book_class_id',
        'isDone',
        'name',
        'amount',
        'bought_price',
        'subtotal'
    ];

    public function bookclass(): BelongsTo
    {
        return $this->BelongsTo(BookClass::class, 'book_class_id', 'id');
    }
}
