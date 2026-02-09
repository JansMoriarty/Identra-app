<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Attendance;
use App\Http\Resources\AttendanceResource; // <--- WAJIB TAMBAHKAN INI
use Illuminate\Http\Request;
use Carbon\Carbon;

class AttendanceWebController extends Controller
{
    public function index(Request $request)
    {
        // 1. Ambil range tanggal, default-nya dari H-7 sampai hari ini
        $startDate = $request->get('start_date') ?? Carbon::now()->subDays(7)->format('Y-m-d');
        $endDate = $request->get('end_date') ?? Carbon::now()->format('Y-m-d');

        // 2. Ambil data dengan whereBetween agar masuk ke rentang tanggal
        $attendances = Attendance::with('guru')
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->orderBy('tanggal', 'desc') // Supaya tanggal terbaru di atas
            ->get();

        $attendancesFormatted = AttendanceResource::collection($attendances)->resolve();

        // 3. Rekap tetap dihitung dari total data yang terfilter
        $rekap = [
            'total' => User::where('role', 'guru')->count(),
            'hadir' => $attendances->whereIn('status', ['hadir', 'telat'])->count(),
            'izin'  => $attendances->whereIn('status', ['izin', 'sakit'])->count(),
            'alpha' => $attendances->where('status', 'alpha')->count(),
        ];

        return view('pages.attendances.index', [
            'attendances' => $attendancesFormatted,
            'startDate'   => $startDate,
            'endDate'     => $endDate,
            'rekap'       => $rekap
        ]);
    }
}
