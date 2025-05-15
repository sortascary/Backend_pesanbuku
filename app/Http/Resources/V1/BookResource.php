<?php

namespace App\Http\Resources\V1;

use App\Models\BookClass;
use App\Models\BookDaerah; 
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookResource extends JsonResource
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
            'name' => $this->name,
            'prices' => $this->bookdaerah->map(fn($item) => [
                'daerah' => $item->daerah,
                'price' => $item->price,
            ])->values()->toArray(),
            'total_stock' => $this->bookclass->sum('stock'),
            'classes' => BookClassResource::collection($this->bookclass),
        ];
    }
}
