<?php

namespace App\Models;

use App\Models\Book; 
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookDaerah extends Model
{

    use HasFactory;

    protected $fillable = [
        'book_id',
        'price',
        'daerah'
    ];

    public function book(): BelongsTo
    {
        return $this->BelongsTo(Book::class, 'book_id', 'id');
    }
}
