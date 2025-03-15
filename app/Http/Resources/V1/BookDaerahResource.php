<?php

namespace App\Http\Resources\V1;

use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookDaerahResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => Book::where('id', $this->book_id)->value('name') ?? 'unknown',
            'daerah' => $this->daerah,
            'price' => $this->price,
        ];
    }
}
