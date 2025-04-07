<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Toko;
use Illuminate\Http\Request;
use App\Http\Resources\TokoResource;
use App\Models\Produk;
use App\Models\Transaksi;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\ImageManager;

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

        $toko = Toko::where('id_pemilik', $pemilik->id_pemilik)->where('is_verified',1)->with('pemilik')->get();
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
                'url_img' => 'nullable|image|mimes:jpeg,png,jpg|max:5120', // Validate image
            ]);

            $urlImg = null;
            if ($request->hasFile('url_img')) {
                $manager = new ImageManager(new GdDriver());
                $file = $request->file('url_img');
                $fileName = uniqid() . '_' . time() . '.' . $file->getClientOriginalExtension();
                $imagePath = public_path("uploadfile/toko/" . $fileName);
                $image = $manager->read($file);
                $image->toWebp(60)->save($imagePath);
                $urlImg = "uploadfile/toko/" . $fileName;
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
            Log::error("Error storing toko: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Error storing data.',
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
                'url_img' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120', // Validate image
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
                $manager = new ImageManager(new GdDriver());
                $file = $request->file('url_img');
                $fileName = uniqid() . '_' . time() . '.' . $file->getClientOriginalExtension();
                $imagePath = public_path("uploadfile/toko/" . $fileName);
                $image = $manager->read($file);
                $image->toWebp(60)->save($imagePath);
                $urlImg = "uploadfile/toko/" . $fileName;
                $oldPath = public_path($toko->url_img);
                if ($toko->url_img && file_exists($oldPath)) { // Perbaiki kurung tutup
                    unlink($oldPath);
                }
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
    try {
        // Get the logged-in user and join with pemilik to get id_pemilik
        $pemilik = Auth::user()->pemilik; // Assuming user has a relationship with pemilik

        if (!$pemilik) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pemilik not found for the logged-in user.',
            ], 404);
        }

        // Cari toko berdasarkan id_pemilik dan id_toko
        $toko = Toko::where('id_pemilik', $pemilik->id_pemilik)
                    ->where('id_toko', $id)
                    ->first();

        if (!$toko) {
            return response()->json([
                'status' => 'error',
                'message' => 'Toko not found or you do not have permission to delete this toko.',
            ], 404);
        }

        DB::beginTransaction(); // Mulai transaksi

        // Simpan path gambar sebelum delete
        $oldPath = public_path($toko->url_img);

        // Hapus toko dari database
        $toko->delete();

        DB::commit(); // Konfirmasi transaksi database

        // Setelah commit, baru hapus file
        if (is_file($oldPath)) {
            unlink($oldPath);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Toko deleted successfully.',
        ]);
    } catch (\Exception $e) {
        DB::rollBack(); // Batalkan transaksi jika terjadi error
        Log::error("Error deleting toko: " . $e->getMessage());
        return response()->json([
            'status' => 'error',
            'message' => 'Error deleting data.',
            'errors' => $e->getMessage()
        ], 500);
    }
}
    public function dashboardtoko($idtoko)
    {
        $toko = Toko::where('id_toko', $idtoko)->first();
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

        $topProdukAll = Produk::where('id_toko', $idtoko)
            ->withSum('detailTransaksi as total_qty', 'qty')
            ->having('total_qty', '>', 0) // Filter quantity lebih dari 0
            ->orderByDesc('total_qty')  // Urutkan berdasarkan total_qty terbesar
            ->limit(5)
            ->get(['kode_produk', 'nama_produk', 'id_toko']);

        $topProdukBulanan = Produk::where('id_toko', $idtoko)
            ->whereHas('detailTransaksi', function ($query) {
                $query->whereMonth('created_at', date('m'))
                    ->whereYear('created_at', date('Y'));
            })
            ->withSum(['detailTransaksi as total_qty' => function ($query) {
                $query->whereMonth('created_at', date('m'))
                    ->whereYear('created_at', date('Y'));
            }], 'qty')
            ->having('total_qty', '>', 0)
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get(['kode_produk', 'nama_produk', 'id_toko']);

        if ($produkCount === 0) {
            $produkCount = 0; // Jika tidak ada produk, set menjadi 0
        }
        if ($transaksiCount === 0) {
            $transaksiCount = 0; // Jika tidak ada transaksi, set menjadi 0
        }
        if ($totalPendapatanHarian === null) {
            $totalPendapatanHarian = 0; // Jika tidak ada pendapatan, set menjadi 0
        }
        if ($topProdukAll === null) {
            $topProdukAll = 0; // Jika tidak ada pendapatan, set menjadi 0
        }
        if ($totalPendapatanBulanan === null) {
            $totalPendapatanBulanan = 0; // Jika tidak ada pendapatan, set menjadi 0
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Data successful',
            'data' => [
                'toko' => new TokoResource($toko),
                'produk_count' => $produkCount,
                'transaksi_count' => $transaksiCount,
                'total_pendapatan_harian' => $totalPendapatanHarian,
                'total_pendapatan_bulanan' => $totalPendapatanBulanan,
                'top_produk_all' => $topProdukAll,
                'top_produk_bulanan' => $topProdukBulanan,
            ]
        ]);
    }
}
