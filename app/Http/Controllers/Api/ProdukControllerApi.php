<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProdukResource;
use App\Models\KartuStok;
use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProdukControllerApi extends Controller
{
    /**
     * Create a new Produk.
     */
    public function store(Request $request)
    {

        // return response()->json([
        //     'status' => 'success',
        //     'message' => 'Produk created successfully.',
        //     'data' => $request->input('stok')
        // ], 201);

        $validated = $request->validate([
            'id_toko' => 'required|exists:toko,id_toko',
            'kode_kategori' => 'required|exists:kategori,kode_kategori',
            'nama_produk' => 'required|string|max:255',
            'harga' => 'required|integer|min:0',
            'stok' =>  $request->input('stok') != "null" ? 'integer|min:0' : '',
            'is_stock_managed' => 'int',
            'url_img' => 'sometimes|image|mimes:jpg,jpeg,png|max:2048', // Validate image upload
        ]);

        try {

            if ($request->hasFile('url_img')) {
                $file = $request->file('url_img');
                $fileName = uniqid() . '_' . time() . '.' . $file->getClientOriginalExtension();
                $imagePath = $file->move("uploadfile/produk/", $fileName);
            } else {
                $imagePath = null; // No image uploaded
            }

            // Create the product
            if ($validated['stok'] == "null") {
                unset($validated['stok']);
            }
            $validated['url_img'] = $imagePath; // Store the image URL in the database
            $produk = Produk::create($validated);

            if ($request->input('is_stock_managed') == 1) {
                // Create or update the stock card entry
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
                'message' => $imagePath,
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
        $produk = Produk::with(['toko', 'kategori'])->get();
        return response()->json([
            'status' => 'success',
            'message' => 'Fetched all products.',
            'data' => ProdukResource::collection($produk)
        ], 200);
    }

    /**
     * Show a single Produk.
     */
    public function shows($id, $bool)
    {
        $bool == "true" ?
            $produk = Produk::with(['toko', 'kategori'])->where('id_toko', $id)->where('is_stock_managed', 1)->get()
            :
            $produk = Produk::with(['toko', 'kategori'])->where('id_toko', $id)->get();



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
                // 'id_toko' => 'sometimes|exists:toko,id_toko',
                'kode_kategori' => 'sometimes|exists:kategori,kode_kategori',
                'nama_produk' => 'sometimes|string|max:255',
                'harga' => 'sometimes|integer|min:0',
               'stok' =>  $request->input('stok') != "null"? 'sometimes|integer|min:0' : 'sometimes',
                'url_img' => 'sometimes|image|mimes:jpg,jpeg,png,gif|max:2048', // Validate image upload
            ]);
           
            if ($validated['stok'] == "null") {
                unset($validated['stok']);
            }
            // Handle image upload (update)
           if ($request->hasFile('url_img')) {
                $file = $request->file('url_img'); 
            
                $oldPath = public_path($produk->url_img);
                if ($produk->url_img && file_exists($oldPath)) { // Perbaiki kurung tutup
                    unlink($oldPath);
                }
                // Simpan file baru dengan nama unik
                $fileName = uniqid() . '_' . time() . '.' . $file->getClientOriginalExtension();
                $imagePath = $file->move("uploadfile/produk/", $fileName);
                $validated['url_img'] = $imagePath;
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
                $oldPath = public_path($produk->url_img);
                if (is_file($oldPath) && file_exists($oldPath)) { // Perbaiki kurung tutup
                    unlink($oldPath);
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
