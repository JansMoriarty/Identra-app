<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Classroom extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'qr_code',
        // 'location',
    ];
} // Pastikan hanya ada satu kurung tutup di paling akhir