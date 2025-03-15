<?php

namespace App\Models;

use App\Models\BookClass;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\hasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrderBook extends Model
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;

    protected $fillable = [
        'book_class_id',
        'isDone',
        'ammount'
    ];

    public function bookclass(): hasMany
    {
        return $this->hasMany(BookClass::class, 'id', 'book_class_id');
    }
}
