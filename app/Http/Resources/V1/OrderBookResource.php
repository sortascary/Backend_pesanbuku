<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderBookResource extends JsonResource
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
            'book' => new BookClassResource($this->bookclass) ?? null,
            'name' => $this->name,
            'bought_price' => $this->bought_price,
            'amount' => $this->amount,
            'subtotal' => $this->subtotal,
            'isDone' => $this->isDone,
        ];
    }
}
