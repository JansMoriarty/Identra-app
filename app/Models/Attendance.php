<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'guru_id',
        'tanggal',
        'jam_masuk',
        'jam_pulang',
        'status',
        'metode',
        'keterangan',
    ];

    /**
     * Relasi ke User/Guru
     * Menggunakan guru_id (UUID) sebagai foreign key
     */
    public function guru()
    {
        // Sesuaikan jika foreign key di tabel users adalah guru_id
        return $this->belongsTo(User::class, 'guru_id', 'guru_id');
    }

    /**
     * Scope untuk mempermudah filter absen hari ini
     */
    public function scopeToday($query)
    {
        return $query->where('tanggal', now()->format('Y-m-d'));
    }
}