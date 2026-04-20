<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,
            AssessmentCategorySeeder::class,
            AssessmentPeriodSeeder::class,
            AssessmentSeeder::class,
            AttendanceRuleSeeder::class,
            AttendanceSeeder::class,
            IntegritySystemSeeder::class,
        ]);
    }
}
