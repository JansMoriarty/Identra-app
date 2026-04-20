<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\AssessmentCategory;
use Illuminate\Http\Request;

class AssessmentController extends Controller
{
    public function getMyReport(Request $request)
    {
        // 1. Ambil user/guru yang sedang login berdasarkan Token
        $teacher = $request->user();

        // 2. Ambil Penilaian Terbaru (Untuk Ringkasan & Radar Chart)
        $latestAssessment = Assessment::with(['details.category', 'evaluator'])
            ->where('teacher_id', $teacher->id)
            ->latest()
            ->first();

        if (!$latestAssessment) {
            return response()->json([
                'success' => false,
                'message' => 'Belum ada data penilaian untuk Anda.',
            ], 404);
        }

        // 3. Ambil Semua Riwayat Penilaian (Untuk List History)
        $history = Assessment::with(['period', 'evaluator'])
            ->where('teacher_id', $teacher->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'period_name' => $item->period->name ?? 'N/A',
                    'final_score' => round($item->final_score, 2),
                    'predicate' => $this->calculatePredicate($item->final_score),
                    'feedback' => $item->general_feedback,
                    'evaluator' => $item->evaluator->name ?? 'Sistem',
                    'date' => $item->created_at->format('d M Y'),
                ];
            });

        // 4. Format data khusus untuk Radar Chart Flutter
        $radarData = $latestAssessment->details->map(function ($detail) {
            return [
                'subject' => $detail->category->name,
                'score' => (int) $detail->score,
                'fullMark' => 5, // Karena skala skor 1-5
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'profile' => [
                    'name' => $teacher->name,
                    'nip' => $teacher->nip ?? '-',
                    'avatar_initial' => strtoupper(substr($teacher->name, 0, 1)),
                ],
                'current_assessment' => [
                    'average_score' => round($latestAssessment->final_score, 1),
                    'predicate' => $this->calculatePredicate($latestAssessment->final_score),
                    'radar_chart' => $radarData,
                ],
                'history' => $history
            ]
        ], 200);
    }

    // Helper Predikat (Samakan logic-nya dengan yang di Web)
    private function calculatePredicate($score)
    {
        if ($score >= 4.5) return 'Istimewa';
        if ($score >= 3.75) return 'Sangat Baik';
        if ($score >= 3.0) return 'Baik';
        if ($score >= 2.0) return 'Cukup';
        return 'Kurang';
    }
}