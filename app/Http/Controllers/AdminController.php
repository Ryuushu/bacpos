<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pelanggan;
use App\Models\Pemilik;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    public function index()
    {

        $totalPelanggan = Pemilik::count(); // Hitung total pelanggan

        return view('dashboard', compact('totalPelanggan'));
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
        return view('dashboard', compact('pemilik', 'totalPelanggan'));
    }
    public function create(Request $request)
    {

            $request->validate([
                'nama_pemilik' => 'required|string|max:255|unique:pemilik,nama_pemilik',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|min:6|confirmed:konfirmasi_password',
            ], [
                'email.unique' => 'Email sudah terdaftar!',
                'password.confirmed' => 'Konfirmasi password tidak sesuai!',
            ]);

            
            $user = User::create([
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'pemilik',
                'is_verified' => true
            ]);
            $pemilik = Pemilik::create([
                'nama_pemilik' => $request->nama_pemilik,
                'id_user' => $user->id_user
            ]);


            return view('dashboard')->with('success', 'Pemilik berhasil ditambahkan.');
       
    }
}
