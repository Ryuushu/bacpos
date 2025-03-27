<?php

namespace App\Exports\LaporanPembelian;

use App\Models\DetailTransaksi;
use App\Models\DetailTransaksiPembelian;
use App\Models\Transaksi;
use App\Models\TransaksiPembelian;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;

class LaporanPembelianTransaksiExportRentan implements WithMultipleSheets
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
class TransaksiSheet implements FromCollection, WithHeadings, WithTitle
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
        $table = TransaksiPembelian::with('user.pekerja', 'user.pemilik')->where('id_toko', $this->idtoko)->whereBetween('created_at', [$this->start_date, $this->end_date])->get();
        $totalHarga = $table->sum(function ($data) {
            return $data->totalharga;
        });
        $data = $table->map(function ($data) {
            return [
                'id_transaksi_pembelian' => $data->id_transaksi_pembelian,
                'nama' => $data->user->pemilik != null ? $data->user->pemilik->nama_pemilik : $data->user->pekerja->nama_pekerja,
                'created_at' => date('Y-m-d H:i:s', strtotime($data->created_at)),
                'totalharga' => $data->totalharga,
            ];
        });
        $data->push([
            'id_transaksi' => "",
            'nama' => "",
            'created_at' => "",
            'totalharga' =>  "",
        ]);
        $data->push([
            'id_transaksi' => "Total",
            'nama' => "",
            'created_at' => "",
            'totalharga' =>  $totalHarga,
        ]);
        return $data;
    }
    public function title(): string
    {
        $title = "Transaksi Pembelian Rentan";
        return $title; // Ganti sesuai keinginan
    }
    public function headings(): array
    {
        return [
            'ID Transaksi',
            'Nama Kasir',
            'Waktu Transaksi',
            'Total Harga Beli',
        ];
    }
}
class DetailTransaksiSheet implements FromCollection, WithHeadings, WithTitle
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
        $table = DetailTransaksiPembelian::with('transaksi.user.pekerja', 'transaksi.user.pemilik')->join("transaksi_pembelian", "detail_transaksi_pembelian.id_transaksi_pembelian", "transaksi_pembelian.id_transaksi_pembelian")
            ->where('transaksi_pembelian.id_toko', $this->idtoko)
            ->whereBetween('detail_transaksi_pembelian.created_at', [$this->start_date, $this->end_date])
            ->get();
        $data = $table->map(function ($data) {
            return [
                'id_transaksi' => $data->id_transaksi_pembelian,
                'namauser ' => $data->transaksi->user->pemilik != null ? $data->transaksi->user->pemilik->nama_pemilik : $data->transaksi->user->pekerja->nama_pekerja,
                'namaproduk' => $data->produk->nama_produk,
                'qty' => $data->qty,
                'hargabeli' => $data->harga_beli,
                'subtotal' => $data->subtotal,
                'harga' => $data->harga,
                'created_at' => date('Y-m-d H:i:s', strtotime($data->created_at)),
            ];
        });
        return $data;
    }
    public function title(): string
    {
        $title = "Detail Transaksi Pembelian Rentan";
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
            'Nama Kasir',
            'Nama Produk',
            'Qty',
            'Harga Beli',
            'Subtotal',
            'Harga Jual',
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
