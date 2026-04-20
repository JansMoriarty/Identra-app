<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Attendance;
use App\Models\PointRule;
use App\Models\PointLedger; // Sesuaikan dengan nama model buku kas poinmu

class AutoAlfaCommand extends Command
{
    protected $signature = 'attendance:auto-alfa';
    protected $description = 'Cek user yang tidak absen dan beri status Alfa serta potong poin';

    public function handle()
    {
        $today = now()->toDateString();
        
        // 1. Ambil aturan poin untuk ALFA
        $alfaRule = PointRule::where('trigger_event', 'ALFA')
                            ->where('is_active', true)
                            ->first();

        // 2. Ambil semua user yang wajib absen (misal: role guru)
        $users = User::all(); 

        foreach ($users as $user) {
            // Cek apakah sudah ada data absen hari ini
            $exists = Attendance::where('user_id', $user->id)
                                ->whereDate('created_at', $today)
                                ->exists();

            if (!$exists) {
                // Simpan status ALFA ke tabel absensi
                Attendance::create([
                    'user_id' => $user->id,
                    'status'  => 'ALFA',
                    'created_at' => now(),
                ]);

                // Jika ada aturan poin Alfa, langsung potong
                if ($alfaRule) {
                    // Logic simpan poin ke ledger/history poin user
                    // PointLedger::create([ ... ]);
                    
                    $this->info("User {$user->name} diabsenkan ALFA dan poin dipotong {$alfaRule->point_modifier}");
                }
            }
        }
    }
}