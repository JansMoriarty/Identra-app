<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\User;
use App\Models\Attendance;
use App\Models\PointRule;

// Command bawaan Laravel
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/**
 * COMMAND: Auto-Generate Status Alpha
 * Menjalankan pengecekan setiap guru yang tidak melakukan absen pada hari tersebut.
 */
Artisan::command('attendance:auto-alfa', function () {
    $this->info('--- Memulai pengecekan Auto-Alfa ---');

    $today = now()->toDateString();

    // 1. Ambil aturan sanksi poin untuk event ALFA
    $alfaRule = PointRule::where('trigger_event', 'ALFA')
        ->where('is_active', true)
        ->first();

    // 2. Ambil User dengan role 'guru' agar Admin/Siswa tidak ikut kena sanksi
    // Jika kamu belum pakai sistem role, bisa pakai User::all() tapi hati-hati.
    $users = User::where('role', 'guru')->get();

    if ($users->isEmpty()) {
        $this->error('Tidak ada user dengan role guru ditemukan.');
        return;
    }

    foreach ($users as $user) {
        // AMBIL UUID dari kolom guru_id, bukan kolom id!
        $uuidGuru = $user->guru_id;

        // Lewati jika user tersebut tidak punya UUID (biar tidak jadi null)
        if (!$uuidGuru) continue;

        // Cek apakah UUID ini sudah absen hari ini
        $exists = Attendance::where('guru_id', $uuidGuru)
            ->where('tanggal', $today)
            ->exists();

        if (!$exists) {
            Attendance::create([
                'guru_id'       => $uuidGuru, // SEKARANG PAKAI UUID
                'tanggal'       => $today,
                'status'        => 'alpha',
                'metode'        => 'manual',
                'keterangan'    => 'Sistem Otomatis: Tidak ada rekaman absensi.',
                'points_earned' => $alfaRule ? $alfaRule->point_modifier : 0,
            ]);

            $this->warn("Guru {$user->name} dinyatakan ALPHA.");
        } else {
            $this->line("Guru {$user->name} sudah absen.");
        }
    }

    $this->info('--- Selesai pengecekan Auto-Alfa ---');
})->purpose('Cek guru yang tidak absen dan beri status alpha otomatis secara masal');

/**
 * SCHEDULER: Menjalankan command secara otomatis
 * Dijalankan setiap hari jam 16:00 (Sesuaikan dengan jam pulang sekolah)
 */
Schedule::command('attendance:auto-alfa')->dailyAt('16:00');
