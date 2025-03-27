<?php

namespace App\Exports\LaporanPembelian;

use App\Models\DetailTransaksi;
use App\Models\DetailTransaksiPembelian;
use App\Models\Transaksi;
use App\Models\TransaksiPembelian;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Sheet;

class LaporanPembelianTransaksiExportPerHari implements WithMultipleSheets
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
        $table = TransaksiPembelian::with('user.pekerja', 'user.pemilik')->where('id_toko', $this->idtoko)->whereDate('created_at', Carbon::today())->get();
        $totalHarga = $table->sum(function ($data) {
            return $data->totalharga;  // Pastikan untuk mengganti dengan kolom harga yang sesuai
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
        $title = "Transaksi Pembelian Hari Ini";
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
            'Total Harga Beli',
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
        return 'Detail Transaksi Pembelian Hari Ini'; // Ganti sesuai keinginan
    }
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $table = DetailTransaksiPembelian::with('transaksi.user.pekerja', 'transaksi.user.pemilik')->join("transaksi_pembelian", "detail_transaksi_pembelian.id_transaksi_pembelian", "transaksi_pembelian.id_transaksi_pembelian")
        ->where('transaksi_pembelian.id_toko', $this->idtoko)
        ->whereDate('detail_transaksi_pembelian.created_at', Carbon::today())
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
