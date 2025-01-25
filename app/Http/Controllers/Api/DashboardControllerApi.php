<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Toko;
use App\Models\Produk;
use App\Models\Transaksi;

class DashboardControllerApi extends Controller
{
    public function index(Request $request)
    {
        // Ambil data pengguna yang sedang login
        $user = $request->user();

        // Pastikan pengguna adalah pemilik
        if ($user->role !== 'pemilik') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Ambil data toko milik pemilik
        $toko = Toko::where('id_pemilik', $user->pemilik->id_pemilik)->get();

        if (!$toko) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data Tidak Ada',
                'data' => [
                    'produk_count' => "0",
                    'transaksi_count' => "0",
                    'total_pendapatan' => "Rp.0",
                ]
            ], 404);
        }

       
        return response()->json([
            'status' => 'success',
            'message' => 'Data successful',
            'data' => $toko
        ]);
    }
    public function listtokobypemilik(Request $request)
    {
        // Ambil data pengguna yang sedang login
        $user = $request->user();

        // Pastikan pengguna adalah pemilik
        if ($user->role !== 'pemilik') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Ambil data toko milik pemilik
        $toko = Toko::where('id_pemilik', $user->pemilik->id_pemilik)->first();

        if (!$toko) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data Tidak Ada',
                'data' => []
            ], 404);
        }

        // Data yang akan ditampilkan di dashboard
      
        return response()->json([
            'status' => 'success',
            'message' => 'Data successful',
            'data' => $toko
        ]);
    }
}
