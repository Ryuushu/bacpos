<?php

namespace App\Exports\LaporanPembelian;

use App\Models\Transaksi;
use App\Models\TransaksiPembelian;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Carbon\Carbon;

class LaporanPembelianTransaksiExportPerTahun implements FromCollection, WithHeadings
{
    protected $tahun;
    protected $idtoko;
    public function __construct($idtoko, $tahun)
    {
        $this->idtoko = $idtoko;
        $this->tahun = $tahun;
    }
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return TransaksiPembelian::selectRaw('MONTHNAME(created_at) as bulan, COUNT(id_transaksi_pembelian) as total_transaksi, SUM(totalharga) as total_harga')
            ->where('id_toko', $this->idtoko)
            ->whereYear('created_at', $this->tahun)
            ->groupByRaw('MONTHNAME(created_at), MONTH(created_at)')
            ->orderByRaw('MONTH(created_at)')
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
            'Bulan',
            'Total Transaksi',
            'Total Harga Beli',
        ];
    }
}
