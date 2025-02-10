<?php

namespace App\Exports;

use App\Models\DetailTransaksi;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\DB;

class LaporanPenjualanBerdasarkanProdukExport implements FromCollection, WithHeadings
{
    protected $idtoko;

    public function __construct($idtoko)
    {
        $this->idtoko = $idtoko;
    }


    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return DetailTransaksi::join("transaksi_penjualan", "detail_transaksi_penjualan.id_transaksi", "=", "transaksi_penjualan.id_transaksi")
            ->join("produk", "detail_transaksi_penjualan.kode_produk", "=", "produk.kode_produk") // Join ke tabel produk untuk mendapatkan nama_produk
            ->where("transaksi_penjualan.id_toko", $this->idtoko)
            ->selectRaw("produk.nama_produk, 
                SUM(detail_transaksi_penjualan.subtotal) as total_harga,
                COUNT(DISTINCT transaksi_penjualan.id_transaksi) as jumlah_transaksi")
            ->groupBy("detail_transaksi_penjualan.kode_produk", "produk.nama_produk")
            ->get();
    }

    /**
     * Define the headings for the exported data.
     *
     * @return array
     */
    public function headings(): array
    {
        return [
            'Nama Produk',
            'Total Penjualan',
            'Total Transaksi',
        ];
    }
}
