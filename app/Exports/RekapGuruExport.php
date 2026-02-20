<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class RekapGuruExport implements FromCollection, WithHeadings, WithMapping
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return collect($this->data);
    }

    public function headings(): array
    {
        return ['Nama Guru', 'NIP', 'Hadir', 'Telat', 'Izin/Sakit', 'Alpha', 'Skor (%)'];
    }

    public function map($guru): array
    {
        // SESUAIKAN KEY BERIKUT DENGAN YANG ADA DI CONTROLLER
        return [
            $guru['name'],
            $guru['nip'] ? "'" . $guru['nip'] : '-',
            $guru['total_hadir'], // Sebelumnya mungkin salah ketik atau kosong
            $guru['total_telat'], // Pastikan key-nya 'total_telat'
            $guru['total_izin'],  // Pastikan key-nya 'total_izin'
            $guru['total_alpha'], // Pastikan key-nya 'total_alpha'
            $guru['persentase'] . '%',
        ];
    }
}
