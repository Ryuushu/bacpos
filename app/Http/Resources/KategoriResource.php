<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class KategoriResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'kode_kategori' => $this->kode_kategori,
            'nama_kategori' => $this->nama_kategori,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
