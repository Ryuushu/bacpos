<?php

namespace App\Exports\LaporanPenjualan;

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
        $table = Transaksi::with("user.pemilik","user.pekerja")
        ->where("id_toko", $this->idtoko)
        ->get();
        $data = $table->groupBy(function ($item) {
            return $item->user->pemilik != null 
                ? $item->user->pemilik->nama_pemilik 
                : $item->user->pekerja->nama_pekerja;
        })->map(function ($groupedData, $nama) {
            return [
                'nama' => $nama,
                'total_transaksi' => $groupedData->sum('totalharga'),
                'jumlah_transaksi' => $groupedData->count(),

            ];
        })->values();
        return $data;
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
