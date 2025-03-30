<?php

namespace App\Http\Resources\V1;

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

        $classes = $this->book->bookclass->pluck('class')->sort()->unique();

        return [
            'id' => $this->id,
            'book_id' => $this->book->id,   
            'name' => $this->book->name,
            'daerah' => $this->daerah,
            'price' => $this->price,
            'classes' => $classes->values(),
            'class' => $classes->isNotEmpty() ? $classes->first() . '-' . $classes->last() : null
        ];
    }
}
