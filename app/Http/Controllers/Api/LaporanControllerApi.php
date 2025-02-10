<?php

namespace App\Http\Controllers\APi;

use App\Exports\LaporanDetailTransaksiExport;
use App\Exports\LaporanPenjualanBerdasarkanProdukExport;
use App\Exports\LaporanTransaksiExport;
use App\Exports\LaporanTransaksiExportPerHari;
use App\Exports\LaporanTransaksiExportPerTahun;
use App\Exports\LaporanTransaksiExportRentan;
use App\Exports\LaporanTransaksiPerBulanExport;
use App\Exports\LaporanTransaksiPerHariExport;
use App\Exports\LaporanTransaksiPerPenggunaExport;
use App\Http\Controllers\Controller;
use App\Models\DetailTransaksi;
use App\Models\Toko;
use App\Models\Transaksi;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class LaporanControllerApi extends Controller
{
    public function exportLaporan($type, Request $req, $idtoko)
    {
        $namatoko = Toko::findOrFail($idtoko)->nama_toko;

        try {
            switch ($type) {
                case 'transaksi-penjualan-per-hari':
                    return Excel::download(new LaporanTransaksiExportPerHari($idtoko), "{$namatoko}_laporan_transaksi_penjualan_per_hari.xlsx");

                case 'transaksi-penjualan-per-tahun':
                    $validated = $req->validate([
                        'tahun' => 'required|integer|min:2000|max:' . date('Y'), // Tahun harus dalam rentang yang masuk akal
                    ]);
                    return Excel::download(
                        new LaporanTransaksiExportPerTahun($idtoko, $validated["tahun"]),
                        "{$namatoko}_laporan_transaksi_penjualan_tahun_{$validated['tahun']}.xlsx"
                    );

                case 'transaksi-penjualan-per-rentan':
                    $validated = $req->validate([
                        'tglmulai' => 'required|date|before_or_equal:tglakhir',
                        'tglakhir' => 'required|date|after_or_equal:tglmulai',
                    ]);
                    return Excel::download(
                        new LaporanTransaksiExportRentan($idtoko, $validated["tglmulai"], $validated["tglakhir"]),
                        "{$namatoko}_laporan_transaksi_penjualan_{$validated['tglmulai']}_to_{$validated['tglakhir']}.xlsx"
                    );

                case 'penjualan-berdasarkan-produk':
                    return Excel::download(new LaporanPenjualanBerdasarkanProdukExport($idtoko), "laporan_{$namatoko}_penjualan_berdasarkan_produk.xlsx");

                case 'transaksi-per-pengguna':
  
                    return Excel::download(
                        new LaporanTransaksiPerPenggunaExport($idtoko),
                        "{$namatoko}_laporan__transaksi_penjualan_per_pengguna_.xlsx"
                    );

                default:
                    return response()->json(['error' => 'Tipe laporan tidak dikenali'], 400);
            }
        } catch (\Exception $e) {
            Log::error("Error generating report: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mengunduh laporan.',
                'errors' => $e->getMessage()
            ], 500);
        }
    }
    public function test($type, Request $req, $idtoko)
    {
        $data = DetailTransaksi::join("transaksi_penjualan", "detail_transaksi_penjualan.id_transaksi", "=", "transaksi_penjualan.id_transaksi")
        ->join("produk", "detail_transaksi_penjualan.kode_produk", "=", "produk.kode_produk") // Join ke tabel produk untuk mendapatkan nama_produk
        ->where("transaksi_penjualan.id_toko", $idtoko)
        ->selectRaw("produk.nama_produk, 
            SUM(detail_transaksi_penjualan.subtotal) as total_harga,
            COUNT(DISTINCT transaksi_penjualan.id_transaksi) as jumlah_transaksi")
        ->groupBy("detail_transaksi_penjualan.kode_produk", "produk.nama_produk")
        ->get();

        return response()->json([
            $toko = Toko::findOrFail($idtoko)->nama_toko
        ], 201);
    }
}
