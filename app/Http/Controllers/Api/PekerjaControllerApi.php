<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pekerja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PekerjaControllerApi extends Controller
{
    /**
     * Create a new Pekerja.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_toko' => 'required|exists:toko,id_toko',
            'nama_pekerja' => 'required|string|max:100',
            'alamat_pekerja' => 'required|string|max:100',
        ]);

        try {
            $pekerja = Pekerja::create($validated);
            return response()->json([
                'status' => 'success',
                'message' => 'Pekerja created successfully.',
                'data' => $pekerja
            ], 201);
        } catch (\Exception $e) {
            Log::error("Error storing pekerja: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Error storing data.',
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all Pekerja.
     */
    public function index()
    {
        $pekerja = Pekerja::all();
        return response()->json([
            'status' => 'success',
            'message' => 'Fetched all workers.',
            'data' => $pekerja
        ], 200);
    }

    /**
     * Show a single Pekerja.
     */
    public function show($id)
    {
        $pekerja = Pekerja::find($id);

        if (!$pekerja) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pekerja not found.',
                'errors' => 'No pekerja found with the given id.'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Pekerja found.',
            'data' => $pekerja
        ], 200);
    }

    /**
     * Update a Pekerja.
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'id_toko' => 'required|exists:toko,id_toko',
            'nama_pekerja' => 'required|string|max:100',
            'alamat_pekerja' => 'required|string|max:100',
        ]);

        try {
            $pekerja = Pekerja::find($id);

            if (!$pekerja) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Pekerja not found.',
                    'errors' => 'No pekerja found with the given id.'
                ], 404);
            }

            $pekerja->update($validated);

            return response()->json([
                'status' => 'success',
                'message' => 'Pekerja updated successfully.',
                'data' => $pekerja
            ], 200);
        } catch (\Exception $e) {
            Log::error("Error updating pekerja: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Error updating data.',
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a Pekerja.
     */
    public function destroy($id)
    {
        try {
            $pekerja = Pekerja::find($id);

            if (!$pekerja) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Pekerja not found.',
                    'errors' => 'No pekerja found with the given id.'
                ], 404);
            }

            $pekerja->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Pekerja deleted successfully.'
            ], 200);
        } catch (\Exception $e) {
            Log::error("Error deleting pekerja: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Error deleting data.',
                'errors' => $e->getMessage()
            ], 500);
        }
    }
}
