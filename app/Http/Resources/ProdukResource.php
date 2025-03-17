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
            'harga' => $this->harga,
            'harga_beli' => $this->harga_beli,
            'stok' => $this->stok,
            'url_img' => $this->url_img ? url($this->url_img) : null,
            'is_stock_managed' => $this->is_stock_managed,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'kategori' => new KategoriResource($this->whenLoaded('kategori')),
        ];
    }
}
