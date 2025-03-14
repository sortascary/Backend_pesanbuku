<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'daerah' => $this->daerah,
            'schoolName' => $this->schoolName,
            'role' => $this->role,
            'phone' => $this->phone,
            'rememberToken' => $this->remember_token
        ];
    }
}
