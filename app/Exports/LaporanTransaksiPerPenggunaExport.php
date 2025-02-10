<?php

namespace App\Exports;

use App\Models\DetailTransaksi;
use App\Models\Transaksi;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class LaporanTransaksiPerPenggunaExport implements FromCollection, WithHeadings
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
        return  DetailTransaksi::join("transaksi_penjualan", "detail_transaksi_penjualan.id_transaksi", "=", "transaksi_penjualan.id_transaksi")
            ->where("transaksi_penjualan.id_toko", $this->idtoko)
            ->selectRaw("transaksi_penjualan.id_user, 
                SUM(detail_transaksi_penjualan.subtotal) as total_transaksi,
                COUNT(DISTINCT transaksi_penjualan.id_transaksi) as jumlah_transaksi")
            ->groupBy("transaksi_penjualan.id_user")
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
            'Nama User',
            'Total Penjualan',
            'Total Transaksi',
        ];
    }
}
