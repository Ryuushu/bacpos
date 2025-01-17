<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProdukResource;
use App\Models\KartuStok;
use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProdukControllerApi extends Controller
{
    /**
     * Create a new Produk.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_toko' => 'required|exists:toko,id_toko',
            'kode_kategori' => 'required|exists:kategori,kode_kategori',
            'nama_produk' => 'required|string|max:255',
            'harga' => 'required|integer|min:0',
            'stok' =>  $request->filled('stok')?'integer|min:0':'',
            'is_stock_managed'=> 'int'
        ]);

        try {
            $produk = Produk::create($validated);
            if ($request->input('is_stock_managed') == 1) {
                // Create or update the stock card entry (you should replace this with the actual model and logic)
                KartuStok::create([
                    'id_toko' => $produk->id_toko,
                'kode_produk' => $produk->kode_produk, 
                'tanggal' => now(), 
                'jenis_transaksi' => 'masuk',
                'jumlah' => $produk->stok, 
                'stok_awal' => 0, 
                'stok_akhir' => $produk->stok, 
                'keterangan' => 'Stok Masuk', 
                'created_at' => now(),
                ]);
            }
            return response()->json([
                'status' => 'success',
                'message' => 'Produk created successfully.',
                'data' => new ProdukResource($produk)
            ], 201);
        } catch (\Exception $e) {
            Log::error("Error storing produk: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Error storing data.',
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all Produk.
     */
    public function index()
    {
        $produk =Produk::with(['toko', 'kategori'])->get();
        return response()->json([
            'status' => 'success',
            'message' => 'Fetched all products.',
            'data' => ProdukResource::collection($produk)
        ], 200);
    }

    /**
     * Show a single Produk.
     */
    public function shows($id,$bool)
    {
        $bool=="true"?
            $produk = Produk::with(['toko', 'kategori'])->where('id_toko',$id)->where('is_stock_managed',1)->get()
:
            $produk = Produk::with(['toko', 'kategori'])->where('id_toko',$id)->get();
          
 

        if (!$produk) {
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

    /**
     * Update a Produk.
     */
    public function update(Request $request, $id)
    {
        try {

        $produk = Produk::findOrFail($id);
        $validated = $request->validate([
            'id_toko' => 'sometimes|exists:toko,id_toko',
            'id_kategori' => 'sometimes|exists:kategori,id_kategori',
            'nama_produk' => 'sometimes|string|max:255',
            'harga' => 'sometimes|integer|min:0',
            'stok' => $request->filled('stok')?'integer|min:0':'',
        ]);

            if (!$produk) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Produk not found.',
                    'errors' => 'No produk found with the given id.'
                ], 404);
            }

            $produk->update($validated);

            return response()->json([
                'status' => 'success',
                'message' => 'Produk updated successfully.',
                'data' => new ProdukResource($produk)
            ], 200);
        } catch (\Exception $e) {
            Log::error("Error updating produk: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Error updating data.',
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a Produk.
     */
    public function destroy($id)
    {
        try {
            $produk = Produk::findOrFail($id);

            if (!$produk) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Produk not found.',
                    'errors' => 'No produk found with the given id.'
                ], 404);
            }

            $produk->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Produk deleted successfully.'
            ], 200);
        } catch (\Exception $e) {
            Log::error("Error deleting produk: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Error deleting data.',
                'errors' => $e->getMessage()
            ], 500);
        }
    }
}
