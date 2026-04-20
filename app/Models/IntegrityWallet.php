<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IntegrityWallet extends Model
{
    use HasFactory;

    // Tentukan nama tabel jika tidak sesuai standar jamak Laravel (integrity_wallets)
    protected $table = 'integrity_wallets';

    protected $fillable = [
        'user_id',
        'point_change',
        'description',
        // tambahkan kolom lain yang kamu punya di tabel ini
    ];

    // Relasi balik ke User (Opsional tapi bagus untuk punya)
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}