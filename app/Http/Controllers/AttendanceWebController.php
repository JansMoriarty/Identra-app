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
        $startDate = $request->get('start_date') ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $endDate = $request->get('end_date') ?? Carbon::now()->endOfMonth()->format('Y-m-d');

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
        // 1. Validasi: Tambahkan latitude dan longitude agar wajib dikirim dari web
        $request->validate([
            'guru_id'    => 'required|exists:users,guru_id',
            'status'     => 'required|in:hadir,pulang',
            'latitude'   => 'required', // Koordinat pengabsen
            'longitude'  => 'required', // Koordinat pengabsen
            'keterangan' => 'nullable|string',
        ]);

        // 2. CEK RADIUS GEOFENCING
        // Memastikan Admin/Perangkat Kiosk berada di area yang ditentukan
        if (!$this->isWithinGeofence($request->latitude, $request->longitude)) {
            return back()->with('error', 'Posisi perangkat di luar jangkauan absensi sekolah!');
        }

        // Jika statusnya pulang, lempar ke fungsi checkout manual
        if ($request->status === 'pulang') {
            // Teruskan koordinat ke checkoutManual jika perlu validasi lokasi di sana juga
            return $this->checkoutManual($request->guru_id, $request->latitude, $request->longitude);
        }

        $guruId = $request->guru_id;
        $hariIni = \Carbon\Carbon::now()->format('Y-m-d');
        $jamSekarang = \Carbon\Carbon::now()->format('H:i:s');
        $batasMasuk = \App\Models\AttendanceRule::getValue('batas_masuk', '12:40:00');

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
                // Opsional: simpan koordinat absen ke database jika kolom tersedia
                // 'latitude' => $request->latitude,
                // 'longitude' => $request->longitude,
            ]
        );

        return redirect()->route('kiosk.face')->with('success', 'Absensi Masuk Berhasil!');
    }

    private function checkoutManual($guruId, $lat, $lng)
    {
        // Validasi Geofencing saat pulang (Opsional, tapi bagus untuk keamanan)
        if (!$this->isWithinGeofence($lat, $lng)) {
            return back()->with('error', 'Gagal absen pulang! Perangkat di luar jangkauan.');
        }

        $hariIni = \Carbon\Carbon::now()->format('Y-m-d');
        $jamSekarang = \Carbon\Carbon::now()->format('H:i:s');
        $jamPulangMinimal = \App\Models\AttendanceRule::getValue('jam_pulang', '14:00:00');

        $attendance = Attendance::where('guru_id', $guruId)
            ->where('tanggal', $hariIni)
            ->first();

        if (!$attendance || $attendance->status === 'alpha') {
            return back()->with('error', 'Data absen masuk tidak ditemukan untuk hari ini.');
        }

        if ($attendance->jam_pulang) {
            return back()->with('error', 'Sudah melakukan absen pulang.');
        }

        if ($jamSekarang < $jamPulangMinimal) {
            return back()->with('error', 'Belum waktunya absen pulang. Minimal jam: ' . $jamPulangMinimal);
        }

        $attendance->update([
            'jam_pulang' => $jamSekarang
        ]);

        return redirect()->route('kiosk.face')->with('success', 'Berhasil absen pulang!');
    }

    /**
     * Helper untuk menghitung apakah koordinat masuk dalam salah satu radius lokasi
     */
    private function isWithinGeofence($userLat, $userLng)
    {
        $locations = \App\Models\Location::all();
        $earthRadius = 6371000; // Meter

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
                return true;
            }
        }

        return false;
    }
}
