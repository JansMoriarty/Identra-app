<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassAttendance extends Model
{
    use HasFactory;

    // 1. Tentukan kolom mana saja yang boleh diisi
    protected $fillable = [
        'user_id',
        'class_code',
        'tanggal',
        'jam_masuk',
        'metode',
    ];

    /**
     * Relasi ke Model User
     * Mengasumsikan satu absen kelas dimiliki oleh satu User (Guru)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * (Opsional) Relasi ke Jadwal
     * Jika kamu ingin menghubungkan class_code ke tabel Jadwal
     */
    public function jadwal(): BelongsTo
    {
        // Kita hubungkan kolom 'class_code' di sini ke 'kode_qr' di tabel Jadwal
        return $this->belongsTo(Jadwal::class, 'class_code', 'kode_qr');
    }
}