<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Assessment;
use App\Models\AssessmentDetail;
use App\Models\AssessmentCategory;
use App\Models\AssessmentPeriod;
use Illuminate\Support\Facades\DB;

class AssessmentSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Pastikan ada Periode (Kita buatkan 2 periode untuk testing history)
        $periods = [
            [
                'name' => 'Semester Ganjil 2025/2026',
                'start_date' => '2025-07-01',
                'end_date' => '2025-12-31',
                'is_active' => false
            ],
            [
                'name' => 'Semester Genap 2025/2026',
                'start_date' => '2026-01-01',
                'end_date' => '2026-06-30',
                'is_active' => true // Ini periode yang aktif sekarang
            ],
        ];

        foreach ($periods as $p) {
            AssessmentPeriod::updateOrCreate(['name' => $p['name']], $p);
        }

        // 2. Ambil data pendukung
        $teachers = User::where('role', 'guru')->get();
        $categories = AssessmentCategory::all();
        $activePeriod = AssessmentPeriod::where('is_active', true)->first();
        $oldPeriod = AssessmentPeriod::where('is_active', false)->first();
        $evaluator = User::where('role', 'admin')->first() ?? User::first();

        if ($categories->isEmpty()) {
            $this->command->error("AssessmentCategory kosong! Jalankan CategorySeeder dulu.");
            return;
        }

        $this->command->info("Sedang menanam data penilaian variatif...");

        foreach ($teachers as $index => $teacher) {
            // Kita bagi guru jadi 3 tipe untuk simulasi chart:
            // Index 0: Guru bermasalah (Skor rendah 1-2)
            // Index 1: Guru rata-rata (Skor 3-4)
            // Sisanya: Guru teladan (Skor 4-5)
            
            if ($index === 0) {
                $type = 'buruk';
                $feedback = 'Sangat perlu ditingkatkan kedisiplinan dan cara mengajarnya.';
            } elseif ($index === 1) {
                $type = 'sedang';
                $feedback = 'Performa stabil, namun perlu inovasi dalam media pembelajaran.';
            } else {
                $type = 'bagus';
                $feedback = 'Luar biasa! Terus pertahankan dedikasi Anda dalam mendidik.';
            }

            // Buat penilaian di 2 periode agar history-nya muncul
            $targetPeriods = [$oldPeriod, $activePeriod];

            foreach ($targetPeriods as $period) {
                if (!$period) continue;

                DB::transaction(function () use ($teacher, $categories, $period, $evaluator, $type, $feedback) {
                    $assessment = Assessment::updateOrCreate(
                        [
                            'teacher_id' => $teacher->id,
                            'assessment_period_id' => $period->id,
                        ],
                        [
                            'evaluator_id' => $evaluator->id,
                            'general_feedback' => $feedback,
                            'is_visible_to_teacher' => true,
                            'final_score' => 0,
                        ]
                    );

                    $totalFinalScore = 0;

                    foreach ($categories as $category) {
                        // Logika skor berdasarkan tipe guru
                        if ($type === 'buruk') {
                            $score = rand(1, 2);
                        } elseif ($type === 'sedang') {
                            $score = rand(3, 4);
                        } else {
                            // Guru bagus, tapi kita kasih satu kategori yang rendah (misal: Kerapihaan) 
                            // agar Radar Chart-nya terlihat berlekuk (tidak bulat sempurna)
                            $score = ($category->name == 'Kerapihan') ? rand(2, 3) : rand(4, 5);
                        }

                        AssessmentDetail::updateOrCreate(
                            [
                                'assessment_id' => $assessment->id,
                                'category_id' => $category->id,
                            ],
                            ['score' => $score]
                        );

                        // Rumus: (skor_1-5) * (bobot_persen / 100)
                        // Contoh: Skor 5 dan Bobot 20% -> 5 * 0.2 = 1.0 poin
                        $totalFinalScore += $score * ($category->weight / 100);
                    }

                    $assessment->update(['final_score' => $totalFinalScore]);
                });
            }
        }

        $this->command->info("Seeder Berhasil: Data guru teladan dan guru 'testing' sudah siap!");
    }
}