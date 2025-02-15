<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\OtpMail;
use App\Models\Otp;
use App\Models\Pemilik;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Str;

class AuthControllerApi extends Controller
{
    // Register
    public function register(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'nama_pemilik' => 'required|string|max:100',
                'email' => 'required|string|email|max:50|unique:users,email',
                'password' => 'required|string|min:6|confirmed',
                'password_confirmation' => 'required|string|min:6',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation errors',
                    'errors' => $validator->errors()
                ], 422);
            }
            $user = User::create([
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'pemilik',
            ]);
            $pemilik = Pemilik::create([
                'nama_pemilik' => $request->nama_pemilik,
                'id_user' => $user->id_user
            ]);

            // $otp = rand(100000, 999999);
            // // Menghasilkan OTP acak 6 digit
            // Otp::create([
            //     'id_user' => $user->id_user,
            //     'otp' => $otp,
            //     'expires_at' => Carbon::now()->addMinutes(5),  // OTP berlaku selama 5 menit
            // ]);

            // // Kirim OTP ke email pengguna
            // Mail::to($request->email)->send(new OtpMail($otp));
            return response()->json([
                'status' => 'success',
                'message' => 'Registration successful',
                'data' => $user
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

    // Login
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'identifier' => 'required|string', // Bisa email, nama pekerja, atau nama pemilik
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->identifier)
            ->orWhereHas('pekerja', function ($query) use ($request) {
                $query->where('nama_pekerja', $request->identifier);
            })
            ->orWhereHas('pemilik', function ($query) use ($request) {
                $query->where('nama_pemilik', $request->identifier);
            })
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid credentials'
            ], 401);
        }

        $token = $user->createToken('mobpos')->plainTextToken;

        $pekerja = $user->pekerja;
        $pemilik = $user->pemilik;

        return response()->json([
            'status' => 'success',
            'message' => 'Login successful',
            'data' => [
                'user' => $user,
                'token' => $token,
                'pekerja' => $pekerja, // Tambahkan data pekerja
                'pemilik' => $pemilik, // Tambahkan data pemilik
            ]
        ], 200);
    }


    // Logout
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Logged out successfully',
        ], 200);
    }
    // Verifikasi OTP
    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'otp' => 'required|string|min:6|max:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $otpRecord = Otp::where('otp', $request->otp)
            ->where('expires_at', '>', Carbon::now())  // Pastikan OTP belum kedaluwarsa
            ->first();

        if (!$otpRecord) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid or expired OTP.'
            ], 400);
        }

        // OTP valid, verifikasi pengguna
        $user = User::find($otpRecord->id_user);

        $user->is_verified = true;
        $success = $user->save();
        if (!$success) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update user verification status.',
            ], 500);
        }

        // Hapus OTP setelah verifikasi
        $otpRecord->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'OTP verified successfully.',
        ], 200);
    }

    // Lupa Password
    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:50|exists:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        // Kirim link reset password
        // $token = Str::random(60);
        // $user->password_reset_token = $token;
        // $user->password_reset_token_expiry = Carbon::now()->addMinutes(30);  // Token berlaku selama 30 menit
        // $user->save();

        // // Kirim email dengan link reset password
        // Mail::to($request->email)->send(new PasswordResetMail($token));

        return response()->json([
            'status' => 'success',
            'message' => 'Password reset link has been sent to your email.',
        ], 200);
    }

    // Reset Password
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('password_reset_token', $request->token)
            ->where('password_reset_token_expiry', '>', Carbon::now())  // Pastikan token belum kedaluwarsa
            ->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid or expired token.'
            ], 400);
        }

        // Reset password
        $user->password = Hash::make($request->password);
        $user->password_reset_token = null;  // Hapus token reset password
        $user->password_reset_token_expiry = null;
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Password has been reset successfully.',
        ], 200);
    }
}
