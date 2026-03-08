<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClassAttendance;
use App\Models\Classroom;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ClassAttendanceController extends Controller
{
    public function store(Request $request)
    {
        $request->validate(['class_code' => 'required']);

        $userId = $request->user()->id;
        $qrScanned = $request->class_code;
        $jamSekarang = now()->toTimeString();
        $hariIni = now()->locale('id')->isoFormat('dddd');
        $tanggalHariIni = now()->toDateString();

        // 1. Cari kelas berdasarkan QR
        $classroom = Classroom::where('qr_code', $qrScanned)->first();

        if (!$classroom) {
            return response()->json(['message' => 'QR Code Ruangan tidak terdaftar!'], 404);
        }

        // 2. Cek jadwal: Harus cocok User, Classroom, Hari, DAN Jam sekarang harus di antara start & end
        $jadwal = Schedule::where('user_id', $userId)
            ->where('classroom_id', $classroom->id)
            ->where('day', $hariIni)
            ->where('start_time', '<=', $jamSekarang) // Jam mulai sudah lewat atau sedang berlangsung
            ->where('end_time', '>=', $jamSekarang)   // Jam selesai belum lewat
            ->first();

        if (!$jadwal) {
            return response()->json([
                'success' => false,
                'message' => 'Maaf Pak, saat ini Anda tidak memiliki jadwal aktif di ' . $classroom->name . '. (Jadwal terdeteksi: ' . $hariIni . ')',
            ], 403);
        }

        // 3. CEK DOUBLE ABSEN: Biar nggak bisa scan berkali-kali di kelas yang sama pada hari yang sama
        $sudahAbsen = ClassAttendance::where('user_id', $userId)
            ->where('class_code', $qrScanned)
            ->where('tanggal', $tanggalHariIni)
            ->exists();

        if ($sudahAbsen) {
            return response()->json([
                'success' => false,
                'message' => 'Pak, Anda sudah melakukan absensi masuk di kelas ini tadi.'
            ], 422);
        }

        // 4. Jika semua pagar lolos, simpan data
        ClassAttendance::create([
            'user_id' => $userId,
            'class_code' => $qrScanned,
            'tanggal' => $tanggalHariIni,
            'jam_masuk' => $jamSekarang,
            'metode' => 'qr_code',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Absensi Berhasil! Selamat mengajar di ' . $classroom->name
        ]);
    }
}
