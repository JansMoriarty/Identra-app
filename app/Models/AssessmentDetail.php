<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssessmentDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'assessment_id',
        'category_id',
        'score'
    ];

    // Relasi balik ke header Assessment
    public function assessment()
    {
        return $this->belongsTo(Assessment::class);
    }

    // Relasi ke Kategori agar bisa tahu nama kategorinya apa
    public function category()
    {
        return $this->belongsTo(AssessmentCategory::class, 'category_id');
    }
}