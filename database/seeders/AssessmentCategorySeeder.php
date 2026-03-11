<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AssessmentCategory;
use Illuminate\Support\Facades\DB;

class AssessmentCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Kompetensi Pedagogik',
                'description' => 'Kemampuan mengelola pembelajaran peserta didik, perancangan, dan pelaksanaan evaluasi.',
                'weight' => 30.00, // Bobot 30%
            ],
            [
                'name' => 'Kompetensi Kepribadian',
                'description' => 'Kemampuan personal yang mencerminkan kepribadian yang mantap, stabil, dewasa, dan berwibawa.',
                'weight' => 20.00, // Bobot 20%
            ],
            [
                'name' => 'Kompetensi Sosial',
                'description' => 'Kemampuan guru untuk berkomunikasi dan bergaul secara efektif dengan peserta didik dan sesama pendidik.',
                'weight' => 20.00, // Bobot 20%
            ],
            [
                'name' => 'Kompetensi Profesional',
                'description' => 'Penguasaan materi pembelajaran secara luas dan mendalam.',
                'weight' => 30.00, // Bobot 30%
            ],
        ];

        foreach ($categories as $category) {
            AssessmentCategory::updateOrCreate(
                ['name' => $category['name']], // Unik berdasarkan nama
                [
                    'description' => $category['description'],
                    'weight' => $category['weight'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        $this->command->info("Seeder Kategori Berhasil: 4 Kompetensi Guru telah ditambahkan.");
    }
}