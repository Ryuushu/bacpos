<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProdukResource;
use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Facades\Image;
use Intervention\Image\ImageManager;

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
            'is_stock_managed' => 'int',
            'url_img' => 'sometimes|image|mimes:jpg,jpeg,png|max:5120', // Validate image upload
        ]);

        try {
            $imagePath = null; // Initialize the image path

            if ($request->hasFile('url_img')) {
                $manager = new ImageManager(new Driver());

                // Get the uploaded file
                $file = $request->file('url_img');

                // Create a unique filename for the image
                $fileName = uniqid() . '_' . time() . '.' . $file->getClientOriginalExtension();
                $imagePath = public_path('uploadfile/produk/' . $fileName);
                $img = $manager->read($file);
                $img->toWebp(60)->save($imagePath);
                $imagePath = "uploadfile/produk/" . $fileName;
            }

            unset($validated['stok']);
            $validated['url_img'] = $imagePath;

            // Store the product
            $produk = Produk::create($validated);

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
                'is_stock_managed' => 'int',
                //    'stok' =>  $request->input('stok') != "null"? 'sometimes|integer|min:0' : 'sometimes',
                'url_img' => 'sometimes|image|mimes:jpg,jpeg,png,gif|max:5120', // Validate image upload
            ]);

            // if ($validated['stok'] == "null") {

            // }
            // unset($validated['stok']);
            // Handle image upload (update)
            if ($request->hasFile('url_img')) {
                $manager = new ImageManager(new Driver());
                $file = $request->file('url_img');
                $oldPath = public_path($produk->url_img);
                if ($produk->url_img && file_exists($oldPath)) { // Perbaiki kurung tutup
                    unlink($oldPath);
                }
                // Simpan file baru dengan nama unik
                $fileName = uniqid() . '_' . time() . '.' . $file->getClientOriginalExtension();
                $imagePath = public_path('uploadfile/produk/' . $fileName);
                $img = $manager->read($file);
                $img->toWebp(60)->save($imagePath);
                $imagePath = "uploadfile/produk/" . $fileName;
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
        DB::beginTransaction(); // Mulai transaksi
        try {
            $produk = Produk::findOrFail($id);
    
            $oldPath = public_path($produk->url_img);
    
            // Hapus produk dari database dulu
            $produk->delete();
    
            DB::commit(); // Konfirmasi transaksi database
    
            // Setelah commit, baru hapus file
            if (is_file($oldPath) && file_exists($oldPath)) {
                unlink($oldPath);
            }
    
            return response()->json([
                'status' => 'success',
                'message' => 'Produk deleted successfully.'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack(); // Batalkan transaksi jika terjadi error
            Log::error("Error deleting produk: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Error deleting data.',
                'errors' => $e->getMessage()
            ], 500);
        }
    }
    
}
