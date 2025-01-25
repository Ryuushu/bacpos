<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DetailOpname;
use App\Models\KartuStok;
use App\Models\Pekerja;
use App\Models\Produk;
use App\Models\StokOpname;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class StokControllerApi extends Controller
{
    public function index()
    {
        $pekerja = Pekerja::with('user')->get();
        return response()->json([
            'status' => 'success',
            'message' => 'Fetched all workers.',
            'data' => $pekerja
        ], 200);
    }

    public function show($id)
    {
        $kartuStoks = KartuStok::join('produk', 'produk.kode_produk', '=', 'kartu_stok.kode_produk')
            ->join('toko', 'toko.id_toko', '=', 'produk.id_toko')
            ->where('toko.id_toko', $id)
            ->get();

        if (!$kartuStoks) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pekerja not found.',
                'errors' => 'No pekerja found with the given id.'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Pekerja found.',
            'data' => $kartuStoks
        ], 200);
    }
    public function searchBykodepwitharray(Request $request)
    {
        $request->validate([
            'kode_produk' => 'required|array',
            'kode_produk.*' => 'exists:produk,kode_produk', // Ensure each kode_produk exists in the produk table
        ]);

        // Retrieve the array of kode_produk
        $kodeProduk = $request->input('kode_produk');

        // Fetch the products from the database where kode_produk is in the array
        $products = Produk::whereIn('kode_produk', $kodeProduk)->get();

        // Return the data as a JSON response
        return response()->json($products);
    }
    public function addstokopname(Request $request)
    {
        try {
            DB::beginTransaction(); // Memulai transaksi

            $validated = $request->validate([
                'id_toko' => 'required|integer|exists:toko,id_toko',
                'stok_opname' => 'required|array',
                'stok_opname.*.kode_produk' => 'required|integer|exists:produk,kode_produk',
                'stok_opname.*.stok_fisik' => 'required|integer|min:0',
                'stok_opname.*.keterangan' => 'nullable|string',
            ]);

            $idOpname = 'OPN-' . '-2-' . $validated['id_toko'] . now()->format('dmYHis') . rand(1000, 9999);

            $stokOpname = StokOpname::create([
                'id_opname' => $idOpname,
                'id_toko' => $validated['id_toko'],
                'tanggal_opname' => now(),
                'keterangan' => '',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($validated['stok_opname'] as $stok) {
                // Ambil stok sistem dari database
                $produk = Produk::where('kode_produk', $stok['kode_produk'])->first();

                if (!$produk) {
                    throw new \Exception("Produk dengan kode {$stok['kode_produk']} tidak ditemukan.");
                }

                // Hitung selisih
                $stokSistem = $produk->stok;
                $stokFisik = $stok['stok_fisik'];
                $selisih = $stokFisik - $stokSistem;

                // Perbarui stok produk


                // Simpan detail opname
                DetailOpname::create([
                    'id_opname' => $stokOpname->id_opname,
                    'kode_produk' => $stok['kode_produk'],
                    'stok_fisik' => $stokFisik,
                    'stok_sistem' => $stokSistem,
                    'selisih' => $selisih,
                    'keterangan' => $stok['keterangan'],
                ]);
                $produk->update(['stok' => $selisih == 0 ? 0 : $stokFisik,'is_stock_managed' => $selisih == 0 ? 0 : 1]);
                // Simpan ke KartuStok
                KartuStok::create([
                    'kode_produk' => $stok['kode_produk'],
                    'jenis_transaksi' => 'penyesuaian', // Sesuaikan jenis transaksi
                    'tanggal' => now()->format('Y-m-d H:i:s'),
                    'jumlah' => abs($selisih), // Hanya jumlah perubahan stok
                    'stok_awal' => $stokSistem,
                    'stok_akhir' => $stokFisik,
                    'keterangan' => $stok['keterangan']
                ]);
            }

            DB::commit(); // Commit transaksi jika semua berhasil

            return response()->json(['message' => 'Data saved successfully'], 200);
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback transaksi jika ada kesalahan
            return response()->json(['message' => 'Data saving failed', 'error' => $e->getMessage()], 500);
        }
    }
}
