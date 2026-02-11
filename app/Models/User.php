<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;


class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    public $incrementing = false;
    protected $keyType = 'string';

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
        // Mengambil satu jabatan yang sedang aktif (is_active = true)
        return $this->hasOne(GuruPosition::class, 'guru_id', 'guru_id')
            ->with('position') // Penting: meload tabel positions
            ->where('is_active', true)
            ->latest();
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'guru_id', 'guru_id');
    }

    public function faceProfile()
    {
        // Kita arahkan ke model FaceProfile
        // Foreign key di tabel face_profiles adalah 'guru_id'
        // Local key di tabel users adalah 'id' (UUID user)
        return $this->hasOne(FaceProfile::class, 'guru_id', 'id');
    }

    // 🔥 UNTUK GURU: Melihat daftar pengajuan izin miliknya
    public function leaveRequests()
    {
        // Hubungkan 'guru_id' di tabel leave_requests dengan 'id' (UUID) di tabel users
        return $this->hasMany(LeaveRequest::class, 'guru_id', 'guru_id');
    }

    // 🔥 UNTUK ADMIN: Melihat data apa saja yang sudah dia approve
    public function approvedLeaves()
    {
        return $this->hasMany(LeaveRequest::class, 'approved_by', 'id');
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
