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
        // Paksa Laravel menganggap ini request JSON agar jika validasi gagal, return-nya JSON
        $request->headers->set('Accept', 'application/json');

        // Gunakan Validator manual agar kita bisa menangkap error-nya dengan pasti
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'guru_id'         => 'required',
            'face_descriptor' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors()
            ], 422);
        }

        try {
            return DB::transaction(function () use ($request) {
                // Cari user berdasarkan guru_id. 
                // PASTIKAN kolom 'guru_id' benar-benar ada di tabel 'users'
                $user = User::where('guru_id', $request->guru_id)->first();

                if (!$user) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Guru dengan ID ' . $request->guru_id . ' tidak ditemukan di tabel users.'
                    ], 404);
                }

                $descriptor = $request->face_descriptor;

                // Jika dari JS dikirim lewat JSON.stringify, biasanya dia masuk sebagai string
                if (is_string($descriptor)) {
                    $descriptor = json_decode($descriptor, true);
                }

                if (!is_array($descriptor)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Format descriptor tidak valid (harus array).'
                    ], 400);
                }

                $faceProfile = FaceProfile::updateOrCreate(
                    ['guru_id' => $user->guru_id],
                    [
                        'face_descriptor' => json_encode($descriptor),
                    ]
                );

                return response()->json([
                    'success' => true,
                    'message' => 'Registrasi wajah ' . $user->name . ' berhasil!',
                ], 200);
            });
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error Server: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getAllFaceProfiles()
    {
        // Gunakan join atau eager loading untuk mengambil nama dari tabel users
        $profiles = \App\Models\FaceProfile::join('users', 'face_profiles.guru_id', '=', 'users.guru_id')
            ->select('face_profiles.guru_id', 'face_profiles.face_descriptor', 'users.name')
            ->get();

        return response()->json($profiles);
    }
}
