<?php

namespace App\Models;

use App\Models\Book; 
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BookClass extends Model
{
    use HasFactory;

    protected $fillable = [
        'book_id',
        'stock',
        'class'
    ];

    public function book(): BelongsTo
    {
        return $this->BelongsTo(Book::class, 'book_id', 'id');
    }
}
