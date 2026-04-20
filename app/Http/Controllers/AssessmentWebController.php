<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\AssessmentCategory;
use App\Models\AssessmentPeriod;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssessmentWebController extends Controller
{
    /**
     * Menampilkan Dashboard Penilaian Guru
     */
    public function index()
    {
        // 1. Ambil Periode Aktif
        $activePeriod = AssessmentPeriod::where('is_active', true)->first();

        // 2. Ambil Semua Guru (Role: guru)
        $teachers = User::where('role', 'guru')
            ->select('id', 'name', 'email')
            ->get();

        // 3. Ambil Semua Kategori Penilaian (untuk modal input)
        $categories = AssessmentCategory::all();

        // 4. Ambil ID Guru yang SUDAH dinilai pada periode aktif ini
        $ratedTeacherIds = [];
        if ($activePeriod) {
            $ratedTeacherIds = Assessment::where('assessment_period_id', $activePeriod->id)
                ->pluck('teacher_id')
                ->toArray();
        }

        return view('pages.assessments.index', [
            'title' => 'Penilaian Guru',
            'activePeriod' => $activePeriod,
            'teachers' => $teachers,
            'categories' => $categories,
            'ratedTeacherIds' => $ratedTeacherIds,
        ]);
    }

    /**
     * Menyimpan hasil penilaian
     */
    public function store(Request $request)
    {
        // Validasi input
        $request->validate([
            'teacher_id' => 'required|exists:users,id',
            'assessment_period_id' => 'required|exists:assessment_periods,id',
            'scores' => 'required|array',
            'general_feedback' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            // 1. Hitung Skor Akhir (Rata-rata atau berdasarkan bobot jika ada)
            $totalScore = 0;
            $categoryCount = count($request->scores);
            
            foreach ($request->scores as $catId => $score) {
                $totalScore += (int)$score;
            }

            $finalScore = $categoryCount > 0 ? ($totalScore / $categoryCount) : 0;

            // 2. Simpan Header Assessment
            $assessment = Assessment::create([
                'teacher_id' => $request->teacher_id,
                'evaluator_id' => auth()->id(), // Admin yang sedang login
                'assessment_period_id' => $request->assessment_period_id,
                'general_feedback' => $request->general_feedback,
                'final_score' => $finalScore,
                'is_visible_to_teacher' => true, // Default dibuka agar guru bisa lihat di API/Flutter
            ]);

            // 3. Simpan Detail Skor per Kategori
            foreach ($request->scores as $categoryId => $scoreValue) {
                $assessment->details()->create([
                    'category_id' => $categoryId,
                    'score' => $scoreValue,
                ]);
            }

            DB::commit();

            return redirect()->route('admin.assessments.index')
                ->with('success', 'Penilaian untuk guru berhasil disimpan.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}