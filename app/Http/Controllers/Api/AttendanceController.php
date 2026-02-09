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
        $batasMasuk = '07:30:00';

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
}
