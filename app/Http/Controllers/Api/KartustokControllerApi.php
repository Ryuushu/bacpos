<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DetailTransaksi;
use App\Models\KartuStok;
use App\Models\Produk;
use Illuminate\Http\Request;
use App\Models\Transaksi;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class KartuStokControllerApi extends Controller
{
    public function shows($kodep, $type)
    {
        $query = KartuStok::with("produk")->where("kode_produk", $kodep);

        // Add condition based on type
        if ($type !== 'all') {
            $query->where('jenis_transaksi', $type); // Assuming the 'type' column exists in the KartuStok model
        }

        $data = $query->orderBy('tanggal','DESC')->get();
        return response()->json([
            'message' => 'Kartu stok berhasil',
            'data' => $data,
        ]);
    }
}
