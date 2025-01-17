<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\KartuStok;
use App\Models\Pekerja;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class StokControllerApi extends Controller
{
    public function index()
    {
        $pekerja = Pekerja::with('user')->get();
        return response()->json([
            'status' => 'success',
            'message' => 'Fetched all workers.',
            'data' => $pekerja
        ], 200);
    }

    public function show($id)
    {
        $kartuStoks = KartuStok::join('produk', 'produk.kode_produk', '=', 'kartu_stok.kode_produk')
                       ->join('toko', 'toko.id_toko', '=', 'produk.id_toko')
                       ->where('toko.id_toko', $id)
                       ->get();

        if (!$kartuStoks) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pekerja not found.',
                'errors' => 'No pekerja found with the given id.'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Pekerja found.',
            'data' => $kartuStoks
        ], 200);
    }

}
