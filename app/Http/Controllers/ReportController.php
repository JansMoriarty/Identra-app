<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class ReportController extends Controller
{
    // 1. LAPORAN SELURUH GURU
    public function monthlyReport(Request $request)
    {
        $month = $request->get('month', date('m'));
        $year = $request->get('year', date('Y'));

        // Ambil semua guru
        $gurus = User::where('role', 'guru')->get();

        // Ambil data absen pada bulan & tahun terpilih
        $reports = $gurus->map(function ($guru) use ($month, $year) {
            $attendance = Attendance::where('guru_id', $guru->guru_id)
                ->whereMonth('tanggal', $month)
                ->whereYear('tanggal', $year)
                ->get();

            return [
                'nama' => $guru->name,
                'hadir' => $attendance->where('status', 'hadir')->count(),
                'telat' => $attendance->where('status', 'telat')->count(),
                'izin' => $attendance->where('status', 'izin')->count(),
                'sakit' => $attendance->where('status', 'sakit')->count(),
                'alpha' => $attendance->where('status', 'alpha')->count(),
            ];
        });

        return view('pages.reports.monthly', compact('reports', 'month', 'year'));
    }

    // 2. CETAK PDF PER GURU
    public function downloadPersonalReport($guru_id, Request $request)
    {
        $guru = User::where('guru_id', $guru_id)->firstOrFail();
        $start = $request->get('start');
        $end = $request->get('end');

        $attendances = Attendance::where('guru_id', $guru_id)
            ->whereBetween('tanggal', [$start, $end])
            ->orderBy('tanggal', 'asc')
            ->get();

        // Hitung Ringkasan
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
}
