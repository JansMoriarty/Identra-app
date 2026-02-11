<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FaceProfile extends Model
{
    protected $fillable = ['guru_id', 'image_path', 'face_descriptor'];

    protected $casts = [
        'face_descriptor' => 'array', // Krusial: Mengubah JSON DB menjadi Array PHP
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'guru_id', 'guru_id');
    }
}
