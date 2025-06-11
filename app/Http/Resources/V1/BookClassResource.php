<?php

namespace App\Http\Resources\V1;

use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookClassResource extends JsonResource
{

    public function toArray(Request $request): array
    
    {
        return [
            'id' => $this->id,
            'book_id' =>$this->book->id,
            'name' =>$this->book->name,
            'class' => $this->class,
            'stock' => $this->stock,
        ];
    }
}
