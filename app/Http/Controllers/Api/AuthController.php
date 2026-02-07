<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // POST /api/login
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Email atau password salah'
            ], 401);
        }

        // Pastikan yang login adalah admin
        if (! $user->isAdmin()) {
            return response()->json([
                'message' => 'Hanya admin yang bisa login ke API ini'
            ], 403);
        }

        // Buat token Sanctum
        $token = $user->createToken('admin-token')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                ],
                'token' => $token
            ]
        ]);
    }

    // POST /api/guru/login
    public function loginGuru(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Ambil user beserta relasi jabatan aktifnya
        $user = User::with('activePosition')->where('email', $request->email)->first();

        // 1. Cek User & Password
        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Email atau password salah'
            ], 401);
        }

        // 2. Cek Role
        if (! $user->isGuru()) {
            return response()->json([
                'message' => 'Hanya akun guru yang bisa login di endpoint ini'
            ], 403);
        }

        // 3. Buat Token
        $token = $user->createToken('guru-mobile-token')->plainTextToken;

        // 4. Ambil Nama Jabatan (Safe Navigation)
        // Kita cek: apakah punya activePosition? Jika ya, apakah punya relasi position?
        $namaJabatan = 'Belum Ditugaskan';
        if ($user->activePosition && $user->activePosition->position) {
            $namaJabatan = $user->activePosition->position->nama_jabatan;
        }

        return response()->json([
            'message' => 'Login guru berhasil',
            'data' => [
                'user' => [
                    'id'            => $user->id,
                    'name'          => $user->name,
                    'email'         => $user->email,
                    'role'          => $user->role,
                    'guru_id'       => $user->guru_id,
                    'jabatan_aktif' => $namaJabatan, // Dikirim ke Flutter
                ],
                'token' => $token
            ]
        ]);
    }
    // POST /api/logout
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout berhasil'
        ]);
    }
}
