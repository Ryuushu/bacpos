<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DetailTransaksi;
use App\Models\KartuStok;
use App\Models\Produk;
use App\Models\Toko;
use Illuminate\Http\Request;
use App\Models\Transaksi;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransaksiControllerApi extends Controller
{
    public function store(Request $request)
    {
        // Validasi input
        $validated = $request->validate([
            'id_toko' => 'required|integer|exists:toko,id_toko', // Pastikan id_toko ada di tabel tokos
            'id_user' => 'required|integer|exists:users,id_user',
            'items' => 'required|array',
            'items.*.kode_produk' => 'required|integer|exists:produk,kode_produk',
            'items.*.qty' => 'required|integer|min:1',
            'bayar' => 'required|integer|min:1',
            'jenis_pembayaran' => 'required',
            'ppn' => 'nullable',
            'bulatppn' => 'nullable',
            'valuediskon' => 'nullable|numeric|min:0', // Validasi diskon
            'tipediskon' => 'nullable|string|in:persen,nominal',
        ]);
       

        // Generate ID transaksi
        $idTransaksi = 'TRX-' . $validated['id_toko'] . now()->format('dmYHis') . rand(1000, 9999);

        // Mulai transaksi database
        DB::beginTransaction();

        try {
            // Simpan data transaksi
            $transaksi = Transaksi::create([
                'id_transaksi' => $idTransaksi,
                'id_toko' => $validated['id_toko'],
                'id_user' => $validated['id_user'],
                'totalharga' => 0,
                'pembayaran' => $validated['bayar'],
                'kembalian' => 0,
                'jenis_pembayaran' => $validated['jenis_pembayaran'],
                'ppn' => $validated['ppn'],
                'bulatppn' => $validated['bulatppn'],
                'valuediskon' => $validated['valuediskon'],
                'tipediskon' => $validated['tipediskon'],
                'created_at' => now()->format('Y-m-d H:i:s'),
            ]);
        
            $totalHarga = 0;
            $itemsDetails = [];

            // Loop setiap item untuk menghitung harga dan menyimpan detail transaksi
            foreach ($validated['items'] as $item) {
                $produk = Produk::findOrFail($item['kode_produk']);
                $harga = $produk->harga;
                $subtotal = $harga * $item['qty'];
        
                // Validasi stok jika is_stock_managed
                if ($produk->is_stock_managed && $produk->stok < $item['qty']) {
                    DB::rollBack();
                    return response()->json(['message' => 'Stok produk ' . $produk->nama_produk . ' tidak cukup'], 400);
                }
        
                // Simpan detail transaksi
                DetailTransaksi::create([
                    'id_transaksi' => $idTransaksi,
                    'kode_produk' => $item['kode_produk'],
                    'harga' => $harga,
                    'qty' => $item['qty'],
                    'subtotal' => $subtotal,
                    'created_at' => now()->format('Y-m-d H:i:s'),
                ]);
        
                // Tambahkan subtotal ke total harga
                $totalHarga += $subtotal;
        
                // Simpan detail untuk response
                $itemsDetails[] = [
                    'kode_produk' => $item['kode_produk'],
                    'nama_produk' => $produk->nama_produk,
                    'qty' => $item['qty'],
                    'harga' => $harga,
                    'subtotal' => $subtotal,
                ];
            }
        
            // **Perhitungan Diskon**
            $totalSetelahDiskon = $totalHarga;
            if ($validated['valuediskon']) {
                if ($validated['tipediskon'] === 'persen') {
                    $diskon = ($validated['valuediskon'] / 100) * $totalHarga;
                } else { // Nominal
                    $diskon = $validated['valuediskon'];
                }
                $totalSetelahDiskon -= $diskon;
            } else {
                $diskon = 0;
            }
        
            // **Perhitungan PPN**
            $ppnValue = $validated['ppn'] ?? 0;
            $ppnAmount = ($ppnValue / 100) * $totalSetelahDiskon;
        
            // **Total Akhir**
            $calculatedTotalAkhir = $totalSetelahDiskon + $ppnAmount;
        
            // **Hitung Kembalian**
            $kembalian = $validated['bayar'] - $calculatedTotalAkhir;
        
            // **Update Total Harga di Transaksi**
            $transaksi->update([
                'totalharga' => $calculatedTotalAkhir,
                'kembalian' => $kembalian,
            ]);


            $user = User::with(['pekerja', 'pemilik'])->find($validated['id_user']);
            $toko = Toko::find($validated['id_toko']);


            $userInfo = [
                'id_user' => $user->id_user,
                'nama' => $user->pekerja ? $user->pekerja->nama_pekerja : ($user->pemilik ? $user->pemilik->nama_pemilik : null),
                'posisi' => $user->pekerja ? "pekerja" : 'Pemilik',
            ];
            if ($toko->url_img) {
                $path = public_path($toko->url_img);

                if (file_exists($path)) {
                    $imageData = base64_encode(file_get_contents($path));
                    $mimeType = mime_content_type($path);
                    $base64Image = $imageData;
                }
            }
            $tokoInfo = $toko ? [
                'id_toko' => $toko->id_toko,
                'nama_toko' => $toko->nama_toko,
                'alamat_toko' => $toko->alamat_toko,
                'whatsapp' => $toko->whatsapp,
                'instagram' => $toko->instagram,
                'img' => $toko->url_img != null ? $base64Image : null,
                'mime' => $toko->url_img != null ? $mimeType : null
            ] : null;

            // Commit transaksi jika semua berhasil
            DB::commit();

            return response()->json([
                'message' => 'Checkout berhasil',
                'id_transaksi' => $idTransaksi,
                'subtotal'=>$totalHarga,
                'ppn'=>$validated["ppn"],
                'bulatppn'=>$validated["bulatppn"],
                'totalharga' => $calculatedTotalAkhir,
                'pembayaran' => $validated['bayar'],
                'jenis_pembayaran' => $transaksi->jenis_pembayaran,
                'kembalian' => $transaksi->kembalian,
                'created_at' => $transaksi->created_at,
                'user' => $userInfo,
                'toko' => $tokoInfo,
                'items' => $itemsDetails,
            ]);
        } catch (\Exception $e) {
            Log::error('Transaksi gagal: ' . $e->getMessage(), [
                'exception' => $e,
                'user_id' => auth()->id_user ?? null, // Opsional, simpan ID user jika ada
            ]);
            // Rollback transaksi jika terjadi error
            DB::rollBack();
            return response()->json([
                'message' => 'Terjadi kesalahan, transaksi dibatalkan.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function riwayat($id_toko, Request $request)
{
    // Validasi parameter StartDate dan EndDate
    $validated = $request->validate([
        'start_date' => 'nullable|date|before_or_equal:end_date',
        'end_date' => 'nullable|date|after_or_equal:start_date',
    ]);

    // Ambil parameter StartDate dan EndDate dari request
    $startDate = $validated['start_date'] ?? Carbon::now()->toDateString();
    $endDate = $validated['end_date'] ?? Carbon::now()->toDateString();

    // Query transaksi dengan filter tanggal jika diberikan
    $transaksiQuery = Transaksi::with('toko', 'user.pekerja', 'user.pemilik', 'detailTransaksi.produk')
        ->where('id_toko', $id_toko)
        ->whereBetween('created_at', [
            $startDate . ' 00:00:00',
            $endDate . ' 23:59:59'
        ])
        ->orderBy('created_at', 'desc'); // Urutkan dari terbaru ke terlama

    $transaksi = $transaksiQuery->get();

    // Mengelompokkan transaksi berdasarkan tanggal (urut dari terbaru)
    $transaksiGrouped = $transaksi->groupBy(function ($item) {
        return Carbon::parse($item->created_at)->format('Y-m-d');
    })->sortKeysDesc(); // Mengurutkan tanggal dari terbaru ke terlama

    // Menambahkan total per grup
    $transaksiWithSum = $transaksiGrouped->map(function (Collection $group) {
        $total = $group->sum('totalharga');
        return [
            'total' => $total,
            'data' => $group,
        ];
    });

    return response()->json([
        'message' => 'Data transaksi berhasil diambil',
        'data' => $transaksiWithSum,
    ]);
}

}
