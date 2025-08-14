<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class TagihanResource extends JsonResource
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
            'user' => new UserResource($this->user),
            'phone' => $this -> phone,
            'schoolName' => $this -> schoolName,
            'daerah' => $this -> daerah,
            'payment' => $this->payment,
            'total_book_price' => $this->total_book_price,
            'unpaid_price' => $this->unpaid_price,
            'riwayat' => $this->riwayat
                ->sortByDesc('created_at')
                ->values()
                ->map(function ($item) {
                return [
                    'paid_amount' => $item->paid_amount,
                    'paid_at'     => $item->paid_at,
                ];
            }),
            'created_at' => $this -> created_at,
        ];
    }
}
