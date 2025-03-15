<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Book extends Model
{
    use HasFactory;

    public function BookDaerah(): HasMany
    {
        return $this->HasMany(BookDaerah::class, 'bookID', 'id');
    }
}
