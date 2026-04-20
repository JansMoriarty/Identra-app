<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FlexibilityItem;
use App\Models\PointRule;

class IntegritySystemSeeder extends Seeder
{
    public function run()
    {
        // 1. Seed Item Marketplace (Voucher)
        FlexibilityItem::create([
            'item_name'   => 'Token Bebas Terlambat 15 Menit',
            'description' => 'Gunakan token ini untuk memutihkan keterlambatan maksimal 15 menit.',
            'item_type'   => 'LATE_WAVER',
            'value_power' => 15,
            'point_cost'  => 50,
            'is_available' => true
        ]);

        FlexibilityItem::create([
            'item_name'   => 'Token Bebas Terlambat 30 Menit',
            'description' => 'Gunakan token ini untuk memutihkan keterlambatan maksimal 30 menit.',
            'item_type'   => 'LATE_WAVER',
            'value_power' => 30,
            'point_cost'  => 90,
            'is_available' => true
        ]);

        // 2. Seed Rule Engine (Aturan Poin)
        // 2. Seed Rule Engine (Aturan Poin)
        PointRule::create([
            'rule_name'          => 'Reward Datang Pagi',
            'target_role'        => 'guru',
            'trigger_event'      => 'CHECK_IN',
            'condition_operator' => '<',
            'condition_time'     => null, // DIUBAH KE NULL: Biar bandingin ke batas_masuk di attendance_rules
            'point_modifier'     => 5,
            'priority'           => 10
        ]);

        PointRule::create([
            'rule_name'          => 'Denda Terlambat Standar', // Tambahan: Biar telat dikit langsung potong
            'target_role'        => 'guru',
            'trigger_event'      => 'CHECK_IN',
            'condition_operator' => '>',
            'condition_time'     => null, // DIUBAH KE NULL: Biar bandingin ke batas_masuk
            'point_modifier'     => -3,
            'priority'           => 6
        ]);

        PointRule::create([
            'rule_name'          => 'Sanksi Tidak Hadir (Alfa)',
            'target_role'        => 'guru',
            'trigger_event'      => 'ALFA',
            'condition_operator' => '>', // Ganti dari '=' menjadi '>' agar lolos Enum
            'condition_time'     => null,
            'point_modifier'     => -10,
            'priority'           => 1,
            'is_active'          => true
        ]);
    }
}
