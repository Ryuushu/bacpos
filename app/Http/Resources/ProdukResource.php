<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProdukResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'kode_produk' => $this->kode_produk,
            'nama_produk' => $this->nama_produk,
            'kode_kategori' => $this->kode_kategori,
            'harga' => $this->harga,
            'stok' => $this->stok,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
