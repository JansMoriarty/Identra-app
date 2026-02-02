<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdminGuruController extends Controller
{
    public function bulkImport(Request $request)
    {
        $tahun = $request->query('tahun', 2025);

        // 1️⃣ Ambil data guru dari API
        $response = Http::get("https://zieapi.zielabs.id/api/getguru", [
            'tahun' => $tahun
        ]);

        if ($response->failed()) {
            return response()->json([
                'message' => 'Gagal memanggil API Guru',
                'status' => $response->status()
            ], 500);
        }

        $gurusApi = collect($response->json());

        $created = [];
        $skipped = [];

        // 2️⃣ Mulai transaction supaya aman
        DB::transaction(function () use ($gurusApi, &$created, &$skipped) {

            $toInsert = [];

            foreach ($gurusApi as $guruData) {

                // Cek apakah sudah ada
                $exists = User::where('guru_id', $guruData['guru_id'])
                    ->orWhere('email', $guruData['email'])
                    ->exists();

                if ($exists) {
                    $skipped[] = $guruData['guru_id'];
                    continue;
                }

                // Password random
                $password = Str::random(8);

                $toInsert[] = [
                    'name' => $guruData['nama'],
                    'email' => $guruData['email'],
                    'password' => Hash::make($password),
                    'role' => 'guru',
                    'guru_id' => $guruData['guru_id'],
                    'created_at' => now(),
                    'updated_at' => now(),
                    'plain_password' => $password // opsional, buat tracking
                ];

                $created[] = [
                    'guru_id' => $guruData['guru_id'],
                    'email' => $guruData['email'],
                    'password' => $password
                ];
            }

            // 3️⃣ Bulk insert → lebih cepat
            if (!empty($toInsert)) {
                // Hapus field plain_password sebelum insert ke DB asli kalau gak ada kolomnya
                foreach ($toInsert as &$item) unset($item['plain_password']);
                User::insert($toInsert);
            }
        });

        return response()->json([
            'message' => 'Bulk import guru selesai',
            'created_count' => count($created),
            'skipped_count' => count($skipped),
            'created' => $created,
            'skipped' => $skipped
        ]);
    }

    // GET /api/admin/guru
    public function index(Request $request)
    {
        $tahun = $request->query('tahun', 2025); // default 2025

        // 1) Ambil semua guru dari API
        $response = Http::get("https://zieapi.zielabs.id/api/getguru", [
            'tahun' => $tahun
        ]);

        if ($response->failed()) {
            return response()->json([
                'message' => 'Gagal memanggil API Guru',
                'status' => $response->status(),
                'body' => $response->body()
            ], 500);
        }

        $gurusApi = collect($response->json());

        // 2) Ambil semua users dengan role guru
        $users = User::where('role', 'guru')->get();

        // 3) Gabungkan data users + API Guru
        $data = $users->map(function ($user) use ($gurusApi) {
            $guruData = $gurusApi->firstWhere('guru_id', $user->guru_id);

            return [
                'id' => $user->id,
                'email' => $user->email,
                'guru_id' => $user->guru_id,
                'name' => $guruData['nama'] ?? $user->name,
                'nuptk' => $guruData['nuptk'] ?? null,
                'nip' => $guruData['nip'] ?? null,
                'jenis_kelamin' => $guruData['jenis_kelamin'] ?? null,
                'created_at' => $user->created_at->format('Y-m-d H:i:s')
            ];
        });

        return response()->json([
            'message' => 'List user guru',
            'data' => $data
        ]);
    }

    // POST /api/admin/guru
public function store(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|min:6',
        'tahun' => 'sometimes|integer'
    ]);

    // ✅ 1) Generate UUID otomatis untuk guru_id
    $guruId = (string) Str::uuid();

    // ✅ 2) Buat user guru di DB lokal
    $user = User::create([
        'name' => $validated['name'],
        'email' => $validated['email'],
        'password' => Hash::make($validated['password']),
        'role' => 'guru',
        'guru_id' => $guruId,
    ]);

    return response()->json([
        'message' => 'User guru berhasil ditambahkan',
        'data' => [
            'user' => $user->only(['id', 'name', 'email', 'role', 'guru_id'])
        ]
    ], 201);
}


    // GET /api/admin/guru/{id}
    public function show($id)
    {
        $guru = User::where('role', 'guru')->findOrFail($id);

        return response()->json([
            'message' => 'Detail user guru',
            'data' => $guru
        ]);
    }

    // PUT /api/admin/guru/{id}
    public function update(Request $request, $id)
    {
        $guru = User::where('role', 'guru')->findOrFail($id);

        $validated = $request->validate([
            'email' => 'sometimes|required|email|unique:users,email,' . $guru->id,
            'password' => 'nullable|min:6',
            'guru_id' => 'sometimes|required|uuid',
            'tahun' => 'sometimes|integer' // opsional, default 2025
        ]);

        $tahun = $validated['tahun'] ?? 2025;

        // 🔹 Jika guru_id diubah, ambil data guru dari API
        if (isset($validated['guru_id'])) {
            $response = Http::get("https://zieapi.zielabs.id/api/getguru", [
                'tahun' => $tahun
            ]);

            if ($response->failed()) {
                return response()->json([
                    'message' => 'Gagal memanggil API Guru',
                    'status' => $response->status(),
                    'body' => $response->body()
                ], 500);
            }

            $gurusApi = collect($response->json());
            $guruData = $gurusApi->firstWhere('guru_id', $validated['guru_id']);

            if (!$guruData) {
                return response()->json([
                    'message' => 'Guru tidak ditemukan di API Guru'
                ], 404);
            }

            // Update nama otomatis dari API Guru
            $guru->name = $guruData['nama'];
            $guru->guru_id = $validated['guru_id'];
        }

        // 🔹 Update field lain
        if (isset($validated['email'])) {
            $guru->email = $guruData['email'];
        }

        if (!empty($validated['password'])) {
            $guru->password = Hash::make($validated['password']);
        }

        $guru->save();

        return response()->json([
            'message' => 'User guru berhasil diupdate',
            'data' => [
                'user' => $guru->only(['id', 'name', 'email', 'role', 'guru_id']),
                'guru_api' => $guruData ?? null
            ]
        ]);
    }

    // DELETE /api/admin/guru/{id}
    public function destroy($id)
    {
        $guru = User::where('role', 'guru')->findOrFail($id);
        $guru->delete();

        return response()->json([
            'message' => 'User guru berhasil dihapus'
        ]);
    }
}
