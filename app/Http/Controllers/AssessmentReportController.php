<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\AssessmentCategory;
use App\Models\AssessmentDetail;
use App\Models\Assessment;
use Illuminate\Http\Request;

class AssessmentReportController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query()->where('role', 'guru');

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $teachers = $query->paginate(10);

        // Data untuk Statistik Overview
        $stats = [
            'total_guru' => User::where('role', 'guru')->count(),
            'total_penilaian' => Assessment::count(),
            'rata_rata_sekolah' => AssessmentDetail::avg('score') ?? 0,
        ];

        // Data untuk Grafik (Contoh: Rata-rata per Kategori secara Nasional/Sekolah)
        $categories = AssessmentCategory::all();
        $chartData = [
            'labels' => $categories->pluck('name'),
            'data' => $categories->map(function ($cat) {
                return AssessmentDetail::where('category_id', $cat->id)->avg('score') ?? 0;
            })
        ];

        return view('pages.report-assessments.index', compact('teachers', 'stats', 'chartData'));
    }

    public function show($id)
    {
        // 1. Ambil data guru atau gagalkan jika tidak ketemu
        $teacher = User::findOrFail($id);

        // 2. Ambil kategori untuk label radar chart
        $categories = AssessmentCategory::all();

        // 3. Hitung rata-rata skor per kategori khusus untuk guru ini
        $radarScores = $categories->map(function ($cat) use ($teacher) {
            return AssessmentDetail::where('category_id', $cat->id)
                ->whereHas('assessment', function ($q) use ($teacher) {
                    $q->where('teacher_id', $teacher->id);
                })->avg('score') ?? 0;
        });

        // 4. Ambil riwayat feedback (Timeline) lengkap dengan data periode
        $feedbacks = Assessment::with('period')
            ->where('teacher_id', $teacher->id)
            ->orderBy('created_at', 'desc')
            ->get();

        // 5. Hitung rata-rata total dari semua skor kategori yang ada
        $averageScore = count($radarScores) > 0 ? $radarScores->avg() : 0;

        // 6. Tentukan predikat berdasarkan nilai rata-rata
        $predicate = $this->getPredicate($averageScore);

        // 7. Kirim semua data ke View
        return view('pages.report-assessments.rapor', compact(
            'teacher',
            'categories',
            'radarScores',
            'feedbacks',
            'averageScore',
            'predicate'
        ));
    }

    /**
     * Fungsi pembantu (helper) untuk menentukan predikat nilai.
     * Dibuat 'private' karena hanya digunakan di dalam controller ini.
     */
    private function getPredicate($score)
    {
        // Jika skor skala 1-5
        if ($score >= 4.5) return 'Sangat Baik (A)';
        if ($score >= 3.7) return 'Baik (B)';
        if ($score >= 3.0) return 'Cukup (C)';
        return 'Perlu Peningkatan (D)';
    }
}
