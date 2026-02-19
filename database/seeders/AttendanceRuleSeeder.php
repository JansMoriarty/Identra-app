<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AttendanceRuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $rules = [
            [
                'name' => 'batas_masuk',
                'rule_value' => '07:30:00',
                'description' => 'Batas akhir waktu hadir, lewat jam ini dianggap Telat.'
            ],
            [
                'name' => 'jam_pulang',
                'rule_value' => '14:00:00',
                'description' => 'Waktu tercepat guru boleh melakukan absen pulang.'
            ],
        ];

        foreach ($rules as $rule) {
            \App\Models\AttendanceRule::updateOrCreate(['name' => $rule['name']], $rule);
        }
    }
}
