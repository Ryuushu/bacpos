<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PekerjaResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id_pekerja' => $this->id_pekerja,
            'id_toko' => $this->id_toko,
            'nama_pekerja' => $this->nama_pekerja,
            'alamat_pekerja' => $this->alamat_pekerja,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
