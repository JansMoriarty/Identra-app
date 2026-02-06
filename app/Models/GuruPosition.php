<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GuruPosition extends Model
{
    // Nama tabel didefinisikan manual jika tidak sesuai konvensi jamak plural
    protected $table = 'guru_positions';

    protected $fillable = [
        'guru_id',       // UUID dari API
        'position_id',   // ID dari tabel positions lokal
        'tanggal_mulai',
        'tanggal_selesai',
        'is_active'
    ];

    // Cast attributes agar lebih mudah dimanipulasi sebagai objek Carbon/Boolean
    protected $casts = [
        'tanggal_mulai'   => 'date',
        'tanggal_selesai' => 'date',
        'is_active'       => 'boolean',
    ];

    /**
     * Relasi balik ke Master Jabatan
     */
    public function position()
    {
        return $this->belongsTo(Position::class, 'position_id');
    }

    /**
     * Scope untuk memudahkan memanggil jabatan yang aktif saja
     * Contoh penggunaan: GuruPosition::active()->get();
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function user()
    {
        // Menghubungkan guru_id di tabel ini ke guru_id di tabel users
        return $this->belongsTo(User::class, 'guru_id', 'guru_id');
    }
}
