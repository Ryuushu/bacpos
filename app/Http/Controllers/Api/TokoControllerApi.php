<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Toko;
use Illuminate\Http\Request;
use App\Http\Resources\TokoResource;
use App\Models\DetailTransaksi;
use App\Models\Produk;
use App\Models\Transaksi;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
        try {
            // Get the logged-in user and join with pemilik to get id_pemilik
            $pemilik = Auth::user()->pemilik; // Assuming user has a relationship with pemilik

            if (!$pemilik) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Pemilik not found for the logged-in user.',
                ], 404);
            }

            $validator = $request->validate([
                'nama_toko' => 'required|string|max:100',
                'alamat_toko' => 'required|string|max:200',
                'whatsapp' => 'nullable|string|max:20',
                'instagram' => 'nullable|string|max:50',
                'url_img' => 'nullable|image|mimes:jpeg,png,jpg|max:2048', // Validate image
            ]);



            $urlImg = null;
            if ($request->hasFile('url_img')) {
                $file = $request->file('url_img');
                $fileName = uniqid() . '_' . time() . '.' . $file->getClientOriginalExtension();
                $urlImg = $file->move("uploadfile/toko/", $fileName);
            } else {
                $urlImg = null; // No image uploaded
            }

            $toko = Toko::create([
                'id_pemilik' => $pemilik->id_pemilik,  // Assign the pemilik's id
                'nama_toko' => $request->nama_toko,
                'alamat_toko' => $request->alamat_toko,
                'whatsapp' => $request->whatsapp,
                'instagram' => $request->instagram,
                'url_img' => $urlImg,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Toko created successfully.',
                'data' => new TokoResource($toko)
            ], 201);
        } catch (\Exception $e) {
            Log::error("Error updating produk: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Error updating data.',
                'errors' => $e->getMessage()
            ], 500);
        }
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
        try {
            // Get the logged-in user and join with pemilik to get id_pemilik
            $pemilik = Auth::user()->pemilik; // Assuming user has a relationship with pemilik

            if (!$pemilik) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Pemilik not found for the logged-in user.',
                ], 404);
            }

            $validator = $request->validate([
                'nama_toko' => 'required|string|max:100',
                'alamat_toko' => 'required|string|max:200',
                'whatsapp' => 'nullable|string|max:20',
                'instagram' => 'nullable|string|max:50',
                'url_img' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Validate image
            ]);

            $toko = Toko::where('id_pemilik', $pemilik->id_pemilik)->where('id_toko', $id)->first();

            if (!$toko) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Toko not found or you do not have permission to update this toko.',
                ], 404);
            }

            $urlImg = $toko->url_img; // Keep the existing image if no new one is uploaded
            if ($request->hasFile('url_img')) {
                $file = $request->file('url_img');

                $oldPath = public_path($toko->url_img);
                if ($toko->url_img && file_exists($oldPath)) { // Perbaiki kurung tutup
                    unlink($oldPath);
                }
                // Simpan file baru dengan nama unik
                $fileName = uniqid() . '_' . time() . '.' . $file->getClientOriginalExtension();
                $urlImg = $file->move("uploadfile/toko/", $fileName);
            }

            $toko->update([
                'nama_toko' => $request->nama_toko,
                'alamat_toko' => $request->alamat_toko,
                'whatsapp' => $request->whatsapp,
                'instagram' => $request->instagram,
                'url_img' => $urlImg,
            ]);

            return (new TokoResource($toko))
                ->additional([
                    'status' => 'success',
                    'message' => 'Toko updated successfully',
                ]);
        } catch (\Exception $e) {
            Log::error("Error updating produk: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Error updating data.',
                'errors' => $e->getMessage()
            ], 500);
        }
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
        $oldPath = public_path($toko->url_img);
        if (is_file($oldPath) && file_exists($oldPath)) { // Perbaiki kurung tutup
            unlink($oldPath);
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
        $produkCount = Produk::where('id_toko', $idtoko)->count();
        $today = now()->toDateString(); // Format YYYY-MM-DD
        $currentMonth = now()->format('Y-m'); // Format YYYY-MM untuk bulan ini

        $transaksiCount = Transaksi::where('id_toko', $idtoko)
            ->whereDate('created_at', $today)
            ->count();

        $totalPendapatanHarian = Transaksi::where('id_toko', $idtoko)
            ->whereDate('created_at', $today)
            ->sum('totalharga');

        $totalPendapatanBulanan = Transaksi::where('id_toko', $idtoko)
            ->where('created_at', 'like', "$currentMonth%")
            ->sum('totalharga');

        $topProdukBulanan = DetailTransaksi::where('id_toko', $idtoko)
            ->where('created_at', 'like', "$currentMonth%")
            ->select('kode_produk', DB::raw('SUM(jumlah) as total_terjual'))
            ->groupBy('kode_produk')
            ->orderByDesc('total_terjual')
            ->limit(5)
            ->get();

        if ($produkCount === 0) {
            $produkCount = 0; // Jika tidak ada produk, set menjadi 0
        }
        if ($transaksiCount === 0) {
            $transaksiCount = 0; // Jika tidak ada transaksi, set menjadi 0
        }
        if ($totalPendapatanHarian === null) {
            $totalPendapatanHarian = 0; // Jika tidak ada pendapatan, set menjadi 0
        }
        if ($totalPendapatanBulanan === null) {
            $totalPendapatanBulanan = 0; // Jika tidak ada pendapatan, set menjadi 0
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Data successful',
            'data' => [
                'produk_count' => $produkCount,
                'transaksi_count' => $transaksiCount,
                'total_pendapatan_harian' => $totalPendapatanHarian,
                'total_pendapatan_bulanan' => $totalPendapatanBulanan,
                'top_produk_bulanan' => $topProdukBulanan,
            ]
        ]);
    }
}
