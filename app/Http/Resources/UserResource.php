<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id_user' => $this->id_user,
            'email' => $this->email,
            'role' => $this->role,
            'id_pemilik' => $this->id_pemilik,
            'id_pekerja' => $this->id_pekerja,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}