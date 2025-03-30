<?php

namespace App\Http\Resources\V1;

use App\Models\BookClass;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookOrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'book_id' => $this->book->id,   
            'name' => $this->book->name,
            'daerah' => $this->daerah,
            'price' => $this->price,
            'classes' =>BookClassResource::collection( $this->book->bookclass),
        ];
    }
}
