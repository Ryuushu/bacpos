<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TokoResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id_toko' => $this->id_toko,
            'nama_toko' => $this->nama_toko,
            'alamat_toko' => $this->alamat_toko,
            'whatsapp' => $this->whatsapp,
            'instagram' => $this->instagram,
            'pemilik' => [
                'id_pemilik' => $this->pemilik->id_pemilik,
                'nama_pemilik' => $this->pemilik->nama_pemilik,
            ],
            'url_img' =>$this->url_img ? asset($this->url_img) : null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
