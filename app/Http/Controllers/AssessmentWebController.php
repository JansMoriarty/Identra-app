<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Assessment;
use App\Models\AssessmentCategory;
use App\Models\AssessmentPeriod;
use App\Models\AssessmentDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AssessmentWebController extends Controller
{
    public function index()
    {
        $activePeriod = AssessmentPeriod::where('is_active', true)->first();
        $teachers = User::where('role', 'guru')->get();
        $categories = AssessmentCategory::all();

        // Ambil ID guru yang sudah dinilai di periode aktif
        $ratedTeacherIds = Assessment::where('assessment_period_id', $activePeriod->id ?? 0)
            ->pluck('teacher_id')
            ->toArray();

        return view('pages.assessments.index', compact('teachers', 'categories', 'activePeriod', 'ratedTeacherIds'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'teacher_id' => 'required|exists:users,id',
            'assessment_period_id' => 'required|exists:assessment_periods,id',
            'scores' => 'required|array',
            'general_feedback' => 'nullable|string'
        ]);

        // --- START: VALIDASI CEK DUPLIKAT (Ini kuncinya!) ---
        $alreadyExists = Assessment::where('teacher_id', $request->teacher_id)
            ->where('assessment_period_id', $request->assessment_period_id)
            ->exists();

        if ($alreadyExists) {
            // Kita kirim pesan spesifik ke session
            return redirect()->back()->with('error_penilaian', 'Anda sudah menilai guru ini pada periode sekarang!');
        }
        // --- END: VALIDASI CEK DUPLIKAT ---

        try {
            DB::beginTransaction();

            $totalScore = 0;
            $categories = AssessmentCategory::whereIn('id', array_keys($request->scores))->get();

            foreach ($categories as $cat) {
                $scoreInput = $request->scores[$cat->id];
                // Rumus: (skor/5) * bobot_persen
                $totalScore += ($scoreInput / 5) * ($cat->weight);
            }

            $assessment = Assessment::create([
                'teacher_id' => $request->teacher_id,
                'evaluator_id' => Auth::id(),
                'assessment_period_id' => $request->assessment_period_id,
                'general_feedback' => $request->general_feedback,
                'final_score' => $totalScore,
                'is_visible_to_teacher' => true
            ]);

            foreach ($request->scores as $categoryId => $score) {
                AssessmentDetail::create([
                    'assessment_id' => $assessment->id,
                    'category_id' => $categoryId,
                    'score' => $score
                ]);
            }

            DB::commit();
            return redirect()->back()->with('success', 'Penilaian berhasil disimpan!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }
}
