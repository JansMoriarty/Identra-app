<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FaceProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class FaceRecognitionController extends Controller
{
    public function registerFace(Request $request)
    {
        $request->validate([
            'guru_id' => 'required|exists:users,guru_id', // BERUBAH: validasi ke kolom uuid
            'image'   => 'required|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        try {
            return DB::transaction(function () use ($request) {
                // BERUBAH: Cari user berdasarkan kolom guru_id (UUID)
                $user = User::where('guru_id', $request->guru_id)->firstOrFail();

                $file = $request->file('image');

                // 1. Simpan Foto ke Storage
                // Gunakan $user->guru_id di nama file agar lebih jelas
                $fileName = 'face_' . $user->guru_id . '_' . time() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('face_profiles', $fileName, 'public');
                $fullPath = storage_path('app/public/' . $path);

                // 2. Eksekusi Node.js AI
                $nodeScript = base_path('face-processor/process.js');
                $command = "node \"$nodeScript\" \"$fullPath\" 2>&1";
                $output = shell_exec($command);

                $result = json_decode($output, true);

                // 3. Validasi Hasil AI
                if (!$result || isset($result['error']) || !is_array($result)) {
                    Storage::disk('public')->delete($path);
                    return response()->json([
                        'success' => false,
                        'message' => 'Gagal mendeteksi wajah: ' . ($result['error'] ?? 'Foto tidak jelas'),
                        'debug'   => $output
                    ], 422);
                }

                // 4. Simpan ke Database
                // Tetap pakai $user->id (77) untuk relasi tabel face_profiles
                $faceProfile = FaceProfile::updateOrCreate(
                    ['guru_id' => $user->guru_id], // ✅ BENAR (UUID)
                    [
                        'image_path'      => $path,
                        'face_descriptor' => $result,
                    ]
                );


                return response()->json([
                    'success' => true,
                    'message' => 'Registrasi wajah ' . $user->name . ' berhasil!', // Ganti ke $user->nama sesuai response JSON kamu
                    'data'    => [
                        'image_url' => asset('storage/' . $path)
                    ]
                ], 200);
            });
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Server Error: ' . $e->getMessage()
            ], 500);
        }
    }
}
