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
    /**
     * Bulk import guru dari API
     */
    public function bulkImport(Request $request)
    {
        $tahun = $request->query('tahun', 2025);

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
        $updated = [];

        DB::transaction(function () use ($gurusApi, &$created, &$skipped, &$updated) {

            foreach ($gurusApi as $guruData) {

                // Pastikan key ada (biar nggak error kalau API berubah)
                $guruId = $guruData['guru_id'] ?? null;
                $email  = $guruData['email'] ?? null;

                if (!$guruId || !$email) {
                    $skipped[] = [
                        'guru_id' => $guruId,
                        'reason' => 'guru_id atau email tidak valid'
                    ];
                    continue;
                }

                $user = User::where('guru_id', $guruId)->first();

                // === JIKA BELUM ADA → CREATE BARU ===
                if (!$user) {

                    $plainPassword = Str::random(8);

                    $user = User::create([
                        'name' => $guruData['nama'] ?? 'Tanpa Nama',
                        'email' => $email,
                        'password' => Hash::make($plainPassword),
                        'role' => 'guru',
                        'guru_id' => $guruId,
                        'nip' => $guruData['nip'] ?? null,
                        'nuptk' => $guruData['nuptk'] ?? null,
                        'jenis_kelamin' => $guruData['jenis_kelamin'] ?? null,
                    ]);

                    $created[] = [
                        'guru_id' => $guruId,
                        'email' => $email,
                        'password' => $plainPassword
                    ];

                    continue;
                }

                // === JIKA SUDAH ADA → UPDATE DATA (SYNC) ===
                $user->update([
                    'name' => $guruData['nama'] ?? $user->name,
                    'email' => $email,
                    'nip' => $guruData['nip'] ?? $user->nip,
                    'nuptk' => $guruData['nuptk'] ?? $user->nuptk,
                    'jenis_kelamin' => $guruData['jenis_kelamin'] ?? $user->jenis_kelamin,
                ]);

                $updated[] = [
                    'guru_id' => $guruId,
                    'email' => $email
                ];
            }
        });

        return response()->json([
            'message' => 'Bulk import guru selesai',
            'created_count' => count($created),
            'updated_count' => count($updated),
            'skipped_count' => count($skipped),
            'created' => $created,
            'updated' => $updated,
            'skipped' => $skipped
        ]);
    }

    /**
     * GET /api/admin/guru
     * List semua guru (gabungan database + API)
     */
    public function index(Request $request)
    {
        $tahun = $request->query('tahun', 2025);

        $response = Http::get("https://zieapi.zielabs.id/api/getguru", ['tahun' => $tahun]);

        if ($response->failed()) {
            return response()->json([
                'message' => 'Gagal memanggil API Guru',
                'status' => $response->status(),
                'body' => $response->body()
            ], 500);
        }

        $gurusApi = collect($response->json());
        $users = User::where('role', 'guru')->get();

        $data = $users->map(function ($user) use ($gurusApi) {
            $guruData = $gurusApi->firstWhere('guru_id', $user->guru_id);

            return [
                'id' => $user->id,
                'email' => $user->email,
                'guru_id' => $user->guru_id,
                'name' => $user->name ?? ($guruData['nama'] ?? null),
                'nuptk' => $user->nuptk ?? ($guruData['nuptk'] ?? null),
                'nip' => $user->nip ?? ($guruData['nip'] ?? null),
                'jenis_kelamin' => $user->jenis_kelamin ?? ($guruData['jenis_kelamin'] ?? null),
                'created_at' => $user->created_at->format('Y-m-d H:i:s')
            ];
        });

        return response()->json([
            'message' => 'List user guru',
            'data' => $data
        ]);
    }

    /**
     * POST /api/admin/guru
     * Tambah guru baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'nip' => 'nullable|string|max:50',
            'nuptk' => 'nullable|string|max:50',
            'jenis_kelamin' => 'nullable|in:L,P'
        ]);

        $guruId = (string) Str::uuid();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'guru',
            'guru_id' => $guruId,
            'nip' => $validated['nip'] ?? null,
            'nuptk' => $validated['nuptk'] ?? null,
            'jenis_kelamin' => $validated['jenis_kelamin'] ?? null,
        ]);

        return response()->json([
            'message' => 'User guru berhasil ditambahkan',
            'data' => $user
        ], 201);
    }

    /**
     * GET /api/admin/guru/{guru_id}
     * Detail guru berdasarkan UUID
     */
    public function show($guru_id)
    {
        $guru = User::where('role', 'guru')->where('guru_id', $guru_id)->first();

        if (!$guru) {
            return response()->json([
                'message' => 'Guru dengan UUID ini tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'message' => 'Detail user guru',
            'data' => $guru
        ]);
    }


    /**
     * PUT /api/admin/guru/{guru_id}
     * Update guru berdasarkan UUID
     */
    public function update(Request $request, $guru_id)
    {
        $guru = User::where('role', 'guru')->where('guru_id', $guru_id)->first();

        if (!$guru) {
            return response()->json([
                'message' => 'Guru dengan UUID ini tidak ditemukan'
            ], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:users,email,' . $guru->id,
            'password' => 'nullable|min:6',
            'nip' => 'nullable|string|max:50',
            'nuptk' => 'nullable|string|max:50',
            'jenis_kelamin' => 'nullable|in:L,P'
        ]);

        if (isset($validated['name'])) $guru->name = $validated['name'];
        if (isset($validated['email'])) $guru->email = $validated['email'];
        if (!empty($validated['password'])) $guru->password = Hash::make($validated['password']);
        if (array_key_exists('nip', $validated)) $guru->nip = $validated['nip'];
        if (array_key_exists('nuptk', $validated)) $guru->nuptk = $validated['nuptk'];
        if (array_key_exists('jenis_kelamin', $validated)) $guru->jenis_kelamin = $validated['jenis_kelamin'];

        $guru->save();

        return response()->json([
            'message' => 'User guru berhasil diupdate',
            'data' => $guru
        ]);
    }


    /**
     * DELETE /api/admin/guru/{guru_id}
     * Hapus guru berdasarkan UUID
     */
    public function destroy($guru_id)
    {
        $guru = User::where('role', 'guru')->where('guru_id', $guru_id)->firstOrFail();
        $guru->delete();

        return response()->json([
            'message' => 'User guru berhasil dihapus'
        ]);
    }
}
