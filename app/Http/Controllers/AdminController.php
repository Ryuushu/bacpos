<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pelanggan;
use App\Models\Pemilik;
use App\Models\User;

class AdminController extends Controller
{
    public function index()
    {

        $totalPelanggan = Pemilik::count(); // Hitung total pelanggan

        return view('dashboard', compact( 'totalPelanggan'));
    }
    public function search(Request $request)
    {
        $query = $request->input('query');

        // Perform the search query
        // In your controller or wherever you're performing the query
        $totalPelanggan = Pemilik::count();
        $pemilik = Pemilik::with('user')
            ->where('nama_pemilik', 'like', '%' . $query . '%') // Use $query for searching `nama_pemilik`
            ->orWhereHas('user', function ($q) use ($query) { // Use $query for searching `user.email`
                $q->where('email', 'like', '%' . $query . '%');
            })
            ->get();


        // Return the search result to the view
        return view('dashboard', compact('pemilik','totalPelanggan'));
    }
}
