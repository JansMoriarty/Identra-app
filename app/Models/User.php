<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;


class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'guru_id',
        'nip',
        'nuptk',
        'jenis_kelamin',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /** ================= RELATIONS ================= */

    public function profile()
    {
        return $this->hasOne(UserProfile::class);
    }

    // 🔥 RIWAYAT JABATAN GURU
    public function guruPositions()
    {
        return $this->hasMany(GuruPosition::class, 'guru_id', 'guru_id');
    }

    // 🔥 JABATAN AKTIF SAAT INI
    public function activePosition()
    {
        return $this->hasOne(GuruPosition::class, 'guru_id', 'guru_id')
                    ->where('is_active', true)
                    ->latest();
    }

    /** Helper */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isGuru(): bool
    {
        return $this->role === 'guru';
    }
}
