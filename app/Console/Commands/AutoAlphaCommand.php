<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class AutoAlphaCommand extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:auto-alpha-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $hariIni = now()->format('Y-m-d');

        // Ambil semua Guru yang BELUM ada di tabel attendance hari ini
        $semuaGuruId = \App\Models\User::where('role', 'guru')->pluck('guru_id');
        $sudahAbsen = \App\Models\Attendance::where('tanggal', $hariIni)->pluck('guru_id');

        $belumAbsen = $semuaGuruId->diff($sudahAbsen);

        foreach ($belumAbsen as $id) {
            \App\Models\Attendance::create([
                'guru_id' => $id,
                'tanggal' => $hariIni,
                'status'  => 'alpha',
                'metode'  => 'manual'
            ]);
        }

        $this->info('Sistem otomatis berhasil mencatat guru yang Alpha.');
    }
}
