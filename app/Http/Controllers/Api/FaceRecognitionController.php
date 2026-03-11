<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FaceProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FaceRecognitionController extends Controller
{
    public function registerFace(Request $request)
    {
        // 1. Validasi Input
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'guru_id'         => 'required', // UUID dari Flutter
            'face_descriptor' => 'required', // Array 192 koordinat
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors()
            ], 422);
        }

        try {
            return \Illuminate\Support\Facades\DB::transaction(function () use ($request) {
                // 2. Cari User berdasarkan UUID (guru_id)
                $user = \App\Models\User::where('guru_id', $request->guru_id)->first();

                if (!$user) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Guru tidak ditemukan di sistem.'
                    ], 404);
                }

                // 3. Pastikan Descriptor dalam bentuk Array (antisipasi jika Flutter kirim String)
                $descriptor = $request->face_descriptor;
                if (is_string($descriptor)) {
                    $descriptor = json_decode($descriptor, true);
                }

                // 4. Validasi panjang array (AI Face Mobile biasanya 192 dimensi)
                if (!is_array($descriptor) || count($descriptor) !== 192) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Data wajah tidak valid. Harus berisi 192 koordinat AI.',
                        'count'   => is_array($descriptor) ? count($descriptor) : 0
                    ], 400);
                }

                // 5. Simpan ke Tabel face_profiles
                // PERBAIKAN: Gunakan user_id (integer) untuk pencarian di tabel ini
                \App\Models\FaceProfile::updateOrCreate(
                    ['user_id' => $user->id], // Primary key (1, 2, dst)
                    [
                        'face_descriptor' => $descriptor,
                        'image_path'      => $request->image_path ?? null,
                    ]
                );

                return response()->json([
                    'success' => true,
                    'message' => 'Profil wajah ' . $user->name . ' berhasil diperbarui!',
                ], 200);
            });
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem.',
                'debug'   => $e->getMessage()
            ], 500);
        }
    }
    public function getAllFaceProfiles()
    {
        // Mengambil data face profile beserta data user terkaitnya
        $profiles = FaceProfile::with('user:guru_id,name')->get();

        return response()->json([
            'success' => true,
            'data' => $profiles
        ]);
    }
}
