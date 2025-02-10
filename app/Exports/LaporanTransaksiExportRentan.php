<?php

namespace App\Exports;

use App\Models\DetailTransaksi;
use App\Models\Transaksi;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;

class LaporanTransaksiExportRentan implements WithMultipleSheets
{
    protected $start_date;
    protected $end_date;
    protected $idtoko;
    public function __construct($idtoko, $start_date, $end_date)
    {
        $this->idtoko = $idtoko;
        $this->start_date = $start_date;
        $this->end_date = $end_date;
    }
    public function sheets(): array
    {
        return [
            'Transaksi Rentang Tanggal' => new TransaksiSheet($this->idtoko, $this->start_date, $this->end_date),
            'Detail Transaksi' => new DetailTransaksiSheet($this->idtoko, $this->start_date, $this->end_date),
            // 'Total Pendapatan' => new TotalPendapatanSheet($this->idtoko,$this->start_date, $this->end_date),
        ];
    }
}
class TransaksiSheet implements FromCollection,WithHeadings, WithTitle
{
    protected $start_date;
    protected $end_date;
    protected $idtoko;
    public function __construct($idtoko, $start_date, $end_date)
    {
        $this->idtoko = $idtoko;
        $this->start_date = $start_date;
        $this->end_date = $end_date;
    }

    public function collection()
    {
        $table =  Transaksi::where('id_toko', $this->idtoko)->whereBetween('created_at', [$this->start_date, $this->end_date])->get();
        $totalHarga = $table->sum(function ($data) {
            return $data->totalharga;
        });
        $totalPembayan = $table->sum(function ($data) {
            return $data->pembayaran;
        });
        $data = $table->map(function ($data) {
            return [
                'id_transaksi' => $data->id_transaksi,
                'nama' => $data->user->pemilik != null ? $data->user->pemilik->nama_pemilik : $data->user->pekerja->nama_pekerja,
                'created_at' => $data->created_at,
                'ppn' => $data->ppn,
                'totalharga' => $data->totalharga,
                'pembayaran' => $data->pembayaran,
                'kembalian' => $data->kembalian == 0 ? '0' : $data->kembalian,
                'jenis_pembayaran' => $data->jenis_pembayaran,
            ];
        });
        $data->push([
            'id_transaksi' => "",
            'nama' => "",
            'created_at' => "",
            'ppn' => "",
            'totalharga' =>  "",
            'pembayaran' => "",
            'kembalian' => "",
            'jenis_pembayaran' => "",
        ]);
        $data->push([
            'id_transaksi' => "Total Pendapatan & Pembayaran",
            'nama' => "",
            'created_at' => "",
            'ppn' => "",
            'totalharga' =>  $totalHarga,
            'pembayaran' => $totalPembayan,
            'kembalian' => "",
            'jenis_pembayaran' => "",
        ]);
        return $data;
    }
    public function title(): string
    {
        $title = "Transaksi Penjualan Rentan";
        return $title; // Ganti sesuai keinginan
    }
    public function headings(): array
    {
        return [
            'ID Transaksi',
            'Nama User',
            'Waktu Transaksi',
            'Ppn',
            'Total Harga',
            'Pembayaran',
            'Kembalian',
            'Jenis Pembayaran',
        ];
    }
}
class DetailTransaksiSheet implements FromCollection,WithHeadings, WithTitle
{
    protected $start_date;
    protected $end_date;
    protected $idtoko;
    public function __construct($idtoko, $start_date, $end_date)
    {
        $this->idtoko = $idtoko;
        $this->start_date = $start_date;
        $this->end_date = $end_date;
    }

    public function collection()
    {
        $table = DetailTransaksi::join("transaksi_penjualan", "detail_transaksi_penjualan.id_transaksi", "transaksi_penjualan.id_transaksi")
            ->where('id_toko', $this->idtoko)
            ->whereBetween('detail_transaksi_penjualan.created_at', [$this->start_date, $this->end_date])
            ->get();
        $data = $table->map(function ($data) {
            return [
                'id_transaksi' => $data->id_transaksi,
                'namaproduk' => $data->produk->nama_produk,
                'harga' => $data->harga,
                'qty' => $data->qty,
                'subtotal' => $data->subtotal,
                'created_at' => $data->created_at,
            ];
        });
        return $data;
    }
    public function title(): string
    {
        $title = "Detail Transaksi Penjualan Rentan";
        return $title; // Ganti sesuai keinginan
    }
    /**
     * Define the headings for the exported data.
     *
     * @return array
     */
    public function headings(): array
    {
        return [
            'ID Transaksi',
            'Nama Produk',
            'Harga Produk',
            'Qty',
            'Subtotal',
            'Waktu Transaksi',
        ];
    }
}

class TotalPendapatanSheet implements FromCollection
{
    protected $start_date;
    protected $end_date;
    protected $idtoko;
    public function __construct($idtoko, $start_date, $end_date)
    {
        $this->idtoko = $idtoko;
        $this->start_date = $start_date;
        $this->end_date = $end_date;
    }
    public function collection()
    {
        // Hitung total pendapatan berdasarkan transaksi
        $totalPendapatan = Transaksi::whereBetween('created_at', [$this->start_date, $this->end_date])
            ->sum('totalharga');  // Menjumlahkan totalharga sebagai total pendapatan

        // Menyusun data untuk sheet
        return collect([
            ['Total Pendapatan', $totalPendapatan],
        ]);
    }
}
