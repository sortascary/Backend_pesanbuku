<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**8
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'books' => OrderBookResource::collection($this->orderbook),
            'user' => new UserResource($this->user),
            'total_book_price' => $this->total_book_price,
            'payment' => $this->payment,
            'isPayed' => $this->isPayed,
            'status' => $this->status,
        ];
    }
}
