<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Ambil semua user yang role-nya guru
        $gurus = User::where('role', 'guru')->get();

        if ($gurus->isEmpty()) {
            $this->command->warn('Tidak ada data guru. Silakan buat user guru terlebih dahulu.');
            return;
        }

        // --- KONFIGURASI RANGE TANGGAL ---
        // Kita buat dua range: Tahun 2025 dan Maret 2026 (1-9 Maret)
        $ranges = [
            [
                'start' => Carbon::create(2025, 1, 1),
                'end'   => Carbon::create(2025, 12, 31),
                'label' => 'Tahun 2025'
            ],
            [
                'start' => Carbon::create(2026, 3, 1),
                'end'   => Carbon::now(), // Sampai hari ini (9 Maret 2026)
                'label' => 'Maret 2026 (1-9 Maret)'
            ]
        ];

        $dataToInsert = [];
        $batchSize = 500; 

        foreach ($ranges as $range) {
            $this->command->info("Memulai generate dummy absen untuk: {$range['label']}...");

            for ($date = $range['start']->copy(); $date->lte($range['end']); $date->addDay()) {
                
                // Skip Sabtu & Minggu
                if ($date->isWeekend()) {
                    continue;
                }

                foreach ($gurus as $guru) {
                    // Cek apakah sudah ada absen di tanggal ini untuk guru ini (agar tidak duplicate)
                    $exists = Attendance::where('guru_id', $guru->guru_id)
                                        ->where('tanggal', $date->format('Y-m-d'))
                                        ->exists();
                    if ($exists) continue;

                    $status = $this->getRandomStatus();
                    
                    $jamMasuk = null;
                    $jamPulang = null;
                    $keterangan = null;
                    $metode = 'face';

                    if ($status === 'hadir') {
                        $jamMasuk = Carbon::createFromTime(6, rand(15, 59), rand(0, 59))->format('H:i:s');
                        $jamPulang = Carbon::createFromTime(rand(14, 15), rand(0, 30), rand(0, 59))->format('H:i:s');
                        $metode = rand(1, 10) <= 8 ? 'face' : 'manual';
                    
                    } elseif ($status === 'telat') {
                        $jamMasuk = Carbon::createFromTime(rand(7, 8), rand(15, 30), rand(0, 59))->format('H:i:s');
                        $jamPulang = Carbon::createFromTime(rand(14, 15), rand(0, 30), rand(0, 59))->format('H:i:s');
                        $metode = rand(1, 10) <= 8 ? 'face' : 'manual';
                    
                    } elseif ($status === 'sakit') {
                        $keterangan = 'Sakit (Dilampirkan surat dokter)';
                        $metode = 'manual';
                    
                    } elseif ($status === 'izin') {
                        $keterangan = 'Izin keperluan keluarga';
                        $metode = 'manual';
                    
                    } elseif ($status === 'alpha') {
                        $keterangan = 'Tanpa Keterangan';
                        $metode = 'manual';
                    }

                    $dataToInsert[] = [
                        'guru_id'    => $guru->guru_id, 
                        'tanggal'    => $date->format('Y-m-d'),
                        'jam_masuk'  => $jamMasuk,
                        'jam_pulang' => $jamPulang,
                        'status'     => $status,
                        'metode'     => $metode,
                        'keterangan' => $keterangan,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    if (count($dataToInsert) >= $batchSize) {
                        Attendance::insert($dataToInsert);
                        $dataToInsert = []; 
                    }
                }
            }
        }

        if (!empty($dataToInsert)) {
            Attendance::insert($dataToInsert);
        }

        $this->command->info('Berhasil! Data absensi 2025 dan awal Maret 2026 sudah masuk.');
    }

    private function getRandomStatus(): string
    {
        $rand = rand(1, 100);
        if ($rand <= 70) return 'hadir';    // 70% Hadir
        if ($rand <= 85) return 'telat';    // 15% Telat
        if ($rand <= 90) return 'sakit';    // 5% Sakit
        if ($rand <= 95) return 'izin';     // 5% Izin
        return 'alpha';                     // 5% Alpha
    }
}