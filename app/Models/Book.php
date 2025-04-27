<?php

namespace App\Models;

use App\Models\BookDaerah; 
use App\Models\BookClass;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\hasMany;

class Book extends Model
{
    use HasFactory;

    public function bookdaerah(): hasMany
    {
        return $this->hasMany(BookDaerah::class, 'book_id', 'id');
    }

    public function bookclass(): hasMany
    {
        return $this->hasMany(BookClass::class, 'book_id', 'id');
    }
}
