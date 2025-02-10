<?php

namespace App\Exports\LaporanPenjualan;

use App\Models\DetailTransaksi;
use App\Models\Transaksi;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Sheet;

class LaporanTransaksiExportPerHari implements WithMultipleSheets
{
    protected $idtoko;

    public function __construct($idtoko)
    {
        $this->idtoko = $idtoko;
    }
    public function sheets(): array
    {
        return [
            'Transaksi Per Hari' => new LaporanTransaksiPerHariExport($this->idtoko),
            'Detail Transaksi' => new LaporanDetailTransaksiExport($this->idtoko),
            // 'Total Pendapatan' => new TotalPendapatanSheetPerhari()
        ];
    }
}
class LaporanTransaksiPerHariExport implements FromCollection, WithHeadings, WithTitle, WithColumnFormatting

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
        $table = Transaksi::with('user.pekerja', 'user.pemilik')->where('id_toko', $this->idtoko)->whereDate('created_at', Carbon::today())->get();
        $totalHarga = $table->sum(function ($data) {
            return $data->totalharga;  // Pastikan untuk mengganti dengan kolom harga yang sesuai
        });
        $totalPembayan = $table->sum(function ($data) {
            return $data->pembayaran;  // Pastikan untuk mengganti dengan kolom harga yang sesuai
        });
        $data = $table->map(function ($data) {
            return [
                'id_transaksi' => $data->id_transaksi,
                'nama' => $data->user->pemilik != null ? $data->user->pemilik->nama_pemilik : $data->user->pekerja->nama_pekerja,
                'created_at' => date('Y-m-d H:i:s', strtotime($data->created_at)),
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
        $title = "Transaksi Penjualan Hari Ini";
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
            'Waktu Transaksi',
            'Ppn',
            'Total Harga',
            'Pembayaran',
            'Kembalian',
            'Jenis Pembayaran',
        ];
    }
    public function columnFormats(): array
    {
        return [
            'G' => '0',  // Menjamin angka di kolom D tampil dengan format angka
        ];
    }
    public function styles(Sheet $sheet)
    {
        // Gaya untuk heading (baris pertama)
        $sheet->getStyle('A1:D1')->applyFromArray([
            'font' => [
                'bold' => true,  // Mengatur font menjadi tebal
                'size' => 12,    // Menyesuaikan ukuran font (opsional)
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,  // Menyelaraskan teks di tengah
            ]
        ]);

        // Gaya untuk baris terakhir (baris total)
        $sheet->getStyle('A' . ($sheet->getHighestRow()))->applyFromArray([
            'font' => ['bold' => true],  // Menebalkan font baris total
        ]);
    }
}
class LaporanDetailTransaksiExport implements FromCollection, WithHeadings, WithTitle
{
    protected $idtoko;

    public function __construct($idtoko)
    {
        $this->idtoko = $idtoko;
    }
    public function title(): string
    {
        return 'Detail Transaksi Penjualan Hari Ini'; // Ganti sesuai keinginan
    }
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $table = DetailTransaksi::with('transaksi.user.pekerja', 'transaksi.user.pemilik')->join("transaksi_penjualan", "detail_transaksi_penjualan.id_transaksi", "transaksi_penjualan.id_transaksi")
        ->where('transaksi_penjualan.id_toko', $this->idtoko)
        ->whereDate('detail_transaksi_penjualan.created_at', Carbon::today())
        ->get();
        $data = $table->map(function ($data) {
            return [
                'id_transaksi' => $data->id_transaksi,
                'namauser ' => $data->transaksi->user->pemilik != null ? $data->transaksi->user->pemilik->nama_pemilik : $data->transaksi->user->pekerja->nama_pekerja,
                'namaproduk' => $data->produk->nama_produk,
                'harga' => $data->harga,
                'qty' => $data->qty,
                'subtotal' => $data->subtotal,
                'created_at' => date('Y-m-d H:i:s', strtotime($data->created_at)),
            ];
        });
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
            'ID Transaksi',
            'Nama Kasir',
            'Nama Produk',
            'Harga Produk',
            'Qty',
            'Subtotal',
            'Waktu Transaksi',
        ];
    }
}
class TotalPendapatanSheetPerhari implements FromCollection, WithTitle
{
    protected $idtoko;

    public function __construct($idtoko)
    {
        $this->idtoko = $idtoko;
    }
    public function title(): string
    {
        return 'Total Pendapatan Hari Ini'; // Ganti sesuai keinginan
    }
    public function collection()
    {
        // Hitung total pendapatan berdasarkan transaksi
        $totalPendapatan = Transaksi::where('id_toko', $this->idtoko)->whereDate('created_at', Carbon::today())->sum('totalharga');  // Menjumlahkan totalharga sebagai total pendapatan

        // Menyusun data untuk sheet
        return collect([
            ['Total Pendapatan', $totalPendapatan],
        ]);
    }
}
