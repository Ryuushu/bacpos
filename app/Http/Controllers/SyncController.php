<?php

namespace App\Http\Controllers;

use App\Http\Resources\KategoriResource;
use App\Http\Resources\ProdukResource;
use App\Http\Resources\TokoResource;
use App\Models\DetailTransaksi;
use App\Models\Kategori;
use App\Models\Produk;
use Illuminate\Http\Request;
use App\Models\Transaksi;
use App\Models\Toko;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class SyncController extends Controller
{
    // ✅ GET - Ambil Data Toko
    public function getToko()
    {
        $pemilik = Auth::user()->pemilik; 

        if (!$pemilik) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pemilik not found for the logged-in user.',
            ], 404);
        }

        $toko = Toko::where('id_pemilik', $pemilik->id_pemilik)->with('pemilik')->get();
        return response()->json([
            'status' => 'success',
            'message' => 'Fetched all toko for pemilik.',
            'data' => TokoResource::collection($toko)
        ], 200);
    }

    // ✅ GET - Ambil Data Produk dengan Filter Stok
    public function getProduk($id, $bool)
    {
        $produk = Produk::with(['toko', 'kategori'])
                        ->where('id_toko', $id)
                        ->when($bool === "true", function ($query) {
                            return $query->where('is_stock_managed', 1);
                        })
                        ->get();

        if ($produk->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Produk not found.',
                'errors' => 'No produk found with the given id.'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Produk found.',
            'data' => ProdukResource::collection($produk)
        ], 200);
    }

    // ✅ GET - Ambil Data Kategori
    public function getKategori(Request $request)
    {
        $request->validate([
            'id_toko' => 'required|exists:toko,id_toko'
        ]);

        $kategori = Kategori::where('id_toko', $request->id_toko)->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Fetched all categories for the toko.',
            'data' => KategoriResource::collection($kategori)
        ], 200);
    }

    // ✅ GET - Ambil Data Transaksi yang Sudah Disinkronkan
    public function getTransaksiPenjualan($id_toko)
    {
        return response()->json(Transaksi::where("synced", 1)->where("id_toko", $id_toko)->get());
    }

    // ✅ GET - Ambil Detail Transaksi yang Sudah Disinkronkan
    public function getDetailTransaksiPenjualan()
    {
        return response()->json(DetailTransaksi::where("synced", 1)->get());
    }

    // 🔄 POST - Sinkronisasi Transaksi ke Server
    public function syncTransaksiPenjualan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'transaksi_penjualan' => 'required|array',
            'transaksi_penjualan.*.id' => 'required|integer',
            'transaksi_penjualan.*.total' => 'required|numeric',
            'transaksi_penjualan.*.tanggal' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            foreach ($request->transaksi_penjualan as $data) {
                Transaksi::updateOrCreate(
                    ['id' => $data['id']],
                    [
                        'total' => $data['total'],
                        'tanggal' => $data['tanggal'],
                        'synced' => 1
                    ]
                );
            }

            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Sync Error (Transaksi): " . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Failed to sync data.'], 500);
        }
    }

    // 🔄 POST - Sinkronisasi Detail Transaksi ke Server
    public function syncDetailTransaksiPenjualan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'detail_transaksi_penjualan' => 'required|array',
            'detail_transaksi_penjualan.*.id' => 'required|integer',
            'detail_transaksi_penjualan.*.transaksi_id' => 'required|integer|exists:transaksi,id',
            'detail_transaksi_penjualan.*.produk_id' => 'required|integer|exists:produk,id',
            'detail_transaksi_penjualan.*.jumlah' => 'required|integer|min:1',
            'detail_transaksi_penjualan.*.harga' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            foreach ($request->detail_transaksi_penjualan as $data) {
                DetailTransaksi::updateOrCreate(
                    ['id' => $data['id']],
                    [
                        'transaksi_id' => $data['transaksi_id'],
                        'produk_id' => $data['produk_id'],
                        'jumlah' => $data['jumlah'],
                        'harga' => $data['harga'],
                        'synced' => 1
                    ]
                );
            }

            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Sync Error (Detail Transaksi): " . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Failed to sync data.'], 500);
        }
    }
}
