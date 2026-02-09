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

    public function manual()
    {
        // Kita ambil dari model User, cari yang rolenya guru, dan urutkan berdasarkan kolom 'name'
        $gurus = \App\Models\User::where('role', 'guru')
            ->orderBy('name', 'asc')
            ->get();

        // Pastikan path view sesuai dengan folder kamu (misal: pages.attendances.manual)
        return view('pages.attendances.manual', compact('gurus'));
    }

    public function store(Request $request)
    {
        // 1. Validasi: Arahkan ke tabel 'users' kolom 'guru_id'
        $request->validate([
            'guru_id'    => 'required|exists:users,guru_id', // Beritahu Laravel tabelnya 'users'
            'status'     => 'required|in:hadir,pulang',
            'keterangan' => 'nullable|string',
        ]);

        // Jika statusnya pulang, lempar ke fungsi checkout manual
        if ($request->status === 'pulang') {
            return $this->checkoutManual($request->guru_id);
        }

        $guruId = $request->guru_id;
        $hariIni = Carbon::now()->format('Y-m-d');
        $jamSekarang = Carbon::now()->format('H:i:s');
        $batasMasuk = '12:40:00';


        $statusInput = ($jamSekarang > $batasMasuk) ? 'telat' : 'hadir';

        $existing = Attendance::where('guru_id', $guruId)
            ->where('tanggal', $hariIni)
            ->first();

        // Jika sudah ada data dan bukan Alpha, berarti sudah absen
        if ($existing && !in_array($existing->status, ['alpha'])) {
            return back()->with('error', 'Guru ini sudah melakukan absensi hari ini.');
        }

        Attendance::updateOrCreate(
            ['guru_id' => $guruId, 'tanggal' => $hariIni],
            [
                'jam_masuk'  => $jamSekarang,
                'status'     => $statusInput,
                'metode'     => 'manual',
                'keterangan' => $request->keterangan,
            ]
        );

        return redirect()->route('kiosk.face')->with('success', 'Absensi Masuk Berhasil!');
    }

    private function checkoutManual($guruId)
    {
        $hariIni = Carbon::now()->format('Y-m-d');
        $jamSekarang = Carbon::now()->format('H:i:s');

        $attendance = Attendance::where('guru_id', $guruId)
            ->where('tanggal', $hariIni)
            ->first();

        if (!$attendance || $attendance->status === 'alpha') {
            return back()->with('error', 'Data absen masuk tidak ditemukan untuk hari ini.');
        }

        if ($attendance->jam_pulang) {
            return back()->with('error', 'Sudah melakukan absen pulang.');
        }

        $attendance->update([
            'jam_pulang' => $jamSekarang
        ]);

        return redirect()->route('kiosk.face')->with('success', 'Berhasil absen pulang!');
    }
}
