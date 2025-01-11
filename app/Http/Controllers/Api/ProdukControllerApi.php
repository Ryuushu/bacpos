<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProdukResource;
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
            'nama_produk' => 'required|string|max:30',
            'kode_kategori' => 'required|exists:kategori,kode_kategori',
            'harga' => 'required|integer|min:1',
            'stok' => 'required|integer|min:0',
        ]);

        try {
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
        $produk = Produk::all();
        return response()->json([
            'status' => 'success',
            'message' => 'Fetched all products.',
            'data' => ProdukResource::collection($produk)
        ], 200);
    }

    /**
     * Show a single Produk.
     */
    public function show($id)
    {
        $produk = Produk::find($id);

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
            'data' => new ProdukResource($produk)
        ], 200);
    }

    /**
     * Update a Produk.
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'nama_produk' => 'required|string|max:30',
            'kode_kategori' => 'required|exists:kategori,kode_kategori',
            'harga' => 'required|integer|min:1',
            'stok' => 'required|integer|min:0',
        ]);

        try {
            $produk = Produk::find($id);

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
            $produk = Produk::find($id);

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
