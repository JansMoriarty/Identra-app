<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssessmentPeriod extends Model
{
    use HasFactory;

    // Nama tabel di database (pastikan sama)
    protected $table = 'assessment_periods';

    // Kolom yang boleh diisi secara massal
    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'is_active',
    ];

    // Opsional: Casting agar Laravel otomatis merubah string date menjadi object Carbon
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];
}