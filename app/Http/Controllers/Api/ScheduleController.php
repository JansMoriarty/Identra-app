<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ScheduleController extends Controller
{
    /**
     * Display a listing of the schedules.
     */
    public function index()
    {
        // Mengambil jadwal beserta relasi guru, kelas, dan mapel
        $schedules = Schedule::with(['guru', 'classroom', 'subject'])->latest()->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar Jadwal Pelajaran',
            'data'    => $schedules
        ], 200);
    }

    /**
     * Store a newly created schedule in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id'      => 'required',
            'classroom_id' => 'required',
            'subject_id'   => 'required',
            'day'          => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
            'start_time'   => 'required',
            'end_time'     => 'required|after:start_time',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $schedule = Schedule::create([
            'user_id'      => $request->user_id,
            'classroom_id' => $request->classroom_id,
            'subject_id'   => $request->subject_id,
            'day'          => $request->day,
            'start_time'   => $request->start_time,
            'end_time'     => $request->end_time,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Jadwal berhasil ditambahkan',
            'data'    => $schedule->load(['guru', 'classroom', 'subject'])
        ], 201);
    }

    /**
     * Remove the specified schedule from storage.
     */
    public function destroy($id)
    {
        $schedule = Schedule::find($id);

        if (!$schedule) {
            return response()->json([
                'success' => false,
                'message' => 'Jadwal tidak ditemukan'
            ], 404);
        }

        $schedule->delete();

        return response()->json([
            'success' => true,
            'message' => 'Jadwal berhasil dihapus'
        ], 200);
    }

    public function getTodaySchedule($guru_id)
    {
        // 1. Cari dulu User ID (Integer) berdasarkan guru_id (UUID)
        $user = \App\Models\User::where('guru_id', $guru_id)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Guru tidak ditemukan',
                'data' => []
            ], 404);
        }

        // 2. Translate hari (DB pakai Bahasa Indonesia)
        $days = [
            'Sunday' => 'Minggu',
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu'
        ];
        $todayInIndo = $days[now()->format('l')];
        $tanggalHariIni = now()->toDateString(); // Contoh: 2026-03-08

        // 3. Ambil jadwal
        $schedules = \App\Models\Schedule::with(['subject', 'classroom'])
            ->where('user_id', $user->id)
            ->where('day', $todayInIndo)
            ->orderBy('start_time', 'asc')
            ->get();

        // 4. Format data DAN Cek Status Absen (KUNCI WARNA HIJAU)
        $formattedData = $schedules->map(function ($item) use ($user, $tanggalHariIni) {
            // --- LOGIKA PENGECEKAN KE TABEL ABSENSI ---
            $isAttended = \App\Models\ClassAttendance::where('user_id', $user->id)
                ->where('subject_name', $item->subject->name)
                ->where('class_code', $item->classroom->qr_code) // Cocokkan dengan QR Ruangan
                ->where('tanggal', $tanggalHariIni)
                ->exists(); // Hasilnya true jika ada data, false jika tidak ada

            return [
                'subject_name'   => $item->subject->name ?? 'Mata Pelajaran',
                'classroom_name' => $item->classroom->name ?? 'Kelas',
                'start_time'     => \Carbon\Carbon::parse($item->start_time)->format('H:i'),
                'end_time'       => \Carbon\Carbon::parse($item->end_time)->format('H:i'),
                'day'            => $item->day,
                'is_attended'    => $isAttended, // <--- FIELD BARU UNTUK FLUTTER
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => $formattedData
        ]);
    }
}
