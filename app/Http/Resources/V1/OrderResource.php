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
            'schoolName' => $this -> schoolName,
            'phone' => $this -> phone,
            'daerah' => $this -> daerah,
            'total_book_price' => $this->total_book_price,
            'payment' => $this->payment,
            'status' => $this->status,
        ];
    }
}
