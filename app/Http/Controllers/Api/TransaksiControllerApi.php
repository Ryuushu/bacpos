<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DetailTransaksi;
use App\Models\KartuStok;
use App\Models\Produk;
use Illuminate\Http\Request;
use App\Models\Transaksi;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class TransaksiControllerApi extends Controller
{
    public function store(Request $request)
    {
        // Validasi input
        $validated = $request->validate([
            'id_toko' => 'required|integer|exists:toko,id_toko', // Pastikan id_toko ada di tabel tokos
            'id_user' => 'required|integer|exists:users,id_user',
            'items' => 'required|array',
            'items.*.kode_produk' => 'required|integer|exists:produk,kode_produk',
            'items.*.qty' => 'required|integer|min:1',
            'bayar' => 'required|integer|min:1',
        ]);

        // Generate ID transaksi
        $idTransaksi = 'TRX-' . $validated['id_toko'] . now()->format('dmYHis') . rand(1000, 9999);

        // Mulai transaksi database
        DB::beginTransaction();

        try {
            // Simpan data transaksi
            $transaksi = Transaksi::create([
                'id_transaksi' => $idTransaksi,
                'id_toko' => $validated['id_toko'],
                'id_user' => $validated['id_user'],
                'totalharga' => 0,
                'pembayaran' => $validated['bayar'],
                'kembalian' => 0,
                'created_at' => now()->format('Y-m-d H:i:s'),
            ]);


            $totalHarga = 0;

            // Loop setiap item untuk menghitung harga dan menyimpan detail transaksi
            foreach ($validated['items'] as $item) {
                $produk = Produk::findOrFail($item['kode_produk']);
                $harga = $produk->harga;
                $subtotal = $harga * $item['qty'];

                // Validasi ketersediaan stok produk jika is_stok_management true
                if ($produk->is_stok_management && $produk->stok < $item['qty']) {
                    // Rollback transaksi jika stok tidak cukup
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Stok produk ' . $produk->nama . ' tidak cukup',
                    ], 400);
                }

                // Simpan detail transaksi
                DetailTransaksi::create([
                    'id_transaksi' => $idTransaksi,
                    'kode_produk' => $item['kode_produk'],
                    'harga' => $harga,
                    'qty' => $item['qty'],
                    'subtotal' => $subtotal,
                ]);

                // Tambahkan subtotal ke total harga
                $totalHarga += $subtotal;
                $itemsDetails[] = [
                    'kode_produk' => $item['kode_produk'],
                    'nama_produk' => $produk->nama_produk,
                    'qty' => $item['qty'],
                    'harga' => $harga,
                    'subtotal' => $subtotal,
                ];
            }

            foreach ($validated['items'] as $item) {
                $produk = Produk::findOrFail($item['kode_produk']);
                if ($produk->is_stok_management) {
                    $stokAwal = $produk->stok;
                    $produk->decrement('stok', $item['qty']);
                    $stokAkhir = $produk->stok; 

                    // Menambahkan ke tabel kartustok
                    KartuStok::create([
                        'kode_produk' => $item['kode_produk'],
                        'jenis_transaksi' => 'keluar', // Karena ini pengurangan stok
                        'tanggal' => now()->format('Y-m-d H:i:s'),
                        'jumlah' => $item['qty'],
                        'stok_awal' => $stokAwal,
                        'stok_akhir' => $stokAkhir,
                        'keterangan' => 'Transaksi penjualan, ID Transaksi: ' . $idTransaksi,
                    ]);
                }
            }
            $transaksi->update([
                'totalharga' => $totalHarga,
                'kembalian' => $validated['bayar'] - $totalHarga,
            ]);

            // Commit transaksi jika semua berhasil
            DB::commit();

            return response()->json([
                'message' => 'Checkout berhasil',
                'id_transaksi' => $idTransaksi,
                'totalharga' => $totalHarga,
                'pembayaran' => $validated['bayar'],
                'kembalian' => $transaksi->kembalian,
                'items' => $itemsDetails,
            ]);
        } catch (\Exception $e) {
            // Rollback transaksi jika terjadi error
            DB::rollBack();
            return response()->json([
                'message' => 'Terjadi kesalahan, transaksi dibatalkan.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function riwayat($id_toko)
    {
        $transaksi = Transaksi::with('toko', 'user', 'detailTransaksi.produk')
        ->where('id_toko', $id_toko)
        ->get();
    
    // Jika tidak ada transaksi untuk toko tersebut
    if ($transaksi->isEmpty()) {
        return response()->json(['message' => 'Tidak ada transaksi ditemukan untuk toko ini.'], 404);
    }
    
    // Mengelompokkan transaksi berdasarkan tanggal
    $transaksiGrouped = $transaksi->groupBy(function ($item) {
        return Carbon::parse($item->created_at)->format('Y-m-d'); // Group berdasarkan tanggal
    });
    
    // Menambahkan total per grup
    $transaksiWithSum = $transaksiGrouped->map(function (Collection $group) {
        $total = $group->sum(function ($item) {
            return $item->detailTransaksi->sum('harga'); // Asumsikan `harga` adalah total per detail transaksi
        });
        return [
            'total' => $total,
            'data' => $group,
        ];
    });
    
    return response()->json([
        'message' => 'Checkout berhasil',
        'data' => $transaksiWithSum,
    ]);
    }
}
