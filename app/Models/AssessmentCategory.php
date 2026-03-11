<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssessmentCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'weight',
    ];

    // Relasi ke Detail Penilaian (Satu kategori bisa punya banyak baris nilai)
    // public function assessmentDetails()
    // {
    //     return $this->hasMany(AssessmentDetail::class, 'category_id');
    // }
}