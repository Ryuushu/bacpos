<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\KategoriResource;
use App\Models\Kategori;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class KategoriControllerApi extends Controller
{
    /**
     * Create a new Kategori.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_toko' => 'required|exists:toko,id_toko',
        'nama_kategori' => 'required|string|max:30|unique:kategori,nama_kategori,NULL,id_kategori,id_toko,' . $request->id_toko,
        ]);

        try {
            $kategori = Kategori::create($validated);
            return response()->json([
                'status' => 'success',
                'message' => 'Kategori created successfully.',
                'data' => new KategoriResource($kategori)
            ], 201);
        } catch (\Exception $e) {
            Log::error("Error storing kategori: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Error storing data.',
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all Kategori for a specific Toko.
     */
    public function index(Request $request)
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

    /**
     * Show a single Kategori.
     */
    public function show($id)
    {
        $kategori = Kategori::find($id);

        if (!$kategori) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kategori not found.',
                'errors' => 'No kategori found with the given id.'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Kategori found.',
            'data' => new KategoriResource($kategori)
        ], 200);
    }

    /**
     * Update a Kategori.
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'id_toko' => 'required|exists:toko,id_toko',
            'nama_kategori' => 'required|string|max:30|unique:kategori,nama_kategori'
        ]);

        try {
            $kategori = Kategori::find($id);

            if (!$kategori) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Kategori not found.',
                    'errors' => 'No kategori found with the given id.'
                ], 404);
            }

            $kategori->update($validated);

            return response()->json([
                'status' => 'success',
                'message' => 'Kategori updated successfully.',
                'data' => new KategoriResource($kategori)
            ], 200);
        } catch (\Exception $e) {
            Log::error("Error updating kategori: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Error updating data.',
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a Kategori.
     */
    public function destroy($id)
    {
        try {
            $kategori = Kategori::find($id);

            if (!$kategori) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Kategori not found.',
                    'errors' => 'No kategori found with the given id.'
                ], 404);
            }

            $kategori->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Kategori deleted successfully.'
            ], 200);
        } catch (\Exception $e) {
            Log::error("Error deleting kategori: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Error deleting data.',
                'errors' => $e->getMessage()
            ], 500);
        }
    }
}
