<?php

namespace App\Http\Controllers\APi;

use App\Exports\LaporanPembelian\LaporanPembelianTransaksiExportPerHari;
use App\Exports\LaporanPembelian\LaporanPembelianTransaksiExportPerTahun;
use App\Exports\LaporanPembelian\LaporanPembelianTransaksiExportRentan;
use App\Exports\LaporanPenjualan\LaporanPenjualanBerdasarkanProdukExport;
use App\Exports\LaporanPenjualan\LaporanTransaksiExportPerHari;
use App\Exports\LaporanPenjualan\LaporanTransaksiExportPerTahun;
use App\Exports\LaporanPenjualan\LaporanTransaksiExportRentan;
use App\Exports\LaporanPenjualan\LaporanTransaksiPerPenggunaExport;
use App\Http\Controllers\Controller;
use App\Models\DetailTransaksi;
use App\Models\DetailTransaksiPembelian;
use App\Models\Toko;
use App\Models\Transaksi;
use App\Models\TransaksiPembelian;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class LaporanControllerApi extends Controller
{
    public function exportLaporanPenjualan($type, Request $req, $idtoko)
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
    public function exportLaporanPembelian($type, Request $req, $idtoko)
    {
        $namatoko = Toko::findOrFail($idtoko)->nama_toko;

        try {
            switch ($type) {
                case 'transaksi-pembelian-per-hari':
                    return Excel::download(new LaporanPembelianTransaksiExportPerHari($idtoko), "{$namatoko}_laporan_transaksi_pembelian_per_hari.xlsx");

                case 'transaksi-pembelian-per-tahun':
                    $validated = $req->validate([
                        'tahun' => 'required|integer|min:2000|max:' . date('Y'), // Tahun harus dalam rentang yang masuk akal
                    ]);
                    return Excel::download(
                        new LaporanPembelianTransaksiExportPerTahun($idtoko, $validated["tahun"]),
                        "{$namatoko}_laporan_transaksi_pembelian_tahun_{$validated['tahun']}.xlsx"
                    );

                case 'transaksi-pembelian-per-rentan':
                    $validated = $req->validate([
                        'tglmulai' => 'required|date|before_or_equal:tglakhir',
                        'tglakhir' => 'required|date|after_or_equal:tglmulai',
                    ]);
                    return Excel::download(
                        new LaporanPembelianTransaksiExportRentan($idtoko, $validated["tglmulai"], $validated["tglakhir"]),
                        "{$namatoko}_laporan_transaksi_pembelian_{$validated['tglmulai']}_to_{$validated['tglakhir']}.xlsx"
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
        $table = Transaksi::with('user.pekerja', 'user.pemilik')->where('id_toko', $idtoko)->whereDate('created_at', Carbon::today())->get();
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
        return response()->json([
            $data
        ], 201);
    }
}
