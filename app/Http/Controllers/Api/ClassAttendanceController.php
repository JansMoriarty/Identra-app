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

        // Ambil waktu dari SERVER (Laptop/Hosting)
        $sekarang = now();
        $jamSekarang = $sekarang->toTimeString();
        $hariIni = $sekarang->locale('id')->isoFormat('dddd');
        $tanggalHariIni = $sekarang->toDateString();

        // Memberikan toleransi 15 menit sebelum jadwal dimulai
        // Contoh: Jadwal jam 21.00, jam 20.45 sudah bisa scan.
        $jamToleransiMulai = $sekarang->copy()->addMinutes(15)->toTimeString();

        // 1. Cari kelas berdasarkan QR
        $classroom = Classroom::where('qr_code', $qrScanned)->first();

        if (!$classroom) {
            return response()->json(['message' => 'QR Code Ruangan tidak terdaftar!'], 404);
        }

        // 2. Cek jadwal aktif dengan toleransi waktu
        $jadwal = Schedule::with('subject')
            ->where('user_id', $userId)
            ->where('classroom_id', $classroom->id)
            ->where('day', $hariIni)
            ->where('start_time', '<=', $jamToleransiMulai) // Pakai toleransi agar tidak kaku
            ->where('end_time', '>=', $jamSekarang)       // Belum melewati jam selesai
            ->first();

        if (!$jadwal) {
            return response()->json([
                'success' => false,
                'message' => 'Maaf Pak, saat ini Anda tidak memiliki jadwal aktif di ' . $classroom->name . '. (Jam Server: ' . substr($jamSekarang, 0, 5) . ')',
            ], 403);
        }

        // Ambil nama mata pelajaran
        $namaMapel = $jadwal->subject->name ?? 'Mata Pelajaran';

        // 3. CEK DOUBLE ABSEN
        $sudahAbsen = ClassAttendance::where('user_id', $userId)
            ->where('subject_name', $namaMapel)
            ->where('class_code', $qrScanned)
            ->where('tanggal', $tanggalHariIni)
            ->exists();

        if ($sudahAbsen) {
            return response()->json([
                'success' => false,
                'message' => 'Pak, Anda sudah melakukan absensi untuk mata pelajaran ' . $namaMapel . ' di kelas ini hari ini.'
            ], 422);
        }

        // 4. Simpan data ke tabel class_attendances
        ClassAttendance::create([
            'user_id'      => $userId,
            'subject_name' => $namaMapel,
            'class_code'   => $qrScanned,
            'tanggal'      => $tanggalHariIni,
            'jam_masuk'    => $jamSekarang,
            'metode'       => 'qr_code',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Absensi Berhasil! Selamat mengajar ' . $namaMapel . ' di ' . $classroom->name
        ]);
    }
}
