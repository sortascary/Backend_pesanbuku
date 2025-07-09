<?php

namespace App\Models;

use App\Models\User;
use App\Models\OrderBook;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($order) {
            $date = now()->format('dmY'); // Format: YYYYMMDD
            $latestOrder = DB::table('orders')
                ->where('id', 'LIKE', "ORDER-$date-%")
                ->orderBy('id', 'desc')
                ->first();
            
            $nextId = $latestOrder ? ((int) substr($latestOrder->id, -5)) + 1 : 1;
            $order->id = "ORDER-$date-" . str_pad($nextId, 5, '0', STR_PAD_LEFT);
        });
    }

    protected $fillable = [
        'user_id',
        'phone', 
        'schoolName', 
        'daerah', 
        'payment',
        'status',
        'total_book_price',
        'done_at',
    ];

    public function user(): BelongsTo
    {
        return $this->BelongsTo(User::class, 'user_id', 'id');
    }

    public function orderbook(): HasMany
    {
        return $this->HasMany(OrderBook::class, 'order_id', 'id');
    }
}
