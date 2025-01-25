<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Toko;
use Illuminate\Http\Request;
use App\Http\Resources\TokoResource;
use App\Models\Produk;
use App\Models\Transaksi;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class TokoControllerApi extends Controller
{
    // List all toko for the logged-in pemilik
    public function index()
    {
        // Get the logged-in user and join with pemilik to get id_pemilik
        $pemilik = Auth::user()->pemilik; // Assuming user has a relationship with pemilik

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

    // Create toko for the logged-in pemilik
    public function store(Request $request)
    {
        // Get the logged-in user and join with pemilik to get id_pemilik
        $pemilik = Auth::user()->pemilik; // Assuming user has a relationship with pemilik

        if (!$pemilik) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pemilik not found for the logged-in user.',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nama_toko' => 'required|string|max:100',
            'alamat_toko' => 'required|string|max:200',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation errors',
                'errors' => $validator->errors(),
            ], 422);
        }

        $toko = Toko::create([
            'id_pemilik' => $pemilik->id_pemilik,  // Assign the pemilik's id
            'nama_toko' => $request->nama_toko,
            'alamat_toko' => $request->alamat_toko,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Toko created successfully.',
            'data' => new TokoResource($toko)
        ], 201);
    }

    // Show a single toko for the logged-in pemilik
    public function show($id)
    {
        // Get the logged-in user and join with pemilik to get id_pemilik
        $pemilik = Auth::user()->pemilik; // Assuming user has a relationship with pemilik
        if (!$pemilik) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pemilik not found for the logged-in user.',
            ], 404);
        }

        $toko = Toko::where('id_pemilik', $pemilik->id_pemilik)->with('pemilik')->find($id);

        if (!$toko) {
            return response()->json([
                'status' => 'error',
                'message' => 'Toko not found or you do not have permission to view this toko.',
            ], 404);
        }

        return new TokoResource($toko);
    }

    // Update toko for the logged-in pemilik
    public function update(Request $request, $id)
    {

        // Get the logged-in user and join with pemilik to get id_pemilik
        $pemilik = Auth::user()->pemilik; // Assuming user has a relationship with pemilik

        if (!$pemilik) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pemilik not found for the logged-in user.',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nama_toko' => 'required|string|max:100',
            'alamat_toko' => 'required|string|max:200',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation errors',
                'errors' => $validator->errors(),
            ], 422);
        }

        $toko = Toko::where('id_pemilik', $pemilik->id_pemilik)->where('id_toko', $id)->first();

        if (!$toko) {
            return response()->json([
                'status' => 'error',
                'message' => 'Toko not found or you do not have permission to update this toko.',
            ], 404);
        }

        $toko->update($request->only(['nama_toko', 'alamat_toko']));

        return (new TokoResource($toko))
            ->additional([
                'status' => 'success',
                'message' => 'Toko updated successfully',
            ]);
    }

    // Delete toko for the logged-in pemilik
    public function destroy($id)
    {
        // Get the logged-in user and join with pemilik to get id_pemilik
        $pemilik = Auth::user()->pemilik; // Assuming user has a relationship with pemilik

        if (!$pemilik) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pemilik not found for the logged-in user.',
            ], 404);
        }

        $toko = Toko::where('id_pemilik', $pemilik->id_pemilik)->where('id_toko', $id)->first();


        if (!$toko) {
            return response()->json([
                'status' => 'error',
                'message' => 'Toko not found or you do not have permission to delete this toko.',
            ], 404);
        }

        $toko->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Toko deleted successfully',
        ]);
    }
    public function dashboardtoko($idtoko)
    {
    
        // Data yang akan ditampilkan di dashboard
        $produkCount =  Produk::where('id_toko', $idtoko )->count();
        $transaksiCount = Transaksi::where('id_toko', $idtoko)->count();
        $today = now()->toDateString(); // Format YYYY-MM-DD
        $totalPendapatanHarian = Transaksi::where('id_toko', $idtoko)
            ->whereDate('created_at', $today)
            ->sum('totalharga');
            
        if ($produkCount === 0) {
            $produkCount = 0; // Jika tidak ada produk, set menjadi 0
        }
        if ($transaksiCount === 0) {
            $transaksiCount = 0; // Jika tidak ada transaksi, set menjadi 0
        }
        if ($totalPendapatanHarian === null) {
            $totalPendapatanHarian = 0; // Jika tidak ada pendapatan, set menjadi 0
        }
        return response()->json([
            'status' => 'success',
            'message' => 'Data successful',
            'data' => [
                'produk_count' => $produkCount,
                'transaksi_count' => $transaksiCount,
                'total_pendapatan' => $totalPendapatanHarian,
            ]
        ]);
    }
}
