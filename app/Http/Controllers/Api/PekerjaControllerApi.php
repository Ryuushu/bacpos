<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pekerja;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class PekerjaControllerApi extends Controller
{
    /**
     * Create a new Pekerja.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_toko' => 'required|exists:toko,id_toko',
            'nama_pekerja' => 'required|string|max:100',
            'alamat_pekerja' => 'required|string|max:100',
            'email' => 'required|string|email|max:50|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'password_confirmation' => 'required|string|min:6',
        ]);

        try {
            $user = User::create([
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'pekerja',
            ]);
            $pekerja = Pekerja::create([
                'id_toko' => $request->id_toko,
                'id_user' => $user->id_user,
                'nama_pekerja' => $request->nama_pekerja,
                'alamat_pekerja' => $request->alamat_pekerja,
            ]);
            return response()->json([
                'status' => 'success',
                'message' => 'Pekerja created successfully.',
                'data' => $pekerja
            ], 201);
        } catch (\Exception $e) {
            Log::error("Error storing pekerja: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Error storing data.',
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all Pekerja.
     */
    public function index()
    {
        $pekerja = Pekerja::with('user')->get();
        return response()->json([
            'status' => 'success',
            'message' => 'Fetched all workers.',
            'data' => $pekerja
        ], 200);
    }

    /**
     * Show a single Pekerja.
     */
    public function show($id)
    {
        $pekerja = Pekerja::with(['user'])->where('id_toko',$id)->get();

        if (!$pekerja) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pekerja not found.',
                'errors' => 'No pekerja found with the given id.'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Pekerja found.',
            'data' => $pekerja
        ], 200);
    }

    /**
     * Update a Pekerja.
     */
    public function update(Request $request, $id)
    {
       
        $validated = $request->validate([
            'id_toko' => 'required|exists:toko,id_toko',
            'nama_pekerja' => 'sometimes|string|max:100',
            'alamat_pekerja' => 'sometimes|string|max:100',
            'password' => $request->filled('password') ? 'min:6|confirmed' : '',
        ]);
        
        try {
            $pekerja = Pekerja::find($id);
            if (!$pekerja) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Pekerja not found.',
                    'errors' => 'No pekerja found with the given id.'
                ], 404);
            }
        
            $user = User::find($pekerja->id_user);
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not found.',
                ], 404);
            }
        
            // Validasi email setelah mendapatkan id_user
            $validated['email'] = $request->validate([
                'email' => 'sometimes|string|email|max:50|unique:users,email,'.$pekerja->id_user.',id_user',
            ])['email'] ?? $user->email;
          
            $user->email = $validated['email'];
            if (!empty($validated['password'])) {
                $user->password = Hash::make($validated['password']);
            }
            $user->save();
            $pekerja->update([
                'id_toko' => $validated['id_toko'],
                'nama_pekerja' => $validated['nama_pekerja'],
                'alamat_pekerja' => $validated['alamat_pekerja'],
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Pekerja updated successfully.',
                'data' => $pekerja
            ], 200);
        } catch (\Exception $e) {
            Log::error("Error updating pekerja: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Error updating data.',
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a Pekerja.
     */
    public function destroy($id)
    {
        try {
            $pekerja = Pekerja::find($id);
            $user = User::find($pekerja->id_user);
            if (!$pekerja) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Pekerja not found.',
                    'errors' => 'No pekerja found with the given id.'
                ], 404);
            }

            $pekerja->delete();
            $user->delete();
            return response()->json([
                'status' => 'success',
                'message' => 'Pekerja deleted successfully.'
            ], 200);
        } catch (\Exception $e) {
            Log::error("Error deleting pekerja: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Error deleting data.',
                'errors' => $e->getMessage()
            ], 500);
        }
    }
}
