<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Assessment extends Model
{
    protected $fillable = [
        'teacher_id',
        'evaluator_id',
        'assessment_period_id',
        'general_feedback',
        'final_score',
        'is_visible_to_teacher'
    ];

    // Relasi ke Guru (User)
    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    // Relasi ke yang menilai (Admin)
    public function evaluator()
    {
        return $this->belongsTo(User::class, 'evaluator_id');
    }

    // Relasi ke Detail (Skor per Kategori)
    // app/Models/Assessment.php
    public function details()
    {
        return $this->hasMany(AssessmentDetail::class, 'assessment_id');
    }

    public function period()
    {
        return $this->belongsTo(AssessmentPeriod::class, 'assessment_period_id');
    }
}
