<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class AttendanceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'guru_id' => $this->guru_id,
            'nama_guru' => $this->guru ? $this->guru->name : 'Unknown',
            'nip' => $this->guru ? $this->guru->nip : '-',

            // Untuk Tampilan di Tabel
            'tanggal' => Carbon::parse($this->tanggal)->translatedFormat('d F Y'),

            // PENTING: Untuk Filter di JavaScript (Alpine.js)
            'tanggal_raw' => Carbon::parse($this->tanggal)->format('Y-m-d'),

            'hari' => Carbon::parse($this->tanggal)->translatedFormat('l'),
            'jam_masuk' => $this->jam_masuk ? Carbon::parse($this->jam_masuk)->format('H:i') : '--:--',
            'jam_pulang' => $this->jam_pulang ? Carbon::parse($this->jam_pulang)->format('H:i') : '--:--',
            'status' => ucfirst($this->status),
            'status_raw' => $this->status,
            'metode' => $this->metode,
            'keterangan' => $this->keterangan ?? '-',
        ];
    }
}
