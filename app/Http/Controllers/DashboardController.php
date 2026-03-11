<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\ClassAttendance;
use App\Models\Classroom;
use App\Models\User;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http; // Tambahkan ini
use Illuminate\Support\Facades\Cache; // Tambahkan ini
use Carbon\Carbon; // Tambahkan ini
use App\Models\LeaveRequest;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Logika Waktu & Statistik (Bawaan Anda)
        $now = now()->locale('id');
        $hariIni = $now->isoFormat('dddd');
        $tanggalHariIni = $now->toDateString();
        $year = $now->year;
        $month = $now->month;

        $totalGuru = User::where('role', 'guru')->count();
        $totalGuruHadir = Attendance::whereDate('tanggal', $tanggalHariIni)->count();

        $data = [
            'totalGuru' => $totalGuru,
            'totalGuruHadir' => $totalGuruHadir,
            'totalRuangan' => Classroom::count(),
            'totalJadwalHariIni' => Schedule::where('day', $hariIni)->count(),
            'totalKelasTerisi' => ClassAttendance::whereDate('tanggal', $tanggalHariIni)->count(),
        ];

        $data['persentaseHadir'] = ($totalGuru > 0)
            ? round(($totalGuruHadir / $totalGuru) * 100)
            : 0;

        // 2. Logika Greeting
        $hour = $now->hour;
        $data['greeting'] = $hour < 12 ? 'Selamat Pagi' : ($hour < 15 ? 'Selamat Siang' : ($hour < 18 ? 'Selamat Sore' : 'Selamat Malam'));

        // 3. LOGIKA KALENDER & API LIBUR NASIONAL
        // Ambil data libur dari API (Gunakan Cache agar tidak berat saat load halaman)
        $allHolidays = Cache::remember("holidays_$year", 60 * 60 * 24, function () use ($year) {
            try {
                $response = Http::get("https://api-harilibur.vercel.app/api?year=$year");
                return $response->successful() ? $response->json() : [];
            } catch (\Exception $e) {
                return []; // Jika API down, kembalikan array kosong
            }
        });

        // Filter libur hanya untuk bulan ini
        $currentMonthHolidays = collect($allHolidays)->filter(function ($h) use ($month) {
            return Carbon::parse($h['holiday_date'])->month == $month;
        });

        // Simpan daftar tanggal libur (angka saja) untuk pengecekan di grid kalender
        $data['holidayDates'] = $currentMonthHolidays->map(function ($h) {
            return (int) Carbon::parse($h['holiday_date'])->format('d');
        })->toArray();

        // Simpan objek lengkap untuk daftar keterangan di bawah kalender
        $data['holidayList'] = $currentMonthHolidays;

        $data['todayLeaves'] = \App\Models\LeaveRequest::whereDate('tanggal_mulai', '<=', $tanggalHariIni)
            ->whereDate('tanggal_selesai', '>=', $tanggalHariIni)
            ->with('guru')
            ->limit(5)
            ->get()
            ->map(function ($leave) {
                return [
                    'id' => $leave->id,
                    'guru' => [
                        // Gunakan optional() atau null safe operator (?) agar tidak 500 error jika guru null
                        'name' => $leave->guru ? ($leave->guru->nama ?? $leave->guru->name) : 'Guru Tidak Ditemukan',
                    ],
                    'jenis' => $leave->jenis,
                    'status' => $leave->status,
                ];
            });

        // Kirim data ke view
        return view('pages.dashboard.ecommerce', [
            'totalGuru' => $totalGuru,
            'totalGuruHadir' => $totalGuruHadir,
            'persentaseHadir' => $data['persentaseHadir'],
            'totalRuangan' => Classroom::count(),
            'totalJadwalHariIni' => Schedule::where('day', $hariIni)->count(),
            'totalKelasTerisi' => ClassAttendance::whereDate('tanggal', $tanggalHariIni)->count(),
            'greeting' => $data['greeting'],
            'holidayDates' => $data['holidayDates'],
            'holidayList' => $data['holidayList'],
            'todayLeaves' => $data['todayLeaves'] // <--- Pastikan ini ada di sini
        ]);
    }
}
