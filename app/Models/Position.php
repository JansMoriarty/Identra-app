<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    protected $fillable = ['nama_jabatan', 'kode_jabatan'];

    public function users()
    {
        return $this->belongsToMany(
            User::class, 
            'guru_positions', 
            'position_id', 
            'guru_id', 
            'id', 
            'guru_id' // Hubungkan ke guru_id di tabel users
        )->withPivot('tanggal_mulai', 'tanggal_selesai', 'is_active');
    }
}