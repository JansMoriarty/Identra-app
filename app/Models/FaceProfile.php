<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FaceProfile extends Model
{
    use HasFactory;

    // Pastikan fillable sesuai dengan nama kolom di Migration baru
    protected $fillable = [
        'user_id', // Ganti guru_id jadi user_id jika di migration sudah diubah
        'image_path',
        'face_descriptor'
    ];

    protected $casts = [
        'face_descriptor' => 'array', // Sangat tepat! 192 float akan jadi Array PHP
    ];

    /**
     * Relasi ke User
     */
    public function user()
    {
        // Jika kolom di tabel ini adalah 'user_id' dan di tabel users adalah 'id'
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
