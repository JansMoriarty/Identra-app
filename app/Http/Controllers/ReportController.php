<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use App\Exports\RekapGuruExport;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    // 1. HALAMAN REKAP KESELURUHAN (WEB VIEW)
    public function index(Request $request)
    {
        $startDate = $request->get('start', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end', Carbon::now()->format('Y-m-d'));

        $rekapGuru = User::where('role', 'guru')
            ->withCount([
                'attendances as total_hadir' => fn($q) => $q->whereBetween('tanggal', [$startDate, $endDate])->where('status', 'hadir'),
                'attendances as total_telat' => fn($q) => $q->whereBetween('tanggal', [$startDate, $endDate])->where('status', 'telat'),
                'attendances as total_izin' => fn($q) => $q->whereBetween('tanggal', [$startDate, $endDate])->where('status', 'izin'),
                'attendances as total_sakit' => fn($q) => $q->whereBetween('tanggal', [$startDate, $endDate])->where('status', 'sakit'),
                'attendances as total_alpha' => fn($q) => $q->whereBetween('tanggal', [$startDate, $endDate])->where('status', 'alpha'),
            ])
            ->with(['guruPositions' => function ($q) {
                $q->where('is_active', true)->with('position');
            }])
            ->get()
            ->map(function ($guru) {
                $posisiAktif = $guru->guruPositions->first();
                $guru->jabatan_aktif = $posisiAktif && $posisiAktif->position
                    ? $posisiAktif->position->nama_jabatan
                    : 'Belum Ditugaskan';

                // Hitung persentase
                $totalHadir = $guru->total_hadir + $guru->total_telat;
                $diffInDays = Carbon::parse(request('start'))->diffInDays(Carbon::parse(request('end'))) ?: 1;
                $guru->persentase = min(round(($totalHadir / ($diffInDays + 1)) * 100), 100);

                return $guru;
            });

        return view('pages.reports.index', compact('rekapGuru', 'startDate', 'endDate'));
    }

    // 2. CETAK PDF PER GURU (DARI MODAL)
    public function downloadPersonalReport($guru_id, Request $request)
    {
        $guru = User::where('guru_id', $guru_id)->firstOrFail();
        $start = $request->get('start');
        $end = $request->get('end');

        $attendances = Attendance::where('guru_id', $guru_id)
            ->whereBetween('tanggal', [$start, $end])
            ->orderBy('tanggal', 'asc')
            ->get();

        $summary = [
            'hadir' => $attendances->where('status', 'hadir')->count(),
            'telat' => $attendances->where('status', 'telat')->count(),
            'izin'  => $attendances->where('status', 'izin')->count(),
            'sakit' => $attendances->where('status', 'sakit')->count(),
            'alpha' => $attendances->where('status', 'alpha')->count(),
        ];

        $pdf = Pdf::loadView('pages.reports.pdf_personal', [
            'guru' => $guru,
            'attendances' => $attendances,
            'summary' => $summary,
            'start' => $start,
            'end' => $end
        ]);

        return $pdf->setPaper('a4', 'portrait')->stream("Laporan_{$guru->name}.pdf");
    }

    // 3. CETAK PDF KESELURUHAN (OPTIONAL)
    // 3. CETAK PDF KESELURUHAN
    public function downloadAllReport(Request $request)
    {
        $start = $request->get('start');
        $end = $request->get('end');

        $rekapGuru = User::where('role', 'guru')
            ->withCount([
                'attendances as total_hadir' => fn($q) => $q->whereBetween('tanggal', [$start, $end])->where('status', 'hadir'),
                'attendances as total_telat' => fn($q) => $q->whereBetween('tanggal', [$start, $end])->where('status', 'telat'),
                'attendances as total_izin' => fn($q) => $q->whereBetween('tanggal', [$start, $end])->where('status', 'izin'),
                'attendances as total_sakit' => fn($q) => $q->whereBetween('tanggal', [$start, $end])->where('status', 'sakit'),
                'attendances as total_alpha' => fn($q) => $q->whereBetween('tanggal', [$start, $end])->where('status', 'alpha'),
            ])->get();

        $pdf = Pdf::loadView('pages.reports.pdf_all', [
            'rekap' => $rekapGuru,
            'start' => $start,
            'end' => $end
        ]);

        return $pdf->setPaper('a4', 'landscape')->stream("Rekap_Absensi_Seluruh_Guru.pdf");
    }

    private function getRekapData($start, $end)
    {
        // Jika start/end kosong, ambil dari request atau default bulan ini
        $start = $start ?? request('start', date('Y-m-01'));
        $end = $end ?? request('end', date('Y-m-d'));

        return User::where('role', 'guru')->get()->map(function ($guru) use ($start, $end) {
            // Gunakan whereDate untuk memastikan perbandingan string tanggal sukses
            $attendances = $guru->attendances()
                ->whereDate('tanggal', '>=', $start)
                ->whereDate('tanggal', '<=', $end)
                ->get();

            $h = $attendances->where('status', 'hadir')->count();
            $t = $attendances->where('status', 'telat')->count();
            $i = $attendances->whereIn('status', ['izin', 'sakit'])->count();
            $a = $attendances->where('status', 'alpha')->count();
            $total = $attendances->count();

            return [
                'name'        => $guru->name,
                'nip'         => $guru->nip,
                'jabatan_aktif'  => $guru->nama_jabatan,
                'total_hadir' => $h,
                'total_telat' => $t,
                'total_izin'  => $i,
                'total_alpha' => $a,
                'persentase'  => $total > 0 ? round((($h + $t) / $total) * 100) : 0,
            ];
        })->toArray();
    }

    public function allExcel(Request $request)
    {
        $start = $request->get('start');
        $end = $request->get('end');

        // Ambil data rekap (sama seperti logika di PDF)
        $rekapGuru = $this->getRekapData($request->get('start'), $request->get('end'));

        return Excel::download(new RekapGuruExport($rekapGuru), "Rekap_Absensi_{$start}_to_{$end}.xlsx");
    }

    public function getPersonalStats(Request $request)
    {
        $user = $request->user();

        // 1. Ambil guru_id (UUID) dari user yang login
        // Berdasarkan log kamu, kolomnya di tabel users bernama 'guru_id'
        $guruUuid = $user->guru_id;

        $start = $request->get('start');
        $end = $request->get('end');

        // 2. Gunakan $guruUuid untuk mencari di tabel attendances
        $attendances = Attendance::where('guru_id', $guruUuid)
            ->whereBetween('tanggal', [$start, $end])
            ->get();

        // 3. Mapping data untuk kalender
        $calendarData = $attendances->map(function ($item) {
            return [
                'date' => $item->tanggal,
                'status' => strtolower($item->status), // 'sakit', 'izin', dll
            ];
        });

        return response()->json([
            'status' => 'success',
            'summary' => [
                'hadir' => $attendances->where('status', 'hadir')->count(),
                'telat' => $attendances->where('status', 'telat')->count(),
                'izin'  => $attendances->where('status', 'izin')->count(),
                'sakit' => $attendances->where('status', 'sakit')->count(),
                'alpha' => $attendances->where('status', 'alpha')->count(),
            ],
            'calendar' => $calendarData
        ]);
    }
}
