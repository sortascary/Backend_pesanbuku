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
            'user' => new UserResource($this->user),
            'phone' => $this -> phone,
            'schoolName' => $this -> schoolName,
            'daerah' => $this -> daerah,
            'payment' => $this->payment,
            'status' => $this->status,
            'total_book_price' => $this->total_book_price,
            'unpaid_price' => $this->unpaid_price,
            'books' => OrderBookResource::collection($this->orderbook),
        ];
    }
}
