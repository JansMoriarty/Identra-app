<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendanceRule;
use App\Http\Resources\AttendanceResource;
use App\Models\User;
use App\Services\IntegrityWalletService;
use App\Models\Location;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    // Tampilkan riwayat absen (Untuk Flutter Index)
    public function index(Request $request)
    {
        $user = $request->user();
        $attendances = Attendance::where('guru_id', $user->guru_id)
            ->orderBy('tanggal', 'desc')
            ->limit(30)
            ->get();

        // Menggunakan Resource::collection untuk banyak data
        return response()->json([
            'success' => true,
            'message' => 'Daftar riwayat absensi',
            'data' => AttendanceResource::collection($attendances)
        ]);
    }

    // Fungsi Absen Masuk (Manual)
    public function store(Request $request)
    {
        $request->validate([
            'guru_id'    => 'required',
            'status'     => 'required|in:hadir,izin,sakit,alpha',
            'keterangan' => 'nullable|string',
        ]);

        $hariIni = Carbon::now()->format('Y-m-d');
        $jamSekarang = Carbon::now()->format('H:i:s');
        $batasMasuk = '12:40:00';

        $existing = Attendance::where('guru_id', $request->guru_id)
            ->where('tanggal', $hariIni)
            ->first();

        // Tentukan status berdasarkan jam jika inputnya adalah 'hadir'
        $statusInput = $request->status;
        if ($statusInput === 'hadir' && $jamSekarang > $batasMasuk) {
            $statusInput = 'telat';
        }

        // LOGIKA UPDATE JIKA SUDAH ADA (Alpha to Hadir/Izin)
        if ($existing) {
            // Jika statusnya bukan Alpha, baru kita tolak (biar gak double hadir/pulang)
            if ($existing->status !== 'alpha') {
                return response()->json(['message' => 'Guru sudah melakukan absensi (' . $existing->status . ')'], 422);
            }

            // Jika sebelumnya Alpha, kita timpa datanya
            $existing->update([
                'jam_masuk'  => ($statusInput === 'hadir' || $statusInput === 'telat') ? $jamSekarang : null,
                'status'     => $statusInput,
                'metode'     => 'manual',
                'keterangan' => $request->keterangan,
            ]);

            return response()->json([
                'message' => 'Status Alpha berhasil diperbarui',
                'data' => new AttendanceResource($existing)
            ]);
        }

        // JIKA BELUM ADA DATA SAMA SEKALI
        $attendance = Attendance::create([
            'guru_id'    => $request->uuid ?? $request->guru_id,
            'tanggal'    => $hariIni,
            'jam_masuk'  => ($statusInput === 'hadir' || $statusInput === 'telat') ? $jamSekarang : null,
            'status'     => $statusInput,
            'metode'     => 'manual',
            'keterangan' => $request->keterangan,
        ]);

        return response()->json([
            'message' => 'Absensi berhasil disimpan',
            'data' => new AttendanceResource($attendance)
        ]);
    }

    // Fungsi Absen Pulang
    public function checkout(Request $request)
    {
        $request->validate([
            'guru_id' => 'required',
        ]);

        $hariIni = Carbon::now()->format('Y-m-d');
        $jamSekarang = Carbon::now()->format('H:i:s');

        $attendance = Attendance::where('guru_id', $request->guru_id)
            ->where('tanggal', $hariIni)
            ->first();

        if (!$attendance) {
            return response()->json(['message' => 'Data absen masuk tidak ditemukan'], 404);
        }

        if ($attendance->jam_pulang) {
            return response()->json(['message' => 'Sudah melakukan absen pulang'], 422);
        }

        $attendance->update([
            'jam_pulang' => $jamSekarang
        ]);

        return response()->json([
            'message' => 'Berhasil absen pulang',
            'data' => new AttendanceResource($attendance) // <--- Bungkus dengan Resource
        ]);
    }

    public function getAttendanceToday($guru_id)
    {
        $hariIni = \Carbon\Carbon::now()->format('Y-m-d');

        $attendance = \App\Models\Attendance::where('guru_id', $guru_id)
            ->where('tanggal', $hariIni)
            ->first();

        if (!$attendance) {
            return response()->json([
                'success' => true,
                'message' => 'Belum ada data absen hari ini',
                'data' => null
            ]);
        }

        // Menggunakan Resource yang kamu buat tadi
        return response()->json([
            'success' => true,
            'data' => new \App\Http\Resources\AttendanceResource($attendance)
        ]);
    }

    public function scanFace(Request $request)
    {
        try {
            // 1. Gunakan Validator::make agar tidak terjadi auto-redirect jika validasi gagal
            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
                'guru_id' => 'required',
                'status'  => 'required|in:hadir,pulang',
                'latitude' => 'nullable',
                'longitude' => 'nullable'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal: ' . implode(', ', $validator->errors()->all())
                ], 422);
            }

            $guruId = $request->guru_id;
            $hariIni = \Carbon\Carbon::now()->format('Y-m-d');
            $jamSekarang = \Carbon\Carbon::now()->format('H:i:s');

            // 2. LOGIKA ABSEN PULANG
            if ($request->status === 'pulang') {
                $jamPulangMin = \App\Models\AttendanceRule::getValue('jam_pulang', '14:00:00');

                $attendance = \App\Models\Attendance::where('guru_id', $guruId)
                    ->where('tanggal', $hariIni)
                    ->first();

                if (!$attendance) {
                    return response()->json(['success' => false, 'message' => 'Anda belum absen masuk hari ini!'], 422);
                }
                if ($attendance->jam_pulang) {
                    return response()->json(['success' => false, 'message' => 'Anda sudah absen pulang.'], 422);
                }
                if ($jamSekarang < $jamPulangMin) {
                    return response()->json(['success' => false, 'message' => 'Belum waktunya pulang. Minimal jam ' . $jamPulangMin], 422);
                }

                $attendance->update(['jam_pulang' => $jamSekarang]);

                return response()->json([
                    'success' => true,
                    'message' => 'Berhasil Absen Pulang!',
                    'data' => new \App\Http\Resources\AttendanceResource($attendance)
                ]);
            }

            // 3. LOGIKA ABSEN MASUK
            $batasMasuk = \App\Models\AttendanceRule::getValue('batas_masuk', '08:00:00');
            $statusInput = ($jamSekarang > $batasMasuk) ? 'telat' : 'hadir';

            $existing = \App\Models\Attendance::where('guru_id', $guruId)
                ->where('tanggal', $hariIni)
                ->first();

            if ($existing && $existing->status !== 'alpha') {
                return response()->json(['success' => false, 'message' => 'Anda sudah absen masuk hari ini.'], 422);
            }

            $attendance = \App\Models\Attendance::updateOrCreate(
                ['guru_id' => $guruId, 'tanggal' => $hariIni],
                [
                    'jam_masuk' => $jamSekarang,
                    'status'    => $statusInput,
                    'metode'    => 'face',
                    'keterangan' => 'Absensi via Face Recognition Kiosk',
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Absensi Masuk Berhasil! Status: ' . ucfirst($statusInput),
                'data' => new \App\Http\Resources\AttendanceResource($attendance)
            ]);
        } catch (\Throwable $e) {
            // JIKA ADA ERROR CODING/DATABASE, TANGKAP DAN KIRIM SEBAGAI JSON
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server!',
                'debug' => $e->getMessage(), // Hapus ini jika sudah masuk tahap produksi
                'line' => $e->getLine()
            ], 500);
        }
    }

    public function verifyAndStore(Request $request)
    {
        // 1. VALIDASI INPUT
        // Kita pastikan captured_embedding dan koordinat ada. user_token_id opsional.
        $request->validate([
            'guru_id'            => 'required',
            'captured_embedding' => 'required|array',
            'latitude'           => 'required',
            'longitude'          => 'required',
            'user_token_id'      => 'nullable|exists:user_tokens,id',
        ]);

        $hariIni = \Carbon\Carbon::now()->format('Y-m-d');
        $jamSekarang = \Carbon\Carbon::now()->format('H:i:s');
        $walletService = new \App\Services\IntegrityWalletService();

        // 2. CEK GEOFENCING (Wajib bagi semua absen non-WFH)
        if (!$this->isWithinGeofence($request->latitude, $request->longitude)) {
            return response()->json(['success' => false, 'message' => 'Luar radius lokasi!'], 403);
        }

        // 3. VERIFIKASI USER & WAJAH
        $user = \App\Models\User::where('guru_id', $request->guru_id)->first();
        if (!$user) return response()->json(['message' => 'User tidak ditemukan!'], 404);

        $faceProfile = \App\Models\FaceProfile::where('user_id', $user->id)->first();
        if (!$faceProfile) return response()->json(['message' => 'Registrasi wajah diperlukan!'], 404);

        // Verifikasi Wajah (Threshold Euclidean Distance 0.85)
        $registeredFace = is_array($faceProfile->face_descriptor)
            ? $faceProfile->face_descriptor
            : json_decode($faceProfile->face_descriptor, true);

        $distance = 0.0;
        for ($i = 0; $i < count($registeredFace); $i++) {
            $distance += pow($registeredFace[$i] - $request->captured_embedding[$i], 2);
        }

        if (sqrt($distance) > 0.85) {
            return response()->json(['message' => 'Wajah tidak cocok!'], 401);
        }

        // 4. AMBIL ATURAN JAM KERJA
        $batasMasuk = \App\Models\AttendanceRule::getValue('batas_masuk', '08:00:00');
        $jamPulangMin = \App\Models\AttendanceRule::getValue('jam_pulang', '14:00:00');

        $attendance = \App\Models\Attendance::where('guru_id', $user->guru_id)
            ->where('tanggal', $hariIni)
            ->first();

        // -----------------------------------------------------------
        // SKENARIO 1: ABSEN MASUK
        // -----------------------------------------------------------
        if (!$attendance || $attendance->status === 'alpha') {
            $statusFinal = 'hadir';
            $isTokenUsed = false;
            $tokenInfo = null;
            $tokenModel = null;

            // Cek apakah terlambat
            if ($jamSekarang > $batasMasuk) {
                $lateMinutes = \Carbon\Carbon::parse($jamSekarang)->diffInMinutes(\Carbon\Carbon::parse($batasMasuk));

                // Cek penggunaan Late Waver Voucher
                if ($request->user_token_id) {
                    $tokenModel = \App\Models\UserToken::with('item')
                        ->where('id', $request->user_token_id)
                        ->where('user_id', $user->id)
                        ->where('status', 'AVAILABLE')
                        ->whereHas('item', fn($q) => $q->where('item_type', 'LATE_WAVER'))
                        ->first();

                    // Validasi: Token harus ada dan value_power (menit) cukup menutupi keterlambatan
                    if ($tokenModel && $tokenModel->item->value_power >= $lateMinutes) {
                        $statusFinal = 'telat_kompensasi';
                        $isTokenUsed = true;
                        $tokenInfo = $tokenModel->item->item_name;

                        // Update Status Token
                        $tokenModel->update([
                            'status' => 'USED',
                            'used_at' => \Carbon\Carbon::now()
                        ]);
                    } else {
                        $statusFinal = 'telat';
                    }
                } else {
                    $statusFinal = 'telat';
                }
            }

            // Simpan data absensi
            $newAttendance = \App\Models\Attendance::updateOrCreate(
                ['guru_id' => $user->guru_id, 'tanggal' => $hariIni],
                [
                    'jam_masuk'        => $jamSekarang,
                    'status'           => $statusFinal,
                    'metode'           => 'face',
                    'is_token_applied' => $isTokenUsed,
                    'token_info'       => $tokenInfo,
                    'latitude'         => $request->latitude,
                    'longitude'        => $request->longitude,
                    'keterangan'       => $isTokenUsed ? "Absen via Token ($tokenInfo)" : "Absen Masuk via Face",
                ]
            );

            // --- RULE ENGINE POIN ---
            // Poin CHECK_IN diproses hanya jika TIDAK memakai token kompensasi telat
            if (!$isTokenUsed) {
                $walletService->processPoints($user->id, $newAttendance->id, 'CHECK_IN', $jamSekarang);
            } else if ($tokenModel) {
                // Catat ID attendance pada token untuk audit trail
                $tokenModel->update(['used_at_attendance_id' => $newAttendance->id]);
            }

            return response()->json([
                'success' => true,
                'message' => $isTokenUsed ? "Absen Berhasil (Voucher Digunakan)" : "Absensi Masuk Berhasil!",
                'data' => [
                    'nama' => $user->name,
                    'status' => $statusFinal,
                    'jam' => $jamSekarang,
                    'is_late' => ($jamSekarang > $batasMasuk) && !$isTokenUsed
                ]
            ], 200);
        }

        // -----------------------------------------------------------
        // SKENARIO 2: ABSEN PULANG
        // -----------------------------------------------------------
        if ($attendance && !$attendance->jam_pulang) {
            if ($jamSekarang < $jamPulangMin) {
                return response()->json([
                    'message' => "Belum waktu pulang. Jam pulang minimal: " . substr($jamPulangMin, 0, 5)
                ], 422);
            }

            $attendance->update([
                'jam_pulang' => $jamSekarang,
                'keterangan' => $attendance->keterangan . ' | Absen Pulang via Face'
            ]);

            // Proses poin CHECK_OUT
            $walletService->processPoints($user->id, $attendance->id, 'CHECK_OUT', $jamSekarang);

            return response()->json([
                'success' => true,
                'message' => 'Absensi Pulang Berhasil!',
                'data' => ['nama' => $user->name, 'jam' => $jamSekarang]
            ], 200);
        }

        return response()->json(['message' => 'Anda sudah menyelesaikan absensi hari ini.'], 422);
    }

    private function isWithinGeofence($userLat, $userLng)
    {
        $locations = Location::all();
        $earthRadius = 6371000;

        foreach ($locations as $location) {
            $latFrom = deg2rad($userLat);
            $lonFrom = deg2rad($userLng);
            $latTo = deg2rad($location->latitude);
            $lonTo = deg2rad($location->longitude);

            $latDelta = $latTo - $latFrom;
            $lonDelta = $lonTo - $lonFrom;

            $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
                cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

            $distance = $angle * $earthRadius;

            if ($distance <= $location->radius) {
                return true; // Berhenti dan kembalikan true kalau ketemu satu lokasi yang cocok
            }
        }

        return false;
    }

    public function getActiveRule()
    {
        // Ambil aturan yang sedang aktif (misal jam masuk 07:00)
        // Jika nama tabelmu berbeda, sesuaikan query-nya
        $rule = AttendanceRule::where('is_active', true)->first();

        if (!$rule) {
            return response()->json(['message' => 'Rule tidak ditemukan'], 404);
        }

        return response()->json([
            'success' => true,
            'target_time' => $rule->start_time, // Mengirim string "07:00:00"
        ]);
    }
}
