<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AssessmentPeriod;
use Carbon\Carbon;

class AssessmentPeriodSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'name' => 'Semester Ganjil 2023/2024',
                'start_date' => '2023-07-01',
                'end_date' => '2023-12-31',
                'is_active' => false,
            ],
            [
                'name' => 'Semester Genap 2023/2024',
                'start_date' => '2024-01-01',
                'end_date' => '2024-06-30',
                'is_active' => false,
            ],
            [
                'name' => 'Semester Ganjil 2024/2025',
                'start_date' => '2024-07-01',
                'end_date' => '2024-12-31',
                'is_active' => false,
            ],
            [
                'name' => 'Semester Genap 2024/2025',
                'start_date' => '2025-01-01',
                'end_date' => '2025-06-30',
                'is_active' => true, // Set ini sebagai periode yang sedang berjalan
            ],
            [
                'name' => 'Semester Ganjil 2025/2026',
                'start_date' => '2025-07-01',
                'end_date' => '2025-12-31',
                'is_active' => false,
            ],
        ];

        foreach ($data as $item) {
            AssessmentPeriod::updateOrCreate(
                ['name' => $item['name']], // Unik berdasarkan nama
                $item
            );
        }

        $this->command->info('Seeder Periode: Berhasil membuat data semester ganjil & genap!');
    }
}