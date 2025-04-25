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
        $validated = $request->validate([
            'id_toko' => 'required|integer|exists:toko,id_toko',
            'id_user' => 'required|integer|exists:users,id_user',
            'items' => 'required|array',
            'items.*.kode_produk' => 'required|integer|exists:produk,kode_produk',
            'items.*.qty' => 'required|integer|min:1',
            'bayar' => 'required|integer|min:1',
            'jenis_pembayaran' => 'required',
            'ppn' => 'nullable',
            'bulatppn' => 'nullable',
            'valuediskon' => 'nullable|numeric|min:0',
            'tipediskon' => 'nullable|string|in:persen,nominal',
        ]);

        $idTransaksi = 'TRX-' . $validated['id_toko'] . now()->format('dmYHis') . rand(1000, 9999);
        DB::beginTransaction();

        try {
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
                'created_at' => now(),
            ]);

            $totalHarga = 0;
            $itemsDetails = [];

            foreach ($validated['items'] as $item) {
                $produk = Produk::findOrFail($item['kode_produk']);
                $harga = $produk->harga;
                $subtotal = $harga * $item['qty'];

                if ($produk->is_stock_managed && $produk->stok < $item['qty']) {
                    DB::rollBack();
                    return response()->json(['message' => 'Stok produk ' . $produk->nama_produk . ' tidak cukup'], 400);
                }

                DetailTransaksi::create([
                    'id_transaksi' => $idTransaksi,
                    'kode_produk' => $item['kode_produk'],
                    'harga' => $harga,
                    'qty' => $item['qty'],
                    'subtotal' => $subtotal,
                    'created_at' => now(),
                ]);

                $totalHarga += $subtotal;
                $itemsDetails[] = [
                    'kode_produk' => $item['kode_produk'],
                    'nama_produk' => $produk->nama_produk,
                    'qty' => $item['qty'],
                    'harga' => $harga,
                    'subtotal' => $subtotal,
                ];
            }

            foreach ($validated['items'] as $item) {
                $produk = Produk::findOrFail($item['kode_produk']);
                if ($produk->is_stock_managed) {
                    $stokAwal = $produk->stok;
                    $produk->decrement('stok', $item['qty']);

                    KartuStok::create([
                        'kode_produk' => $item['kode_produk'],
                        'jenis_transaksi' => 'keluar',
                        'tanggal' => now(),
                        'jumlah' => $item['qty'],
                        'stok_awal' => $stokAwal,
                        'stok_akhir' => $produk->stok,
                        'keterangan' => 'Transaksi Penjualan, ID Transaksi: ' . $idTransaksi,
                    ]);
                }
            }

            $ppnAmount = ($validated['ppn'] ?? 0) / 100 * $totalHarga;
            $totalSetelahPPN = $totalHarga + $ppnAmount;

            $diskon = 0;
            if (!empty($validated['valuediskon'])) {
                $diskon = ($validated['tipediskon'] === 'persen') ? ($validated['valuediskon'] / 100 * $totalSetelahPPN) : $validated['valuediskon'];
            }

            $calculatedTotalAkhir = $totalSetelahPPN - $diskon;
            $kembalian = $validated['bayar'] - $calculatedTotalAkhir;

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

            $tokoInfo = $toko ? [
                'id_toko' => $toko->id_toko,
                'nama_toko' => $toko->nama_toko,
                'alamat_toko' => $toko->alamat_toko,
                'whatsapp' => $toko->whatsapp,
                'instagram' => $toko->instagram,
                'img' => $toko->url_img ? base64_encode(file_get_contents(public_path($toko->url_img))) : null,
                'mime' => $toko->url_img ? mime_content_type(public_path($toko->url_img)) : null,
            ] : null;

            DB::commit();

            return response()->json([
                'message' => 'Checkout berhasil',
                'id_transaksi' => $idTransaksi,
                'subtotal' => $totalHarga,
                'ppn' => $validated['ppn'],
                'bulatppn' => $validated['bulatppn'],
                'valuediskon' => $validated['valuediskon'],
                'tipediskon' => $validated['tipediskon'],
                'totalharga' => $calculatedTotalAkhir,
                'pembayaran' => $validated['bayar'],
                'jenis_pembayaran' => $transaksi->jenis_pembayaran,
                'kembalian' => $kembalian,
                'created_at' => $transaksi->created_at,
                'user' => $userInfo,
                'toko' => $tokoInfo,
                'detail_transaksi' => $itemsDetails,
            ]);
        } catch (\Exception $e) {
            Log::error('Transaksi gagal: ' . $e->getMessage(), ['exception' => $e, 'user_id' => auth()->id_user ?? null]);
            DB::rollBack();
            return response()->json(['message' => 'Terjadi kesalahan, transaksi dibatalkan.', 'error' => $e->getMessage()], 500);
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
