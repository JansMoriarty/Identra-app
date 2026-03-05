<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Http\Resources\AttendanceResource; // <--- WAJIB IMPORT INI
use Illuminate\Http\Request;
use Carbon\Carbon;

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
}
